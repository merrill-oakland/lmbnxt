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
 * The primary controller for file based imports.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb;
defined('MOODLE_INTERNAL') || die();

/**
 * Controller class for importing files and folders.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {
    protected $typeprocessors = array();

    public function import_file($path = null) {
        if (!$path) {
            $path = get_config('enrol_lmb', 'xmlpath');
        }

        $parser = new parser();
        $parser->set_controller($this);
        $parser->process_file($path);
    }

    // Takes a data object from an input source and does things to it.
    public function process_data(local\types\base\data $data) {

    }

    public function process_xml_object(local\xml_node $xmlobj) {
        $type = strtolower($xmlobj->get_name());

        if (!isset($this->typeprocessors[$type])) {
            $class = '\\enrol_lmb\\local\\xml\\'.$type;
            if (!class_exists($class)) {
                return;
            }
            $this->typeprocessors[$type] = new $class();
        }

        $xmlproc = $this->typeprocessors[$type];

        try {
            $objs = $xmlproc->process_xml_to_data($xmlobj);
//print "<pre>";print_r($objs);print "</pre>";
            if (!is_array($objs)) {
                $objs = array($objs);
            }

            foreach ($objs as $obj) {
                $obj->log_id();
                $obj->save_to_db();
            }
        } catch (\enrol_lmb\local\exception\message_exception $e) {
            logging::instance()->log_line($e->getMessage(), logging::ERROR_MAJOR);
        }

    }
}
