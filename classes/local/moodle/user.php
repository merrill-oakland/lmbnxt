<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * An object for converting data to moodle.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb\local\moodle;

defined('MOODLE_INTERNAL') || die();

use enrol_lmb\logging;
use enrol_lmb\settings;
use enrol_lmb\local\data;

require_once($CFG->dirroot.'/user/lib.php');

/**
 * Abstract object for converting a data object to Moodle.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user extends base {
    /**
     * This function takes a data object and attempts to apply it to Moodle.
     *
     * @param data\base $data A data object to process.
     */
    public function convert_to_moodle(\enrol_lmb\local\data\base $data) {
        global $DB;

        if (!($data instanceof data\person)) {
            throw new \coding_exception('Expected instance of data\person to be passed.');
        }

        $settings = $this->settings;

        $this->data = $data;

        // First see if we are going to be working with an existing or new user.
        $new = false;
        $user = $this->find_existing_user();
        if (empty($user)) {
            if (!(bool)$settings->get('createnewusers')) {
                // Don't create a new user if not enabled.
                logging::instance()->log_line('Not creating new users');
                return;
            }
            $new = true;
            $user = $this->create_new_user_object();
        }

        // Check if this user's email address is allowed.
        if ($new && !$this->check_email_domain()) {
            // We allow different log levels based on a setting.
            $loglevel = logging::ERROR_WARN;
            if ((bool)$settings->get('donterroremail')) {
                $loglevel = logging::ERROR_NONE;
            }
            logging::instance()->log_line('User email not allowed by email domain settings.', $loglevel);
            return;
        }

        $username = $this->get_username();

        if (empty($username) && $settings->get('sourcedidfallback')) {
            // Fallback to the sourcedid if we can't find a username.
            $username = (string)$this->data->sdid;
        }

        if (!empty($username)) {
            $user->username = $username;
        } else {
            if (empty($user->username)) {
                logging::instance()->log_line('No username could be determined for user. Cannot create.', logging::ERROR_NOTICE);
                return;
            } else {
                $error = 'User has no username with current settings. Keeping '.$user->username.'.';
                logging::instance()->log_line($error, logging::ERROR_WARN);
            }
        }

        if ($new) {
            $auth = $settings->get('auth');
            if (!empty($auth)) {
                $user->auth = $auth;
            }
        }

        $user->idnumber = $this->data->sdid;

        if ($new || $settings->get('forceemail')) {
            if (!empty($this->data->email)) {
                if ((bool)$settings->get('lowercaseemails')) {
                    $user->email = strtolower($this->data->email);
                } else {
                    $user->email = $this->data->email;
                }
            } else {
                $user->email = '';
            }
        }

        $nickname = false;
        if (isset($this->data->nickname)) {
            $nickname = $this->data->nickname;
        }

        if ($new || $settings->get('forcefirstname')) {
            if ($nickname && $settings->get('nickname') == settings::USER_NICK_FIRST) {
                $user->firstname = $nickname;
            } else {
                $user->firstname = $this->data->givenname;
            }
        }

        if ($new || $settings->get('forcealtname')) {
            if ($nickname && $settings->get('nickname') == settings::USER_NICK_ALT) {
                $user->alternatename = $nickname;
            }
        }

        if ($new || $settings->get('forcelastname')) {
            $user->lastname = $this->data->familyname;
        }




        // TODO - Need to make sure there won't be a username collision.



        try {
            if ($new) {
                logging::instance()->log_line('Creating new Moodle user');
                $userid = user_create_user($user, false, true);
            }  else {
                logging::instance()->log_line('Updating Moodle user');
                user_update_user($user, false, true);
            }
        } catch (\moodle_exception $e) {
            // TODO - catch exception and pass back up to message.
            $error = 'Fatal exception while inserting/updating user. '.$e->getMessage();
            logging::instance()->log_line($error, logging::ERROR_MAJOR);
            throw $e;
        }
    }

    /**
     * Checks if this is a allowed user based on createusersemaildomain and ignoredomaincase.
     *
     * @return bool True if the user is allowed, false if not.
     */
    protected function check_email_domain() {
        $domain = $this->settings->get('createusersemaildomain');

        // We allow this if the setting is empty.
        if (empty($domain)) {
            return true;
        }

        if (empty($this->data->email)) {
            return false;
        }

        // Extract the domain from the email address.
        $emaildomain = explode('@', $this->data->email);
        if (count($emaildomain) !== 2) {
            // Invalid email address.
            return false;
        }
        $emaildomain = $emaildomain[1];

        if ($this->settings->get('ignoredomaincase')) {
            $matchappend = 'i';
        } else {
            $matchappend = '';
        }

        if (!preg_match('/^'.$domain.'$/'.$matchappend, $emaildomain)) {
            // If the match failed, then we return false.
            return false;
        }

        return true;
    }

    /**
     * Find an existing user record for this instance.
     *
     * @return false|\stdClass User object or false if not found.
     */
    protected function find_existing_user() {
        global $DB;

        // First try to find based on the idnumber/sdid.
        $existing = self::get_user_for_sdid($this->data->sdid);

        if ($existing) {
            return $existing;
        }

        // If we get here, and colsolidate usernames isn't set, then we didn't find it.
        if (!$this->settings->get('consolidateusernames')) {
            return false;
        }

        // See if we can find a user with the same username, and now ID number.
        $username = $this->get_username();

        if (empty($username)) {
            return false;
        }

        $existing = $DB->get_record('user', array('username' => $username));

        if (!$existing) {
            return false;
        }

        if (!empty($existing->idnumber)) {
            $error = "Existing user with username {$username} found, but has non-matching ID Number.";
            logging::instance()->log_line($error, logging::ERROR_NOTICE);
            return false;
        }

        return $existing;
    }

    /**
     * Returns a user record for the passed sdid.
     *
     * @param string $sdid
     * @return false|\stdClass
     */
    public static function get_user_for_sdid($sdid) {
        global $DB;

        return $DB->get_record('user', array('idnumber' => $sdid));
    }

    /**
     * Create a new user object for this instance.
     *
     * @return \stdClass A basic new user object to work with.
     */
    protected function create_new_user_object() {
        $user = new \stdClass();

        return $user;
    }

    /**
     * Find the proper username for this user.
     *
     * @return false|string The username, or false if can't be determined.
     */
    protected function get_username() {

        $username = false;
        switch ($this->settings->get('usernamesource')) {
            case (settings::USER_NAME_EMAIL):
                if (isset($this->data->email)) {
                    $username = $this->data->email;
                }
                break;
            case (settings::USER_NAME_EMAILNAME):
                if (isset($this->data->email) && preg_match('{(.+?)@.*?}is', $this->data->email, $matches)) {
                    $username = trim($matches[1]);
                }
                break;
            case (settings::USER_NAME_LOGONID):
                if (isset($this->data->logonid)) {
                    $username = $this->data->logonid;
                }
                break;
            case (settings::USER_NAME_SCTID):
                if (isset($this->data->sctid)) {
                    $username = $this->data->sctid;
                }
                break;
            case (settings::USER_NAME_EMAILID):
                if (isset($this->data->emailid)) {
                    $username = $this->data->emailid;
                }
                break;
            case (settings::USER_NAME_OTHER):
                $otherid = $this->settings->get('otheruserid');
                if (!empty($otherid) && isset($this->data->userid[$otherid]->userid)) {
                    $username = $this->data->userid[$otherid]->userid;
                }
                break;
        }

        if (empty($username)) {
            return false;
        }

        // Moodle requires usernames to be lowercase.
        $username = strtolower($username);

        return $username;
    }
}
