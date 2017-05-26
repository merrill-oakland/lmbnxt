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
 * Works on types of messages.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb\local\lis2;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for working with message types.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2017 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class member_replace extends base {
    const NAMESPACE_DEF = "www.imsglobal.org/services/lis/mms2p0/wsdl11/sync/imsmms_v2p0";

    const MAPPING_PATH = '/enrol/lmb/classes/local/lis2/mappings/member_replace.json';

    const DATA_CLASS = '\\enrol_lmb\\local\\data\\member_person';
    // TODO loop handling - confirm not needed in spec.
    /**
     * Basic constructor.
     */
    public function __construct() {
        $this->load_mappings();
    }

    /**
     * Process the role type nodes.
     *
     * @param xml_node|array $node The XML node to process, or array of nodes
     * @param array $mapping The mapping for the field
     */
    protected function process_roletype_node($node, $mapping) {
        $role = $node->get_value();
        $this->dataobj->lis_roletype = $role;

        // TODO - should be based on settings.
        if (strcasecmp("editingteacher", $role) === 0) {
            $this->dataobj->roletype = "02";
        } else if (strcasecmp("student", $role) === 0) {
            $this->dataobj->roletype = "01";
        } else {
            // TODO - better handling here...
            $this->dataobj->roletype = "00";
        }
    }

    /**
     * Process the list status nodes.
     *
     * @param xml_node|array $node The XML node to process, or array of nodes
     * @param array $mapping The mapping for the field
     */
    protected function process_status_node($node, $mapping) {
        // So far we know of 'Active', but assume there are probably others...
        $active = $node->get_value();
        $this->dataobj->lis_active = $active;

        if (strcasecmp("Active", $active) === 0) {
            $this->dataobj->status = 1;
        }

        // TODO better detection of other values...
        $this->dataobj->status = 0;
    }

    // TODO - Student fields.
    // TODO - rescript fields.
}
