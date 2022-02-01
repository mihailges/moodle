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

namespace theme_boost;

/**
 * Test the boostnavbar file
 *
 * @package    theme_boost
 * @copyright  2021 Peter Dias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class boostnavbar_test extends \advanced_testcase {
    /**
     * Provider for test_remove_no_link_items
     * The setup and expected arrays are defined as an array of 'nodekey' => $hasaction
     *
     * @return array
     */
    public function remove_no_link_items_provider(): array {
        return [
            'All nodes have links links including leaf node' => [
                [
                    'node1' => true,
                    'node2' => true,
                    'node3' => true,
                ],
                [
                    'Home' => true,
                    'Courses' => true,
                    'tc_1' => true,
                    'node1' => true,
                    'node2' => true,
                    'node3' => true,
                ]
            ],
            'Only some parent nodes have links. Leaf node has a link.' => [
                [
                    'node1' => false,
                    'node2' => true,
                    'node3' => true,
                ],
                [
                    'Home' => true,
                    'Courses' => true,
                    'tc_1' => true,
                    'node2' => true,
                    'node3' => true,
                ]
            ],
            'All parent nodes do not have links. Leaf node has a link.' => [
                [
                    'node1' => false,
                    'node2' => false,
                    'node3' => true,
                ],
                [
                    'Home' => true,
                    'Courses' => true,
                    'tc_1' => true,
                    'node3' => true,
                ]
            ],
            'All parent nodes have links. Leaf node does not has a link.' => [
                [
                    'node1' => true,
                    'node2' => true,
                    'node3' => false,
                ],
                [
                    'Home' => true,
                    'Courses' => true,
                    'tc_1' => true,
                    'node1' => true,
                    'node2' => true,
                    'node3' => false,
                ]
            ],
            'All parent nodes do not have links. Leaf node does not has a link.' => [
                [
                    'node1' => false,
                    'node2' => false,
                    'node3' => false,
                ],
                [
                    'Home' => true,
                    'Courses' => true,
                    'tc_1' => true,
                    'node3' => false,
                ]
            ],
            'Some parent nodes do not have links. Leaf node does not has a link.' => [
                [
                    'node1' => true,
                    'node2' => false,
                    'node3' => false,
                ],
                [
                    'Home' => true,
                    'Courses' => true,
                    'tc_1' => true,
                    'node1' => true,
                    'node3' => false,
                ]
            ]
        ];
    }
    /**
     * Test the remove_no_link_items function
     *
     * @dataProvider remove_no_link_items_provider
     * @param array $setup
     * @param array $expected
     * @throws \ReflectionException
     */
    public function test_remove_no_link_items(array $setup, array $expected) {
        global $PAGE;

        $this->resetAfterTest();
        // Unfortunate hack needed because people use global $PAGE around the place.
        $PAGE->set_url('/');
        $course = $this->getDataGenerator()->create_course();
        $page = new \moodle_page();
        $page->set_course($course);
        $page->set_url(new \moodle_url('/course/view.php', array('id' => $course->id)));
        // A dummy url to use. We don't care where it's pointing to.
        $url = new \moodle_url('/');
        foreach ($setup as $node => $hasaction) {
            $page->navbar->add($node, $hasaction ? $url : null);
        }

        $boostnavbar = $this->getMockBuilder(boostnavbar::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $rc = new \ReflectionClass(boostnavbar::class);
        $rcp = $rc->getProperty('items');
        $rcp->setAccessible(true);
        $rcp->setValue($boostnavbar, $page->navbar->get_items());

        // Make the call to the function.
        $rcm = $rc->getMethod('remove_no_link_items');
        $rcm->setAccessible(true);
        $rcm->invoke($boostnavbar);

        // Get the value for the class variable that the function modifies.
        $values = $rcp->getValue($boostnavbar);
        $actual = [];
        foreach ($values as $value) {
            $actual[$value->text] = $value->has_action();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provider for test_remove_duplicated_link_items
     *
     * @return array
     */
    public function remove_duplicated_link_items_provider(): array {
        return [
            'Breadcrumbs that have nodes with the identical action url' => [
                [
                    'Node 1' => new \moodle_url('/example1.php'),
                    'Node 2' => new \moodle_url('/example2.php', ['id' => 1]),
                    'Node 3' => new \moodle_url('/example2.php', ['id' => 1]),
                    'Node 4' => new \moodle_url('/example4.php', ['id' => 1])
                ],
                ['Home', 'Node 1', 'Node 2', 'Node 4']
            ],
            'Breadcrumbs that do not have nodes with the identical action url.' => [
                [
                    'Node 1' => new \moodle_url('/example1.php'),
                    'Node 2' => new \moodle_url('/example2.php', ['id' => 1]),
                    'Node 3' => new \moodle_url('/example2.php', ['id' => 2]),
                    'Node 4' => new \moodle_url('/example4.php', ['id' => 1])
                ],
                ['Home', 'Node 1', 'Node 2', 'Node 3', 'Node 4']
            ],
        ];
    }

    /**
     * Test the remove_duplicated_link_items function
     *
     * @dataProvider remove_duplicated_link_items_provider
     * @param array $navbarnodes The array containing the text => moodle_url of the nodes to be added to the navbar
     * @param array $expected The array containing the text of the expected navbar nodes
     */
    public function test_remove_duplicated_link_items(array $navbarnodes, array $expected) {
        $this->resetAfterTest();
        $page = new \moodle_page();
        $page->set_url('/');

        // Add the navbar nodes.
        foreach ($navbarnodes as $text => $url) {
            $page->navbar->add($text, $url, \navigation_node::TYPE_CUSTOM);
        }

        $boostnavbar = $this->getMockBuilder(boostnavbar::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $rc = new \ReflectionClass(boostnavbar::class);
        $rcp = $rc->getProperty('items');
        $rcp->setAccessible(true);
        $rcp->setValue($boostnavbar, $page->navbar->get_items());

        // Make the call to the function.
        $rcm = $rc->getMethod('remove_duplicated_link_items');
        $rcm->setAccessible(true);
        $rcm->invoke($boostnavbar);

        // Get the value for the class variable that the function modifies.
        $values = $rcp->getValue($boostnavbar);
        $actual = [];
        foreach ($values as $value) {
            $actual[] = $value->text;
        }
        $this->assertEquals($expected, $actual);
    }


    /**
     * Provider for test_remove_items_that_exist_in_nav_menu
     *
     * @return array
     */
    public function remove_items_that_exist_in_nav_menu_provider(): array {
        return [
            'The breadcrumb node exists in the primary navigation menu.' => [
                'primary',
                [
                    [
                        'key' => 'node1',
                        'text' => 'Node 1'
                    ]
                ],
                [
                    'node1' => 'Node 1',
                    'node2' => 'Node 2',
                    'node3' => 'Node 3'
                ],
                ['Home', 'Node 2', 'Node 3']
            ],
            'The breadcrumb node exists in the secondary navigation menu.' => [
                'secondary',
                [
                    [
                        'key' => 'node2',
                        'text' => 'Node 2'
                    ]
                ],
                [
                    'node1' => 'Node 1',
                    'node2' => 'Node 2',
                    'node3' => 'Node 3'
                ],
                ['Home', 'Node 1', 'Node 3']
            ],
            'Multiple breadcrumb nodes exist in the secondary navigation menu.' => [
                'secondary',
                [
                    [
                        'key' => 'node2',
                        'text' => 'Node 2'
                    ],
                    [
                        'key' => 'node3',
                        'text' => 'Node 3'
                    ]
                ],
                [
                    'node1' => 'Node 1',
                    'node2' => 'Node 2',
                    'node3' => 'Node 3'
                ],
                ['Home', 'Node 1']
            ],
            'The breadcrumb node does not exist in the secondary navigation menu.' => [
                'secondary',
                [
                    [
                        'key' => 'node4',
                        'text' => 'Node 4'
                    ]
                ],
                [
                    'node1' => 'Node 1',
                    'node2' => 'Node 2',
                    'node3' => 'Node 3'
                ],
                ['Home', 'Node 1', 'Node 2', 'Node 3']
            ],
        ];
    }

    /**
     * Test the remove_items_that_exist_in_nav_menu function
     *
     * @dataProvider remove_items_that_exist_in_nav_menu_provider
     * @param string $navmenu The name of the navigation menu we would like to use (primary or secondary)
     * @param array $navmenunodes The array containing the key and text of the nodes to be added to the navigation menu
     * @param array $navbarnodes Array containing the key => text of the nodes to be added to the navbar
     * @param array $expected Array containing the text of the expected navbar nodes after the filtering
     */
    public function test_remove_items_that_exist_in_nav_menu(string $navmenu, array $navmenunodes, array $navbarnodes,
            array $expected) {
        global $PAGE;

        // Unfortunate hack needed because people use global $PAGE around the place.
        $PAGE->set_url('/');
        $this->resetAfterTest();
        $page = new \moodle_page();
        $page->set_url('/');

        switch ($navmenu) {
            case 'primary':
                $navigationmenu = new \core\navigation\views\primary($page);
            case 'secondary':
                $navigationmenu = new \core\navigation\views\secondary($page);
        }

        $navigationmenu->initialise();
        // Add the additional nodes to the navigation menu.
        foreach ($navmenunodes as $navmenunode) {
            $navigationmenu->add($navmenunode['text'], null, \navigation_node::TYPE_CUSTOM, null, $navmenunode['key']);
        }

        // Add the additional navbar nodes.
        foreach ($navbarnodes as $key => $text) {
            $page->navbar->add($text, null, \navigation_node::TYPE_CUSTOM, null, $key);
        }

        $boostnavbar = $this->getMockBuilder(boostnavbar::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $rc = new \ReflectionClass(boostnavbar::class);
        $rcp = $rc->getProperty('items');
        $rcp->setAccessible(true);
        $rcp->setValue($boostnavbar, $page->navbar->get_items());

        // Make the call to the function.
        $rcm = $rc->getMethod('remove_items_that_exist_in_nav_menu');
        $rcm->setAccessible(true);
        $rcm->invoke($boostnavbar, $navigationmenu);

        // Get the value for the class variable that the function modifies.
        $values = $rcp->getValue($boostnavbar);
        $actual = [];
        foreach ($values as $value) {
            $actual[] = $value->text;
        }
        $this->assertEquals($expected, $actual);
    }
}
