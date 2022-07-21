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
 * Tertiary navigation selector group item renderable test.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \core\navigation\output\tertiary\navigation_selector_group_item
 */
class navigation_selector_group_item_test extends \advanced_testcase {

    /**
     * Test add_action_item().
     *
     * @covers ::add_action_item
     * @dataProvider test_add_item_provider
     * @param array $actionitems Array of navigation selector action items.
     * @param string|null $expectedexceptionmessage The expected exception message.
     * @param string|null $expectedactiveitemname The name (title) of the expected active item.
     * @param array $expecteditems Array of expected navigation selector items present within the navigation selector.
     */
    public function test_add_action_item(array $actionitems, ?string $expectedexceptionmessage,
            ?string $expectedactiveitemname = null, array $expecteditems = []) {
        $navigationselectorgroup = new navigation_selector_group_item('Group');

        if ($expectedexceptionmessage) {
            $this->expectException('coding_exception');
            $this->expectExceptionMessage($expectedexceptionmessage);
        }

        foreach ($actionitems as $actionitem) {
            $navigationselectorgroup->add_action_item($actionitem);
        }

        $actualactiveitem = $navigationselectorgroup->get_active_action_item();
        $actualactiveitemname = $actualactiveitem ? $actualactiveitem->get_name() : null;

        $this->assertEquals($expectedactiveitemname, $actualactiveitemname);
        $this->assertEquals($expecteditems, $navigationselectorgroup->get_action_items());
    }

    /**
     * Data provider for test_add_action_item().
     *
     * @return array
     */
    public function test_add_item_provider(): array {
        return [
            'Adding multiple action items; only one is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new \moodle_url('/'), true),
                    new navigation_selector_action_item('Action item 3', new \moodle_url('/'), false),
                ],
                null,
                'Action item 2',
                [
                    new navigation_selector_action_item('Action item 1', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new \moodle_url('/'), true),
                    new navigation_selector_action_item('Action item 3', new \moodle_url('/'), false)
                ],
            ],
            'Adding multiple action items; none is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 3', new \moodle_url('/'), false),
                ],
                null,
                null,
                [
                    new navigation_selector_action_item('Action item 1', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 3', new \moodle_url('/'), false),
                ],
            ],
            'Adding multiple action items; more than one is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new \moodle_url('/'), true),
                    new navigation_selector_action_item('Action item 2', new \moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 3', new \moodle_url('/'), true),
                ],
                'Cannot add more then one active action item into the tertiary navigation selector group.',
            ],
        ];
    }

    /**
     * Test export_for_template().
     *
     * @covers ::export_for_template
     * @dataProvider test_export_for_template_provider
     * @param navigation_selector_group_item $navselectorgroupitem The navigation selector group item object.
     * @param array $expectedexportdata The expected export data.
     */
    public function test_export_for_template(navigation_selector_group_item $navselectorgroupitem, array $expectedexportdata) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('core');
        $this->assertEquals($expectedexportdata, $navselectorgroupitem->export_for_template($renderer));
    }

    /**
     * Data provider for test_export_for_template().
     *
     * @return array
     */
    public function test_export_for_template_provider(): array {
        return [
            'Navigation selector group item with multiple action items.' => [
                (new navigation_selector_group_item('Group'))
                    ->add_action_item(new navigation_selector_action_item('Action item 1', new \moodle_url('example1.php'), false))
                    ->add_action_item(new navigation_selector_action_item('Action item 2', new \moodle_url('example2.php'), true)),
                [
                    'name' => 'Group',
                    'actionitems' => [
                        [
                            'name' => 'Action item 1',
                            'action' => new \moodle_url('example1.php'),
                            'isactive' => false,
                            'attributes' => [],
                        ],
                        [
                            'name' => 'Action item 2',
                            'action' => new \moodle_url('example2.php'),
                            'isactive' => true,
                            'attributes' => [],
                        ],
                    ],
                ],
            ],
            'Navigation selector group item with no action items.' => [
                (new navigation_selector_group_item('Group')),
                [
                    'name' => 'Group',
                ],
            ],
        ];
    }
}
