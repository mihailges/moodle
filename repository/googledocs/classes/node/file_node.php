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
 * Class used to represent a file node in the googledocs repository.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_googledocs\node;

/**
 * Represents a file node in the googledocs repository.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_node implements node {

    /** @var string The ID of the file node. */
    private $id;

    /** @var string The title of the file node. */
    private $title;

    /** @var string The source for the file node. */
    private $source;

    /** @var string The timestamp representing the last modified date. */
    private $modified;

    /** @var string|null The size of the file. */
    private $size;

    /** @var bool The thumbnail of the file. */
    private $thumbnail;

    /**
     * Constructor.
     *
     * @param \stdClass $content The file object
     */
    public function __construct(\stdClass $content) {
        $this->id = $content->id;
        $this->title = $this->generate_file_title($content);
        $this->source = json_encode(
            [
                'id' => $content->id,
                'name' => $content->name,
                'link' => $this->generate_file_link($content),
                'exportformat' => $this->generate_file_export_format($content)
            ]
        );
        $this->modified = ($content->modifiedTime) ? strtotime($content->modifiedTime) : '';
        $this->size = !empty($content->size) ? $content->size : null;
        // Use iconLink as a file thumbnail if set, otherwise use the default icon depending on the file type.
        // Note: The Google Drive API can return a link to a preview thumbnail of the file (via thumbnailLink).
        // However, in many cases the Google Drive files are not public and an authorized request is required
        // to get the thumbnail which we currently do not support. Therefore, to avoid displaying broken
        // thumbnail images the repository, the icon of the Google Drive file is being used as a thumbnail
        // instead as it does not require an authorized request.
        $this->thumbnail = !empty($content->iconLink) ? $content->iconLink : '';
    }

    /**
     * Create the file node.
     *
     * @return array|null The node array or null if the node could not be created
     */
    public function create_node(): ?array {
        // Cannot create the file node if the file title was not generated. This means that the current file type
        // is invalid or unknown.
        if (!$this->title) {
            return null;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'source' => $this->source,
            'date' => $this->modified,
            'size' => $this->size,
            'thumbnail' => $this->thumbnail,
            'thumbnail_height' => 64,
            'thumbnail_width' => 64,
        ];
    }

    /**
     * Generates and returns the title for the file node depending on the type of the Google drive file.
     *
     * @param \stdClass $content The file object
     * @return string The file title
     */
    private function generate_file_title(\stdClass $content): ?string {
        // Determine the file type through the file extension.
        if (isset($content->fileExtension)) { // The file is a regular file.
            return $content->name;
        } else { // The file is probably a Google Doc file.
            // We need to generate the name by appending the proper google doc extension.
            $type = str_replace('application/vnd.google-apps.', '', $content->mimeType);

            if ($type === 'document') {
                return "{$content->name}.gdoc";
            }
            if ($type === 'presentation') {
                return "{$content->name}.gslides";
            }
            if ($type === 'spreadsheet') {
                return "{$content->name}.gsheet";
            }
            if ($type === 'drawing') {
                $config = get_config('googledocs');
                $ext = $config->drawingformat;
                return "{$content->name}.{$ext}";
            }
        }

        return null;
    }

    /**
     * Generates and returns the file export format depending on the type of the Google drive file.
     *
     * @param \stdClass $content The file object
     * @return string The file export format
     */
    private function generate_file_export_format(\stdClass $content): ?string {
        // Determine the file type through the file extension.
        if (isset($content->fileExtension)) { // The file is a regular file.
            // The file has an extension, therefore we can download it.
            return 'download';
        } else {
            // The file is probably a Google Doc file, we get the corresponding export link.
            $type = str_replace('application/vnd.google-apps.', '', $content->mimeType);
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

    /**
     * Generates and returns the external link to the file.
     *
     * @param \stdClass $content The file object
     * @return string The link to the file
     */
    private function generate_file_link(\stdClass $content): string {
        // If the google drive file has webViewLink set, use it as an external link.
        $link = !empty($content->webViewLink) ? $content->webViewLink : '';
        // Otherwise, use webContentLink if set or leave the external link empty.
        if (empty($link) && !empty($content->webContentLink)) {
            $link = $content->webContentLink;
        }

        return $link;
    }
}
