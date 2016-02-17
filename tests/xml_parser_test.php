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

class xml_parser_testcase extends xml_helper {
    public function test_parser() {
        $this->resetAfterTest(true);

        $xml = '<tests><n1 a1="va1">vn1</n1><n2><nc>vnc1</nc><nc>vnc2</nc><nc>vnc3</nc></n2>'.
               '<n3 a1="va1" a2="va2"><nc>vnc1</nc></n3></tests>';

        $node = $this->get_node_for_xml($xml);

        // Check the root node.
        $this->assertEquals('tests', $node->get_name());

        // Node n1.
        $this->assertTrue(isset($node->n1));
        $this->assertInstanceOf('\\enrol_lmb\\local\\xml_node', $node->n1);
        $this->assertTrue($node->n1->has_data());
        $this->assertEquals('vn1', $node->n1->get_value());
        $this->assertEquals('n1', $node->n1->get_name());
        $this->assertEquals('va1', $node->n1->get_attribute('a1'));

        // Node n2.
        $this->assertTrue(isset($node->n2));
        $this->assertInstanceOf('\\enrol_lmb\\local\\xml_node', $node->n2);
        $this->assertFalse($node->n2->has_data());
        $this->assertNull($node->n2->get_value());

        // Children of n2.
        $this->assertInternalType('array', $node->n2->nc);
        $this->assertCount(3, $node->n2->nc);
        $this->assertEquals('vnc1', $node->n2->nc[0]->get_value());
        $this->assertEquals('vnc2', $node->n2->nc[1]->get_value());
        $this->assertEquals('vnc3', $node->n2->nc[2]->get_value());

        // Node n3.
        $this->assertTrue(isset($node->n3));
        $this->assertInstanceOf('\\enrol_lmb\\local\\xml_node', $node->n3);
        $this->assertTrue($node->n3->has_data());
        $this->assertInternalType('array', $node->n3->get_attributes());
        $this->assertCount(2, $node->n3->get_attributes());
        $this->assertEquals('va1', $node->n3->get_attributes()['a1']);
        $this->assertEquals('va2', $node->n3->get_attributes()['a2']);

    }


}
