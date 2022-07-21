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

use moodle_url;

/**
 * Tertiary navigation selector renderable test.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \core\navigation\output\tertiary\navigation_selector
 */
class navigation_selector_test extends \advanced_testcase {

    /**
     * Test add_item().
     *
     * @covers ::add_item
     * @dataProvider test_add_item_provider
     * @param array $items Array of navigation selector items.
     * @param string|null $expectedexceptionmessage The expected exception message.
     * @param string|null $expectedactiveitemname The name (title) of the expected active item.
     * @param array $expecteditems Array of expected navigation selector items present within the navigation selector.
     */
    public function test_add_item(array $items, ?string $expectedexceptionmessage, ?string $expectedactiveitemname = null,
            array $expecteditems = []) {
        $navigationselector = new navigation_selector();

        if ($expectedexceptionmessage) {
            $this->expectException('coding_exception');
            $this->expectExceptionMessage($expectedexceptionmessage);
        }

        foreach ($items as $item) {
            $navigationselector->add_item($item);
        }

        $actualactiveitem = $navigationselector->get_active_action_item();
        $actualactiveitemname = $actualactiveitem ? $actualactiveitem->get_name() : null;

        $this->assertEquals($expectedactiveitemname, $actualactiveitemname);
        $this->assertEquals($expecteditems, $navigationselector->get_items());
    }

