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
 * Tests for the xml parser.
 *
 * @package    enrol_lmb
 * @author     Eric Merrill <merrill@oakland.edu>
 * @copyright  2016 Oakland University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/enrol/lmb/tests/helper.php');

class xml_person_testcase extends xml_helper {
    public function test_conversion() {
        global $CFG;
        $node = $this->get_node_for_file($CFG->dirroot.'/enrol/lmb/tests/fixtures/person.xml');
        $converter = new \enrol_lmb\local\xml\person();
        $person = $converter->process_xml_to_data($node);

        $this->assertInstanceOf('\\enrol_lmb\\local\\data\\person', $person);
        $this->assertEquals('Test SCT Banner', $person->sdidsource);
        $this->assertEquals('1000001', $person->sdid);
        $this->assertEquals('Test SCT Banner', $person->sdidsource);

        $this->assertInternalType('array', $person->userid);
        $this->assertCount(5, $person->userid);

        $userid = $person->userid['Logon ID'];
        $this->assertEquals('LoginIDUserid', $userid->userid);
        $this->assertEquals('012345', $userid->password);

        $userid = $person->userid['SCTID'];
        $this->assertEquals('SCTIDUserid', $userid->userid);
        $this->assertEquals('234567', $userid->password);

        $userid = $person->userid['UDCIdentifier'];
        $this->assertEquals('UDCIdUserid', $userid->userid);
        $this->assertEquals('456789', $userid->password);

        $userid = $person->userid['Email ID'];
        $this->assertEquals('EmailIDUserid', $userid->userid);
        $this->assertEquals('678901', $userid->password);

        $userid = $person->userid['Other ID'];
        $this->assertEquals('OtherIDUserid', $userid->userid);
        $this->assertEquals('890123', $userid->password);

        $this->assertEquals('Test A User', $person->fullname);
        $this->assertEquals('Nick User', $person->nickname);
        $this->assertEquals('User', $person->familyname);
        $this->assertEquals('Test', $person->givenname);
        $this->assertEquals('Mr.', $person->prefix);
        $this->assertEquals('Jr.', $person->suffix);
        $this->assertEquals('A', $person->middle);
        $this->assertEquals('2', $person->gender);
        $this->assertEquals('testuser@example.com', $person->email);
        $this->assertEquals('989-555-9898', $person->televoice);
        $this->assertEquals('989-555-1212', $person->telemobile);
        $this->assertEquals('123 Fake St.', $person->streetadr);
        $this->assertEquals('Springfield', $person->city);
        $this->assertEquals('MI', $person->region);
        $this->assertEquals('55555', $person->postalcode);
        $this->assertEquals('USA', $person->country);

        $this->assertInternalType('array', $person->institutionrole);
        $this->assertCount(3, $person->institutionrole);
        $this->assertEquals('ProspectiveStudent', $person->institutionrole[0]);
        $this->assertEquals('Staff', $person->institutionrole[1]);
        $this->assertEquals('Student', $person->institutionrole[2]);

        $this->assertEquals('Undeclared', $person->major);
        $this->assertEquals('Lecturer', $person->title);
        $this->assertEquals('MS Computer Science', $person->degree);

        $this->assertInternalType('array', $person->customrole);
        $this->assertCount(2, $person->customrole);
        $this->assertEquals('ApplicantAccept', $person->customrole[0]);
        $this->assertEquals('BannerINB', $person->customrole[1]);
    }
}
