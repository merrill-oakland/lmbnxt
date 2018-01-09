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

use \enrol_lmb\local\processors\types;

/**
 * Controller class for importing files and folders.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {
    /** @var array An array of existing type processors for reuse */
//    protected $typeprocessors = array();

    /** @var array Options for this run */
    protected $options = array();

    protected $currentheader = false;

    public function import_file($path = null) {
        if (!$path) {
            $path = get_config('enrol_lmb', 'xmlpath');
        }

        $parser = new parser();
        $parser->set_controller($this);
        $parser->process_file($path);
    }

    // Takes a data object from an input source and does things to it.
//     public function process_data(local\types\base\data $data) {
//
//     }

    /**
     * Set a controller option.
     *
     * Valid keys:
     *     nodb - If true, disabled saving to db.
     *
     * @param string $key The option key
     * @param string $value The option value
     */
    public function set_option($key, $value) {
        $this->options[$key] = $value;
    }

    /**
     * Return a controller option.
     *
     * Valid keys:
     *     nodb - If true, disabled saving to db.
     *
     * @param string $key The option key
     * @return mixed
     */
    public function get_option($key) {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }

    /**
     * We specially treat header nodes.
     *
     * @param xml_node $xmlobj The XML node to work on.
     */
    public function process_header_node(local\xml_node $xmlobj) {
        $header = new \stdClass();
        $header->version = $xmlobj->IMSX_VERSION->get_value();
        $header->messageid = $xmlobj->IMSX_MESSAGEIDENTIFIER->get_value();
        $header->namespace = $xmlobj->get_attribute("XMLNS:XSI");

        $this->currentheader = $header;
    }

    /**
     * Takes a built XML node and processes it.
     *
     * @param xml_node $xmlobj The XML node to work on.
     */
    public function process_xml_object(local\xml_node $xmlobj) {

        $message = new message($this, $xmlobj);

        $message->process_to_data();
//
//         // Get the processor (cached).
//         $xmlproc = types::get_type_processor($xmlobj->get_name());
//
//         try {
//             // Convert the node to a data object.
//             $objs = $xmlproc->process_xml_to_data($xmlobj);
//
//             if (!is_array($objs)) {
//                 // Convert single object to array for later.
//                 $objs = array($objs);
//             }
//
//             // Some nodes (like membership) may return many children.
//             foreach ($objs as $obj) {
//                 $obj->log_id();
//                 if (empty($this->options['nodb'])) {
//                     $obj->save_to_db();
//                 }
//             }
//         } catch (\enrol_lmb\local\exception\message_exception $e) {
//             // There as a fatal exeption for this node.
//             logging::instance()->log_line($e->getMessage(), logging::ERROR_MAJOR);
//         }

    }
}
