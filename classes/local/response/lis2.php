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
 * A responder for LIS 2 messages.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2017 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_lmb\local\response;

defined('MOODLE_INTERNAL') || die();

class lis2 extends xml {

    protected $namespace = null;

    protected $responsetags = array('REPLACECOURSESECTIONREQUEST' => 'replaceCourseSectionResponse');

    public function get_response_body() {
        $response = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">';

        // First do the header if we can get it.
        if ($this->controller) {
            $header = $controller->get_current_header();

            if ($header) {
                $version = $xmlobj->IMSX_VERSION->get_value();
                $messageid = $xmlobj->IMSX_MESSAGEIDENTIFIER->get_value();
                $headernamespace = $xmlobj->get_attribute("XMLNS:XSI");
                $responseid = uniqid();

                $status = '<imsx_statusInfo>
                               <imsx_codeMajor>success</imsx_codeMajor>
                               <imsx_severity>status</imsx_severity>
                               <imsx_messageRefIdentifier>'.$messageid.'</imsx_messageRefIdentifier>
                               <imsx_description/>
                               <imsx_codeMinor>
                                  <imsx_codeMinorField>
                                     <imsx_codeMinorFieldName>TargetEndSystem</imsx_codeMinorFieldName>
                                     <imsx_codeMinorFieldValue>fullsuccess</imsx_codeMinorFieldValue>
                                  </imsx_codeMinorField>
                               </imsx_codeMinor>
                            </imsx_statusInfo>';

                $response .= '<soapenv:Header>'.
                             '<imsx_syncResponseHeaderInfo xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                             'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="'.$headernamespace.'">'.
                             '<imsx_version>'.$version.'</imsx_version>'.
                             '<imsx_messageIdentifier>'.$responseid.'</imsx_messageIdentifier>'.
                             $status.
                             '</imsx_syncResponseHeaderInfo>'.
                             '</soapenv:Header>';
            }
        }

        // Now the body response.
        $response .= '<soapenv:Body>';

        if ($responsetag = $this->get_response_tag()) {
            $response .= '<'.$reqtype.'Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                         'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="'.$this->namespace.'" />';
        }

        $response .= '</soapenv:Body>';

        $response .= '</soapenv:Envelope>';


    }

    public function set_namespace($namespace) {
        $this->namespace = $namespace;
    }


    protected function get_response_tag() {
        $roottag = $this->message->get_root_tag();
        if (isset($this->responsetags[$roottag])) {
            return $this->responsetags[$roottag];
        }

        return false;
    }
}