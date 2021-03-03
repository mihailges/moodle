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
 * Utility class for presenting the googledocs repository contents.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_googledocs;

use repository_googledocs\node\file_node;
use repository_googledocs\node\folder_node;

/**
 * Base class for presenting the googledocs repository contents.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class googledocs_content {

    /** @var rest The rest API object. */
    protected $service;

    /** @var string The current path. */
    protected $path;

    /** @var bool Whether sorting should be applied to the fetched content. */
    protected $sortcontent;

    /**
     * Constructor.
     *
     * @param rest $service The rest API object
     * @param string $path The current path
     * @param bool $sortcontent Whether sorting should be applied to the content
     */
    public function __construct(rest $service, string $path, bool $sortcontent = true) {
        $this->service = $service;
        $this->path = $path;
        $this->sortcontent = $sortcontent;
    }

    /**
     * Generate and return all nodes (files and folders) for the existing content based on the path or search query.
     *
     * @param string $query The search query
     * @param callable $isvalid The callback function which determines whether a given file should be displayed based
     *                          on the existing file restrictions
     * @return array[] The array containing the content nodes
     */
    public function get_content_nodes(string $query, callable $isvalid): array {
        $folders = [];
        $files = [];
        $contents = $this->get_contents($query);

        foreach ($contents as $item) {
            if ($item->mimeType === 'application/vnd.google-apps.folder') { // It is a folder.
                $modified = ($item->modifiedTime) ? strtotime($item->modifiedTime) : '';
                // Create a folder node for the given content item.
                $folder = new folder_node($item->id, $item->name, $this->path, $modified);
                $folders["{$item->name}{$item->id}"] = $folder->create_node();

            } else { // It is a file.
                // Create a file node for the given content item.
                $extension = !empty($item->fileExtension) ? $item->fileExtension : null;
                $mimetype = !empty($item->mimeType) ? $item->mimeType : null;

                // Skip this file if the file node title was not generated. This means that the file type
                // is invalid or unknown.
                if (!$title = $this->generate_file_title($item->name, $extension, $mimetype)) {
                    continue;
                }

                // If the google drive file has webViewLink set, use it as an external link.
                $link = !empty($item->webViewLink) ? $item->webViewLink : '';
                // Otherwise, use webContentLink if set or leave the external link empty.
                if (empty($link) && !empty($item->webContentLink)) {
                    $link = $item->webContentLink;
                }

                $source = json_encode(
                    [
                        'id' => $item->id,
                        'name' => $item->name,
                        'link' => $link,
                        'exportformat' => $this->generate_file_export_format($extension, $mimetype)
                    ]
                );

                $modified = ($item->modifiedTime) ? strtotime($item->modifiedTime) : '';
                $size = !empty($item->size) ? $item->size : '';
                // Use iconLink as a file thumbnail if set, otherwise use the default icon depending on the file type.
                // Note: The Google Drive API can return a link to a preview thumbnail of the file (via thumbnailLink).
                // However, in many cases the Google Drive files are not public and an authorized request is required
                // to get the thumbnail which we currently do not support. Therefore, to avoid displaying broken
                // thumbnail images the repository, the icon of the Google Drive file is being used as a thumbnail
                // instead as it does not require an authorized request.
                $thumbnail = !empty($item->iconLink) ? $item->iconLink : '';

                $file = new file_node($title, $source, $modified, $size, $thumbnail);
                $filenode = $file->create_node();
                // Validate the file through the filter callback function.
                if ($isvalid($filenode)) {
                    // Adds the file to the file list. Using the item id along with the name as key
                    // of the array because Google Drive allows files with identical names.
                    $files["{$item->name}{$item->id}"] = $filenode;
                }
            }
        }
        // Sort the contents if required.
        if ($this->sortcontent) {
            // Order the results alphabetically by their array keys.
            \core_collator::ksort($files, \core_collator::SORT_STRING);
            \core_collator::ksort($folders, \core_collator::SORT_STRING);
        }

        return array_merge(array_values($folders), array_values($files));
    }

    /**
     * Build the navigation (breadcrumb) from a given path.
     *
     * @return array Array containing name and path of each navigation node
     */
    public function get_navigation(): array {
        $nav = [];
        $navtrail = '';
        $pathnodes = explode('/', $this->path);

        foreach ($pathnodes as $node) {
            list($id, $name) = helper::explode_node_path($node);
            $name = empty($name) ? $id : $name;
            $nav[] = array(
                'name' => $name,
                'path' => helper::build_node_path($id, $name, $navtrail)
            );
            $tmp = end($nav);
            $navtrail = $tmp['path'];
        }

        return $nav;
    }

    /**
     * Returns all relevant contents (files and folders) based on the given path or search query.
     *
     * @param string $query The search query
     * @return array The array containing the contents
     */
    abstract protected function get_contents(string $query): array;

    /**
     * Generates and returns the title for the file node depending on the type of the Google drive file.
     *
     * @param string $filename The name of the Google drive file
     * @param string|null $filextension The extension of the Google drive file (if exists)
     * @param string|null $filextension The mimetype of the Google drive file (if exists)
     * @return string The file title
     */
    private function generate_file_title(string $filename, ?string $filextension, ?string $filemimetype): ?string {
        // Determine the file type through the file extension.
        if ($filextension) { // The file is a regular file.
            return $filename;
        } else { // The file is probably a Google Doc file.
            // We need to generate the name by appending the proper google doc extension.
            $type = str_replace('application/vnd.google-apps.', '', $filemimetype);

            if ($type === 'document') {
                return "{$filename}.gdoc";
            }
            if ($type === 'presentation') {
                return "{$filename}.gslides";
            }
            if ($type === 'spreadsheet') {
                return "{$filename}.gsheet";
            }
            if ($type === 'drawing') {
                $config = get_config('googledocs');
                $ext = $config->drawingformat;
                return "{$filename}.{$ext}";
            }
        }

        return null;
    }

    /**
     * Generates and returns the file export format depending on the type of the Google drive file.
     *
     * @param string|null $filextension The extension of the Google drive file (if exists)
     * @param string|null $filextension The mimetype of the Google drive file (if exists)
     * @return string The file export format
     */
    private function generate_file_export_format(?string $filextension, ?string $filemimetype): ?string {
        // Determine the file type through the file extension.
        if ($filextension) { // The file is a regular file.
            // The file has an extension, therefore we can download it.
            return 'download';
        } else {
            // The file is probably a Google Doc file, we get the corresponding export link.
            $type = str_replace('application/vnd.google-apps.', '', $filemimetype);
            $types = get_mimetypes_array();
            $config = get_config('googledocs');

            if ($type === 'document') {
                $ext = $config->documentformat;
                if ($ext === 'rtf') {
                    // Moodle user 'text/rtf' as the MIME type for RTF files.
                    // Google uses 'application/rtf' for the same type of file.
                    // See https://developers.google.com/drive/v3/web/manage-downloads.
                    return 'application/rtf';
                } else {
                    return $types[$ext]['type'];
                }
            }
            if ($type === 'presentation') {
                $ext = $config->presentationformat;
                return $types[$ext]['type'];
            }
            if ($type === 'spreadsheet') {
                $ext = $config->spreadsheetformat;
                return $types[$ext]['type'];
            }
            if ($type === 'drawing') {
                $ext = $config->drawingformat;
                return $types[$ext]['type'];
            }
        }

        return null;
    }
}
