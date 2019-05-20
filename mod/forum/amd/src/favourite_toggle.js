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
 * Handle discussion subscription toggling on a discussion list in
 * the forum view.
 *
 * @module     mod_forum/favourite_toggle
 * @package    mod_forum
 * @copyright  2019 Peter Dias <peter@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
        'jquery',
        'core/templates',
        'core/notification',
        'mod_forum/repository',
        'mod_forum/selectors',
        'core/str',
    ], function(
        $,
        Templates,
        Notification,
        Repository,
        Selectors,
        String
    ) {

    /**
     * Register event listeners for the subscription toggle.
     *
     * @param {object} root The discussion list root element
     */
    var registerEventListeners = function(root) {
        root.on('click', Selectors.favourite.toggle, function(e) {
            var toggleElement = $(this);
            var forumId = toggleElement.data('forumid');
            var discussionId = toggleElement.data('discussionid');
            var subscriptionState = toggleElement.data('targetstate');

            Repository.setFavouriteDiscussionState(forumId, discussionId, subscriptionState)
                .then(function(context) {
                    return Templates.render('mod_forum/discussion_favourite_toggle', context);
                })
                .then(function(html, js) {
                    return Templates.replaceNode(toggleElement, html, js);
                })
                .then(function() {
                    return String.get_string("favouriteupdated", "forum")
                        .done(function(s) {
                            // Add a new or update an existing "info" flash notification message.
                            var infoFlashNotification = null;
                            var flashNotificationAttr = {
                                flash: true
                            };
                            // Get all present flash notifications in the notification area.
                            var allFlashNotifications = Notification.getNotificationElements(flashNotificationAttr);
                            // We only need to update and keep one flash notification. We should remove any additional ones.
                            $.each(allFlashNotifications, function(index, flashNotificationElement) {
                                if ($(flashNotificationElement).data("type") == "info") {
                                    infoFlashNotification = flashNotificationElement;
                                    allFlashNotifications.splice(index, 1);
                                    return false;
                                }
                            });

                            // Remove all other flash notifications.
                            Notification.clearNotificationElements(allFlashNotifications);

                            if (infoFlashNotification !== null) { // "Info" flash notification is present in the notification area.
                                Notification.updateNotification(infoFlashNotification, s);
                            } else { // "Info" flash notification is not present in the notification area.
                                // Add new "info" flash notification.
                                Notification.addNotification({
                                    message: s,
                                    type: "info",
                                    flash: true
                                });
                            }
                        });
                })
                .catch(Notification.exception);

            e.preventDefault();
        });
    };

    return {
        init: function(root) {
            registerEventListeners(root);
        }
    };
});
