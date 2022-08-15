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

namespace core\navigation\output\tertiary;

/**
 * Tertiary navigation selector action item renderable test.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \core\navigation\output\tertiary\navigation_selector_action_item
 */
class navigation_selector_action_item_test extends \advanced_testcase {

    /**
     * Test export_for_template().
     *
     * @covers ::export_for_template
     * @dataProvider test_export_for_template_provider
     * @param navigation_selector_action_item $navselectoractionitem The navigation selector action item object.
     * @param array $expectedexportdata The expected export data.
     */
    public function test_export_for_template(navigation_selector_action_item $navselectoractionitem, array $expectedexportdata) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('core');
        $this->assertEquals($expectedexportdata, $navselectoractionitem->export_for_template($renderer));
    }

    /**
     * Data provider for test_export_for_template().
     *
     * @return array
     */
    public function test_export_for_template_provider(): array {
        return [
            'Navigation selector action item labeled as active.' => [
                new navigation_selector_action_item('Action item', new \moodle_url('example.php'), true),
                [
                    'name' => 'Action item',
                    'action' => new \moodle_url('example.php'),
                    'isactive' => true,
                    'attributes' => [],
                ],
            ],
            'Navigation selector action item with additional attributes.' => [
                new navigation_selector_action_item('Action item', new \moodle_url('example.php'), false,
                    ['target' => '_blank']),
                [
                    'name' => 'Action item',
                    'action' => new \moodle_url('example.php'),
                    'isactive' => false,
                    'attributes' => [
                        ['name' => 'target', 'value' => '_blank'],
                    ],
                ],
            ],
        ];
    }
}