    /**
     * Data provider for test_add_item().
     *
     * @return array
     */
    public function test_add_item_provider(): array {
        return [
            'Adding only action items; only one is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new moodle_url('/'), true),
                ],
                null,
                'Action item 2',
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new moodle_url('/'), true),
                ],
            ],
            'Adding only action items; none is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new moodle_url('/'), false),
                ],
                null,
                null,
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), false),
                    new navigation_selector_action_item('Action item 2', new moodle_url('/'), false),
                ],
            ],
            'Adding only action items; more than one is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), true),
                    new navigation_selector_action_item('Action item 2', new moodle_url('/'), true),
                ],
                'Cannot add more then one active action item to the tertiary navigation selector.',
            ],
            'Adding action and group items; one action item within a group is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), false),
                    (new navigation_selector_group_item('Group item 1'))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 1', new moodle_url('/'),
                            false))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 2', new moodle_url('/'),
                            true)),
                ],
                null,
                'Group 1 - action item 2',
                [
                    new navigation_selector_action_item('Action item 1', new \moodle_url('/'), false),
                    (new navigation_selector_group_item('Group item 1'))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 1', new moodle_url('/'),
                            false))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 2', new moodle_url('/'),
                            true)),
                ],
            ],
            'Adding action and group items; more than one action item is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), true),
                    (new navigation_selector_group_item('Group item 1'))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 1', new moodle_url('/'),
                            false))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 2', new moodle_url('/'),
                            true)),
                ],
                'Cannot add more then one active action item to the tertiary navigation selector.',
            ],
            'Adding action and group items; more than one action item within the groups is labeled as active.' => [
                [
                    new navigation_selector_action_item('Action item 1', new moodle_url('/'), false),
                    (new navigation_selector_group_item('Group item 1'))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 1', new moodle_url('/'),
                            false))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - action item 2', new moodle_url('/'),
                            true)),
                    (new navigation_selector_group_item('Group item 2'))
                        ->add_action_item(new navigation_selector_action_item('Group 2 - action item 1', new moodle_url('/'),
                            true))
                ],
                'Cannot add more then one active action item to the tertiary navigation selector.',
            ],
        ];
    }

    /**
     * Test export_for_template().
     *
     * @covers ::export_for_template
     * @dataProvider test_export_for_template_provider
     * @param navigation_selector $navigationselector The navigation selector object.
     * @param array $expectedexportdata The expected export data.
     */
    public function test_export_for_template(navigation_selector $navigationselector, array $expectedexportdata) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('core');
        $this->assertEquals($expectedexportdata, $navigationselector->export_for_template($renderer));
    }

    /**
     * Data provider for test_export_for_template().
     *
     * @return array
     */
    public function test_export_for_template_provider(): array {
        return [
            'Navigation selector with action items.' => [
                (new navigation_selector('Tertiary navigation'))
                    ->add_item(new navigation_selector_action_item('Action item 1', new moodle_url('example1.php'), false))
                    ->add_item(new navigation_selector_action_item('Action item 2', new moodle_url('example2.php'), true)),
                [
                    'activeitemtext' => 'Action item 2',
                    'items' => [
                        [
                            'name' => 'Action item 1',
                            'action' => new moodle_url('example1.php'),
                            'isactive' => false,
                            'isgroup' => false,
                            'attributes' => [],
                        ],
                        [
                            'name' => 'Action item 2',
                            'action' => new moodle_url('example2.php'),
                            'isactive' => true,
                            'isgroup' => false,
                            'attributes' => [],
                        ],
                    ],
                    'title' => 'Tertiary navigation',
                ],
            ],
            'Navigation selector with group items.' => [
                (new navigation_selector())
                    ->add_item((new navigation_selector_group_item('Group item 1'))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - item 1', new moodle_url('example11.php'),
                            false))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - item 2', new moodle_url('example12.php'),
                            false)))
                    ->add_item((new navigation_selector_group_item('Group item 2'))
                        ->add_action_item(new navigation_selector_action_item('Group 2 - item 1', new moodle_url('example21.php'),
                            true))
                        ->add_action_item(new navigation_selector_action_item('Group 2 - item 2', new moodle_url('example22.php'),
                            false))),
                [
                    'activeitemtext' => 'Group 2 - item 1',
                    'items' => [
                        [
                            'name' => 'Group item 1',
                            'actionitems' => [
                                [
                                    'name' => 'Group 1 - item 1',
                                    'action' => new moodle_url('example11.php'),
                                    'isactive' => false,
                                    'attributes' => [],
                                ],
                                [
                                    'name' => 'Group 1 - item 2',
                                    'action' => new moodle_url('example12.php'),
                                    'isactive' => false,
                                    'attributes' => [],
                                ],

                            ],
                            'isgroup' => true,
                        ],
                        [
                            'name' => 'Group item 2',
                            'actionitems' => [
                                [
                                    'name' => 'Group 2 - item 1',
                                    'action' => new \moodle_url('example21.php'),
                                    'isactive' => true,
                                    'attributes' => [],
                                ],
                                [
                                    'name' => 'Group 2 - item 2',
                                    'action' => new \moodle_url('example22.php'),
                                    'isactive' => false,
                                    'attributes' => [],
                                ],

                            ],
                            'isgroup' => true,
                        ],
                    ],
                    'title' => null,
                ],
            ],
            'Navigation selector with action and group items.' => [
                (new navigation_selector('Tertiary navigation'))
                    ->add_item(new navigation_selector_action_item('Action item 1', new \moodle_url('example1.php'), false))
                    ->add_item((new navigation_selector_group_item('Group item 1'))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - item 1', new \moodle_url('example11.php'),
                            false))
                        ->add_action_item(new navigation_selector_action_item('Group 1 - item 2', new \moodle_url('example12.php'),
                            false)))
                    ->add_item(new navigation_selector_action_item('Action item 2', new \moodle_url('example2.php'), true)),
                [
                    'activeitemtext' => 'Action item 2',
                    'items' => [
                        [
                            'name' => 'Action item 1',
                            'action' => new \moodle_url('example1.php'),
                            'isactive' => false,
                            'attributes' => [],
                            'isgroup' => false,
                        ],
                        [
                            'name' => 'Group item 1',
                            'actionitems' => [
                                [
                                    'name' => 'Group 1 - item 1',
                                    'action' => new \moodle_url('example11.php'),
                                    'isactive' => false,
                                    'attributes' => [],
                                ],
                                [
                                    'name' => 'Group 1 - item 2',
                                    'action' => new \moodle_url('example12.php'),
                                    'isactive' => false,
                                    'attributes' => [],
                                ],

                            ],
                            'isgroup' => true,
                        ],
                        [
                            'name' => 'Action item 2',
                            'action' => new \moodle_url('example2.php'),
                            'isactive' => true,
                            'attributes' => [],
                            'isgroup' => false,
                        ],
                    ],
                    'title' => 'Tertiary navigation',
                ],
            ],
            'Navigation selector with no active action items.' => [
                (new navigation_selector())
                    ->add_item(new navigation_selector_action_item('Action item 1', new \moodle_url('example1.php'), false))
                    ->add_item(new navigation_selector_action_item('Action item 2', new \moodle_url('example2.php'), false)),
                [
                    'activeitemtext' => null,
                    'items' => [
                        [
                            'name' => 'Action item 1',
                            'action' => new \moodle_url('example1.php'),
                            'isactive' => false,
                            'attributes' => [],
                            'isgroup' => false,
                        ],
                        [
                            'name' => 'Action item 2',
                            'action' => new \moodle_url('example2.php'),
                            'isactive' => false,
                            'attributes' => [],
                            'isgroup' => false,
                        ],
                    ],
                    'title' => null
                ],
            ],
        ];
    }
}
