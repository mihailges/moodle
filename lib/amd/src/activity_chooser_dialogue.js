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
 * A system for displaying notifications to users from the session.
 *
 * Wrapper for the YUI M.core.notification class. Allows us to
 * use the YUI version in AMD code until it is replaced.
 *
 * @module     core/activity_chooser_dialogue
 * @package    core
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.7
 */
define(
    [
        'jquery',
        'core/activity_chooser_events',
        'core/local/aria/focuslock',
        'core/modal_factory',
        'core/modal_events'
    ],
    function(
        $,
        ActivityChooserEvents,
        FocusLock,
        ModalFactory,
        ModalEvents
    ) {

     // The panel widget
    var panel = null;

    // The submit button - we disable this until an element is set
    var submitbutton = null;

    // The chooserdialogue container
    var container = null;

    var options = null;

    // Any event listeners we may need to cancel later
    var listenevents = [];

    var bodycontent = null;
    var headercontent = null;
    var instanceconfig = null;

    // The hidden field storing the disabled element values for submission.
    var hiddenRadioValue = null;

    var ATTRS = {
        /**
         * The minimum height (in pixels) before resizing is prevented and scroll
         * locking disabled.
         *
         * @attribute minheight
         * @type Number
         * @default 300
         */
        minheight: 300,

        /**
         * The base height??
         *
         * @attribute baseheight
         * @type Number
         * @default 400
         */
        baseheight: 400,

        /**
         * The maximum height (in pixels) at which we stop resizing.
         *
         * @attribute maxheight
         * @type Number
         * @default 300
         */
        maxheight: 660,

        /**
         * The title of the close button.
         *
         * @attribute closeButtonTitle
         * @type String
         * @default 'Close'
         */
        closeButtonTitle: {
            validator: Y.Lang.isString,
            value: 'Close'
        }
    };

    var setupChooserDialogue = function(body, title) {
        bodycontent = body;
        headercontent = title;
    };

    /**
     * Cancel any listen events in the listenevents queue
     *
     * Several locations add event handlers which should only be called before the form is submitted. This provides
     * a way of cancelling those events.
     *
     * @method cancel_listenevents
     */
    var cancelListenevents = function() {
        // Detach all listen events to prevent duplicate triggers
        var thisevent;
        while (listenevents.length) {
            thisevent = listenevents.shift();
            $(thisevent).off();
        }
    };

    var hide = function() {
        // Cancel all listen events
        cancelListenevents();
        container.off();
        panel.hide();
    };

    var cancelPopup = function(e) {
        // Prevent normal form submission before hiding
        e.preventDefault();
        hide();
    };

    //   /**
    //  * Return an array of class names prefixed with 'modchooserdialogue-' and
    //  * the name of the type of dialogue.
    //  *
    //  * Note: Class name are converted to lower-case.
    //  *
    //  * If an array of arguments is supplied, each of these is prefixed and
    //  * lower-cased also.
    //  *
    //  * If no arguments are supplied, then the prefix is returned on it's
    //  * own.
    //  *
    //  * @method _getClassNames
    //  * @param {Array} [args] Any additional names to prefix and lower-case.
    //  * @return {Array}
    //  * @private
    //  */
    // var _getClassNames = function(args) {
    //     var prefix = 'modchooserdialogue-course-modchooser',
    //         results = [];
    //
    //     results.push(prefix.toLowerCase());
    //     if (args) {
    //         var arg;
    //         for (arg in args) {
    //             results.push((prefix + '-' + arg).toLowerCase());
    //         }
    //     }
    //
    //     return results;
    // };

    var SELECTORS = {
        CHOOSER_CONTAINER: '[data-region="chooser-container"]',
        CHOOSER_OPTIONS_CONTAINER: '[data-region="chooser-options-container"]',
        CHOOSER_OPTION_CONTAINER: '[data-region="chooser-option-container"]',
        CHOOSER_OPTION_ACTIONS_CONTAINER: '[data-region="chooser-option-actions-container"]',
        CHOOSER_OPTION_INFO_CONTAINER: '[data-region="chooser-option-info-container"]',
        CHOOSER_OPTION_SUMMARY_CONTAINER: '[data-region="chooser-option-summary-container"]',
        CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER: '[data-region="chooser-option-summary-content-container"]',
        CHOOSER_OPTION_SUMMARY_ACTIONS_CONTAINER: '[data-region="chooser-option-summary-actions-container"]',
        CHOOSER_OPTION_ACTION_GROUP_ELEMENT: '[data-group="chooser-option-action"]',
        CHOOSER_OPTION_ACTIONS: {
            SHOW_CHOOSER_OPTION_SUMMARY: '[data-action="show-option-summary"]',
        },
        ADD_CHOOSER_OPTION: '[data-action="add-chooser-option"]',
        CLOSE_CHOOSER_OPTION_SUMMARY: '[data-action="close-chooser-option-summary"]',
    };

    var registerListenerEvents = function() {
        // Show the chooser option summary.
        var thisevent = $(SELECTORS.CHOOSER_OPTION_ACTIONS.SHOW_CHOOSER_OPTION_SUMMARY).on('click', function(e) {
            var optionSummaryElement = $(e.target).closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
                .find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
            showOptionSummary(optionSummaryElement);
        });
        listenevents.push(thisevent);

        // Close the chooser option summary.
        thisevent = $(SELECTORS.CLOSE_CHOOSER_OPTION_SUMMARY).on('click', function(e) {
            var optionSummaryElement = $(e.target).closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
                .find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
            optionSummaryElement.removeClass('open');
            $(SELECTORS.CHOOSER_CONTAINER).removeClass('noscroll');
        });
        listenevents.push(thisevent);

        thisevent = $(SELECTORS.CHOOSER_OPTION_CONTAINER).on('keydown', function(e) {
            var index = $(this).index();

            var chooserOptions = $(SELECTORS.CHOOSER_OPTIONS_CONTAINER).find(SELECTORS.CHOOSER_OPTION_CONTAINER);
            var totalChooserOptions = chooserOptions.length;

            // Right key or down key.
            if (e.keyCode === 39 || e.keyCode === 40) {
                var nextIndex = index + 1;
                if (typeof chooserOptions[nextIndex] === 'undefined') {
                    return;
                }
                moveFocus(chooserOptions[index], chooserOptions[nextIndex]);
            }

            // Left key.
            if (e.keyCode === 37 || e.keyCode === 38) {
                var prevIndex = index - 1;
                if (typeof chooserOptions[prevIndex] === 'undefined') {
                    return;
                }
                moveFocus(chooserOptions[index], chooserOptions[prevIndex]);
            }

            // End key.
            if (e.keyCode === 35) {
                var lastChooserOptionIndex = totalChooserOptions - 1;
                if (index === lastChooserOptionIndex) {
                    return;
                }
                moveFocus(chooserOptions[index], chooserOptions[lastChooserOptionIndex]);
            }

            // Home key.
            if (e.keyCode === 36) {
                var firstChooserOptionIndex = 0;
                if (index === firstChooserOptionIndex) {
                    return;
                }
                moveFocus(chooserOptions[index], chooserOptions[firstChooserOptionIndex]);
            }

            // Enter, space key.
            // if (e.keyCode === 13 || e.keyCode == 32) {
            //
            //     Object.values(SELECTORS.CHOOSER_OPTION_ACTIONS).forEach(chooserOptionActionElement => {
            //         $(chooserOptionActionElement).attr('tabindex', 0);
            //     });
            //     var addChooserOptionActionElement = $(this).find(SELECTORS.ADD_CHOOSER_OPTION);
            //     $(addChooserOptionActionElement).focus();
            //     $(this).attr('tabindex', -1);
            // }
        });
        listenevents.push(thisevent);

        // thisevent = $(SELECTORS.CHOOSER_OPTION_ACTION_GROUP_ELEMENT).on('keydown', function(e) {
        //     e.preventDefault();
        //     e.stopPropagation();
        //
        //     var chooserOptionActions = $(this).closest(SELECTORS.CHOOSER_OPTION_ACTIONS_CONTAINER)
        //         .find(SELECTORS.CHOOSER_OPTION_ACTION_GROUP_ELEMENT);
        //     var index = chooserOptionActions.index(this);
        //
        //     // Esc key.
        //     if (e.keyCode === 27) {
        //         var chooserOption = $(this).closest(SELECTORS.CHOOSER_OPTION_CONTAINER);
        //         $(chooserOption).attr('tabindex', 0);
        //         $(chooserOption).focus();
        //         $(this).attr('tabindex', -1);
        //     }
        //
        //     // Right key.
        //     if (e.keyCode === 39) {
        //         var nextIndex = index + 1;
        //         if (typeof chooserOptionActions[nextIndex] === 'undefined') {
        //             return;
        //         }
        //         moveFocus(chooserOptionActions[index], chooserOptionActions[nextIndex]);
        //     }
        //
        //     // Left key.
        //     if (e.keyCode === 37) {
        //         var prevIndex = index - 1;
        //         if (typeof chooserOptions[prevIndex] === 'undefined') {
        //             return;
        //         }
        //         moveFocus(chooserOptions[index], chooserOptions[prevIndex]);
        //     }
        //
        //     // End key.
        //     if (e.keyCode === 35) {
        //         var lastChooserOptionIndex = totalChooserOptions - 1;
        //         if (index === lastChooserOptionIndex) {
        //             return;
        //         }
        //         moveFocus(chooserOptions[index], chooserOptions[lastChooserOptionIndex]);
        //     }
        //
        //     // Home key.
        //     if (e.keyCode === 36) {
        //         var firstChooserOptionIndex = 0;
        //         if (index === firstChooserOptionIndex) {
        //             return;
        //         }
        //         moveFocus(chooserOptions[index], chooserOptions[firstChooserOptionIndex]);
        //     }
        //
        //     if (e.keyCode === 13) {
        //         e.preventDefault();
        //         e.stopPropagation();
        //         if ($(this).parent().hasClass('showmodsummary')) {
        //             var chooserOptionSummary = $(this).closest(SELECTORS.MODULE_AREA)
        //                 .find(SELECTORS.MODULE_SUMMARY_AREA);
        //             showOptionSummary(chooserOptionSummary);
        //         }
        //         return;
        //     }
        // });
        // listenevents.push(thisevent);
    };

    var prepareChooser = function(sectionid) {
        var body = bodycontent.detach();

        // Stop the default event actions before we proceed.
        return ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            body: body.html(),
            title: headercontent.html(),
            large: true
        }).then(function(modal) {
            modal.getRoot().on(ModalEvents.shown, function() {

                // Find all anchors used to add an option and append the section param to the url.
                var anchors = $(SELECTORS.CHOOSER_OPTION_CONTAINER).find(SELECTORS.ADD_CHOOSER_OPTION);
                anchors.each(function(index, anchor) {
                    anchor.href += '&section=' + sectionid;
                });

                // var chooserOptions = $(SELECTORS.CHOOSER_OPTIONS_CONTAINER).find(SELECTORS.CHOOSER_OPTION_CONTAINER);
                // var firstChooserOption = chooserOptions[0];
                // $(firstChooserOption).attr("tabindex", 0);

                var moreInfoLinks = $(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER).find('.helplinkpopup');
                moreInfoLinks.each(function(key, moreInfoLink) {
                    $(moreInfoLink).attr('tabindex', -1);
                });

                registerListenerEvents();
            }.bind(this));

            // We want to focus on the action select when the dialog is closed.
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.getRoot().remove();
            }.bind(this));


            modal.show();

            return modal;
        });
    };

    /**
      * Calculate the optimum height of the chooser dialogue
      *
      * This tries to set a sensible maximum and minimum to ensure that some options are always shown, and preferably
      * all, whilst fitting the box within the current viewport.
      *
      * @method center_dialogue
      * @param Node {dialogue} Y.Node The dialogue
      */
    var centerDialogue = function(dialogue) {
        var bb = panel.get('boundingBox'),
            winheight = bb.get('winHeight'),
            newheight, totalheight;

        if (panel.shouldResizeFullscreen()) {
            // No custom sizing required for a fullscreen dialog.
            return;
        }

        // Try and set a sensible max-height -- this must be done before setting the top
        // Set a default height of 640px
        newheight = ATTRS.maxheight;
        if (winheight <= newheight) {
            // Deal with smaller window sizes
            if (winheight <= ATTRS.minheight) {
                newheight = ATTRS.minheight;
            } else {
                newheight = winheight;
            }
        }

        // If the dialogue is larger than a reasonable minimum height, we
        // disable the page scrollbars.
        if (newheight > ATTRS.minheight) {
            // Disable the page scrollbars.
            if (panel.lockScroll && !panel.lockScroll.isActive()) {
                panel.lockScroll.enableScrollLock(true);
            }
        } else {
            // Re-enable the page scrollbars.
            if (panel.lockScroll && panel.lockScroll.isActive()) {
                panel.lockScroll.disableScrollLock();
            }
        }

        // Take off 15px top and bottom for borders, plus 40px each for the title and button area before setting the
        // new max-height.
        totalheight = newheight;
        newheight = newheight - (15 + 15 + 40 + 40);
        $(dialogue).css('maxHeight', newheight + 'px');

        var dialogueheight = bb.getStyle('height');
        if (dialogueheight.match(/.*px$/)) {
            dialogueheight = dialogueheight.replace(/px$/, '');
        } else {
            dialogueheight = totalheight;
        }

        if (dialogueheight < ATTRS.baseheight) {
            dialogueheight = ATTRS.baseheight;
            $(dialogue).css('height', dialogueheight + 'px');
        }

        panel.centerDialogue();
    };


    var showOptionSummary = function(optionSummaryElement) {
        // Get the current scroll position of the chooser container element.
        var topPosition = $(SELECTORS.CHOOSER_CONTAINER).scrollTop();
        // Get the height of the chooser container element.
        var height = $(SELECTORS.CHOOSER_CONTAINER).outerHeight();
        // Disable the scroll of the chooser container element.
        $(SELECTORS.CHOOSER_CONTAINER).addClass('noscroll');

        setOptionSummaryPositionAndHeight(optionSummaryElement, topPosition, height);

        var optionSummaryContentElement = optionSummaryElement.find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER);
        // Set the scroll of the type summary content element to top.
        if (optionSummaryContentElement.scrollTop() > 0) {
            optionSummaryContentElement.scrollTop(0);
        }
        // Show the particular summary overlay.
        optionSummaryElement.addClass('open');
        var cancelAction = optionSummaryElement.find(SELECTORS.CLOSE_CHOOSER_OPTION_SUMMARY);
        var addAction = optionSummaryElement.find(SELECTORS.ADD_MODULE);

        $(cancelAction).attr('tabindex', 0);
        $(addAction).attr('tabindex', 0);
    };

    var setOptionSummaryPositionAndHeight = function(optionSummaryElement, position, height) {
        var optionSummaryContentElement = optionSummaryElement.find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER);
        var optionSumarryActionsElement = optionSummaryElement.find(SELECTORS.CHOOSER_OPTION_SUMMARY_ACTIONS_CONTAINER);
        var contentHeight = height - optionSumarryActionsElement.outerHeight();
        optionSummaryContentElement.css({'height' : contentHeight + 'px'});

        optionSummaryElement.css({'top' : position + 'px', 'height' : height + 'px'});
    };

    var moveFocus = function(fromElement, toElement) {
        $(toElement).attr('tabindex', 0);
        $(toElement).focus();
        $(fromElement).attr('tabindex', -1);
    };

    /**
      * Display the module chooser.
      *
      * @method display_chooser
      * @param {EventFacade} e Triggering Event
      */
    var displayChooser = function(e, sectionid) {
        prepareChooser(sectionid);
    };

    return /** @alias module:core/activity_chooser_dialogue */{

        /**
         * Poll the server for any new notifications.
         *
         * @method fetchNotifications
         */
        setupChooserDialogue: setupChooserDialogue,

        /**
         * Add a notification to the page.
         *
         * Note: This does not cause the notification to be added to the session.
         *
         * @method addNotification
         * @param {Object}  notification                The notification to add.
         * @param {string}  notification.message        The body of the notification
         * @param {string}  notification.type           The type of notification to add (error, warning, info, success).
         * @param {Boolean} notification.closebutton    Whether to show the close button.
         * @param {Boolean} notification.announce       Whether to announce to screen readers.
         */
        displayChooser: displayChooser
    };
});
