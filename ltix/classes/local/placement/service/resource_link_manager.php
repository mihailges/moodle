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

namespace core_ltix\local\placement\service;

use core\context;
use core_ltix\constants;
use core_ltix\local\lticore\models\resource_link;
use core_ltix\local\placement\placements_manager;
use core_ltix\local\placement\placement_repository;

/**
 * Placement resource link manager class.
 *
 * This placement service class provides standard CRUD functionality for resource link data.
 *
 * @package    core_ltix
 * @copyright  2025 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resource_link_manager {

    /** @var string The placement type string. e.g. 'mod_lti:activityplacement'. */
    private string $placementtype;

    /** @var string The component associated with the placement type. */
    private string $component;

    /** @var context The context associated with the placement type. */
    private context $context;

    /** @var int The tool ID. */
    private int $toolid;

    /**
     * Constructor.
     *
     * @param string $placementtype The placement type string. e.g. 'mod_lti:activityplacement'.
     * @param string $component The component associated with the placement type.
     * @param context $context The context associated with the placement type.
     * @param int $toolid The tool ID.
     */
    private function __construct(string $placementtype, string $component, context $context, int $toolid) {
        $this->placementtype = $placementtype;
        $this->component = $component;
        $this->context = $context;
        $this->toolid = $toolid;
    }

    /**
     * Factory method.
     *
     * This method validates the provided arguments to ensure they meet the necessary requirements for the service to be
     * used correctly. Once validation is complete, it returns a properly instantiated service object, ready for use by
     * the caller.
     *
     * @param string $placementtype The placement type string. e.g. 'mod_lti:activityplacement'.
     * @param string $component The component associated with the placement type.
     * @param context $context The context object associated with the placement type.
     * @param int $toolid The tool ID.
     * @return self
     */
    public static function create(string $placementtype, string $component, context $context, int $toolid): self {
        global $DB;

        // Validate the context. Currently, tools can only be used within the course or module context. This check ensures
        // that the provide context is one of these two types.
        // NOTE: If tool usage is expanded to support additional contexts in the future, this validation logic will need
        // to be revisited and updated accordingly.
        if (!($context instanceof \context_course || $context instanceof \context_module)) {
            throw new \coding_exception("Invalid context.");
        }

        // Validate the passed placement type.
        if (!placements_manager::is_valid_placement_type($placementtype)) {
            throw new \coding_exception("Invalid placement type.");
        }

        $placementtypecomponent = $DB->get_record('lti_placement_type', ['type' => $placementtype], 'component')->component;

        if ($component !== $placementtypecomponent) {
            throw new \coding_exception("Invalid component.");
        }

        $coursecontext = ($context instanceof \context_course) ? $context : $context->get_course_context();

        // Validate that the provided placement type is both configured and enabled for the given tool.
        if (!placement_repository::is_placement_enabled_for_tool_in_course($placementtype, $toolid, $coursecontext->instanceid)) {
            throw new \coding_exception("The specified placement type cannot be used at this time with this tool.");
        }

        return new self($placementtype, $component, $context, $toolid);
    }

    /**
     * Creates a resource link.
     *
     * @param int $itemid The resource link ID.
     * @param int $contextid The context ID.
     * @param int $url The resource link URL .
     * @param string $title The title of the resource link.
     * @param string|null $text The description of the resouce being linked to.
     * @param string $textformat The format of the text field (e.g. FORMAT_MOODLE, FORMAT_HTML, ...).
     * @param bool $gradable Whether the link supports score posting from the tool.
     * @param string|null $servicesalt The LTI 1.0/1.1 hash salt used in generation of lis_result_sourcedid.
     * @param string|null $launchcontainer Constant that specifies the link should be displayed
     *                                     (e.g. LTI_LAUNCH_CONTAINER_DEFAULT, LTI_LAUNCH_CONTAINER_WINDOW, etc).
     * @param string|null $icon The icon associated to the resource link.
     * @param string|null $customparams The additional custom parameters that should be included in launches.
     * @return resource_link The created resource link persistent object.
     */
    public function create_resource_link(int $itemid, string $url, string $title, ?string $text = null,
            string $textformat = FORMAT_MOODLE, bool $gradable = false, ?string $servicesalt = null,
            ?string $launchcontainer = constants::LTI_LAUNCH_CONTAINER_DEFAULT, ?string $icon = null,
            ?string $customparams = null): resource_link {

        $resourcelink = new resource_link(0, (object) [
            'typeid' => $this->toolid,
            'component' => $this->component,
            'itemtype' => $this->placementtype,
            'contextid' => $this->context->id,
            'itemid' => $itemid,
            'url' => $url,
            'title' => $title,
            ...(!empty($text) ? ['text' => $text] : []),
            'textformat' => $textformat,
            'gradable' => $gradable,
            ...(!empty($servicesalt) ? ['servicesalt' => $servicesalt] : []),
            ...(isset($launchcontainer) ? ['launchcontainer' => $launchcontainer] : []),
            ...(!empty($icon) ? ['icon' => $icon] : []),
            ...(!empty($customparams) ? ['customparams' => $customparams] : []),
        ]);

        return $resourcelink->create();
    }

    /**
     * Returns a resource link.
     *
     * @param int $itemid The item ID of the resource link to return.
     * @return resource_link|null The resource link persistent object, or null if it does not exist.
     * @throws dml_exception
     */
    public function get_resource_link(int $itemid): ?resource_link {
        $resourcelink = (new resource_link())->get_record([
            'itemid' => $itemid,
            'component' => $this->component,
            'itemtype' => $this->placementtype
        ]);

        return $resourcelink ?: null;
    }

    /**
     * Updates the properties of a resource link.
     *
     * This method updates the allowed properties of a resource link based on the provided `updatedata` array.
     * Properties such as 'typeid', 'component', 'itemtype', and 'itemid' are disallowed from being updated by the caller
     * as there are no valid use cases for modifying these properties directly.
     *
     * @param int $itemid The item ID of the resource link to update.
     * @param array $updatedata An associative array of data to update.
     * @return bool|null True on success, or false if no updates were made.
     * @throws dml_exception If no record or multiple records found.
     */
    public function update_resource_link(int $itemid, array $updatedata): bool {
        // Define allowed resource link properties that can be updated.
        $allowedproperties = [
            'url', 'title', 'text', 'textformat', 'gradable', 'launchcontainer', 'servicesalt', 'icon', 'customparams'
        ];

        // Remove any properties from the update data that are not allowed to be updated.
        $updatedata = array_filter($updatedata, function($key) use ($allowedproperties) {
            return in_array($key, $allowedproperties);
        }, ARRAY_FILTER_USE_KEY);

        // Early return if there is no data to update.
        if (empty($updatedata)) {
            return false;
        }

        $resourcelink = $this->get_resource_link($itemid);

        // Early return if the resource link cannot be found.
        if (empty($updatedata) || is_null($resourcelink)) {
            return false;
        }

        // Loop through update data and set values.
        foreach ($updatedata as $property => $value) {
            $resourcelink->set($property, $value);
        }

        return $resourcelink->update();
    }

    /**
     * Deletes a resource link.
     *
     * @param int $itemid The item ID of the resource link to be deleted.
     * @return bool True on success, or false if no deletion was performed.
     * @throws dml_exception
     */
    public function delete_resource_link($itemid): bool {
        $resourcelink = $this->get_resource_link($itemid);

        // Early return if the resource link cannot be found.
        if (is_null($resourcelink)) {
            return false;
        }

        return $resourcelink->delete();
    }
}
