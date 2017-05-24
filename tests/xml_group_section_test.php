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

class xml_group_section_testcase extends xml_helper {
    public function test_conversion() {
        global $CFG;
        $node = $this->get_node_for_file($CFG->dirroot.'/enrol/lmb/tests/fixtures/section.xml');

        $converter = new \enrol_lmb\local\xml\group();

        $section = $converter->process_xml_to_data($node);
        $this->assertInstanceOf('\\enrol_lmb\\local\\data\\section', $section);

        $this->assertEquals('Test SCT Banner', $section->sdidsource);
        $this->assertEquals('10001.201640', $section->sdid);
        $this->assertEquals('Course Title', $section->title);
        $this->assertEquals('English Dept', $section->deptname);

        $this->assertEquals('1472515200', $section->begindate);
        $this->assertEquals('1481932800', $section->enddate);

        $this->assertEquals('Test SCT Banner', $section->termsdidsource);
        $this->assertEquals('201640', $section->termsdid);
        $this->assertEquals('Test SCT Banner', $section->coursesdidsource);
        $this->assertEquals('ENG-101', $section->coursesdid);

        $this->assertEquals('0', $section->beginrestrict);
        $this->assertEquals('1', $section->endrestrict);

        $this->assertEquals('0', $section->enrollallowed);
        $this->assertEquals('1', $section->enrollaccept);

        $this->assertEquals('10001', $section->crn);
        $this->assertEquals('ENG-101-001', $section->rubric);

        $this->assertCount(2, $section->events);
        $this->assertEquals('Section Meeting', $section->events[0]->eventdescription);
        $this->assertEquals('2016-08-30', $section->events[0]->begindate);
        $this->assertEquals('2016-12-17', $section->events[0]->enddate);
        $this->assertEquals('mf', $section->events[0]->daysofweek);
        $this->assertEquals('08:00:00', $section->events[0]->begintime);
        $this->assertEquals('09:07:00', $section->events[0]->endtime);
        $this->assertEquals('HH 113', $section->events[0]->location);

        $this->assertEquals('Section Meeting', $section->events[1]->eventdescription);
        $this->assertEquals('2016-08-30', $section->events[1]->begindate);
        $this->assertEquals('2016-12-17', $section->events[1]->enddate);
        $this->assertEquals('w', $section->events[1]->daysofweek);
        $this->assertEquals('10:00:00', $section->events[1]->begintime);
        $this->assertEquals('11:07:00', $section->events[1]->endtime);
        $this->assertEquals('HH 113', $section->events[1]->location);

        $this->assertEquals('MOODLE', $section->deliverysystem);
    }
}