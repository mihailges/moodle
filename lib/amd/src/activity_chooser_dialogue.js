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
        'core/yui',
        'jquery',
        'core/pubsub',
        'core/activity_chooser_events',
        'core/local/aria/focuslock',
        'core/modal_factory',
        'core/modal_events'
    ],
    function(
        Y,
        $,
        PubSub,
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
        MODULE_CHOOSER_CONTAINER: '.modchoosercontainer',
        MODULES_AREA: '.modulescontainer',
        MODULE_AREA: '.module',
        MODULE_INFO_AREA: '.modinfo',
        MODULE_INFO_ACTIONS: {
            ADD_MODULE: '.modinfo .modicon a',
            SHOW_MODULE_SUMMARY: '.modinfo .modactions .showmodsummary i'
        },
        MODULE_SUMMARY_AREA: '.modsummary',
        MODULE_SUMMARY_ACTIONS: {
            ADD_MODULE: '.modsummary .actions .addmodule',
            CLOSE_MODULE_SUMMARY: '.modsummary .actions .closemodsummary'
        }
    };

    var registerListenerEvents = function() {
        // Show the module summary.
        var thisevent = $(SELECTORS.MODULE_INFO_ACTIONS.SHOW_MODULE_SUMMARY).on('click', function(e) {
            var modSummaryElement = $(e.target).closest(SELECTORS.MODULE_AREA).find(SELECTORS.MODULE_SUMMARY_AREA);
            openModSummary(modSummaryElement);
        });
        listenevents.push(thisevent);

        // Close the module summary.
        thisevent = $(SELECTORS.MODULE_SUMMARY_ACTIONS.CLOSE_MODULE_SUMMARY).on('click', function(e) {
            var modSummaryElement = $(e.target).closest(SELECTORS.MODULE_AREA).find(SELECTORS.MODULE_SUMMARY_AREA);
            modSummaryElement.removeClass('open');
            $(SELECTORS.MODULE_CHOOSER_CONTAINER).removeClass('noscroll');
        });
        listenevents.push(thisevent);

        thisevent = $(SELECTORS.MODULE_AREA).on('keydown', function(e) {
            var index = $(this).index();
            var moduleItemOuterWidth = $(SELECTORS.MODULE_AREA).outerWidth();
            var moduleItemsWrapperWidth = $(SELECTORS.MODULES_AREA).width();
            var moduleItemsPerRow = Math.floor(moduleItemsWrapperWidth / moduleItemOuterWidth);
            var moduleItems = $(SELECTORS.MODULES_AREA).find(SELECTORS.MODULE_AREA);
            var totalModuleItems = moduleItems.length;
            var totalRowsCount = Math.ceil(totalModuleItems / moduleItemsPerRow);
            var indexRow = Math.ceil((index + 1) / moduleItemsPerRow);

            // Right key.
            if (e.keyCode === 39) {
                var nextIndex = index + 1;
                if (typeof moduleItems[nextIndex] === 'undefined') {
                    return;
                }
                moveFocus(moduleItems[index], moduleItems[nextIndex]);
            }

            // Left key.
            if (e.keyCode === 37) {
                var prevIndex = index - 1;
                if (typeof moduleItems[prevIndex] === 'undefined') {
                    return;
                }
                moveFocus(moduleItems[index], moduleItems[prevIndex]);
            }

            // Down key.
            if (e.keyCode === 40) {
                // If the focus is on an element from the last visual row.
                if (indexRow == totalRowsCount) {
                    return;
                }
                var nextIndex = index + moduleItemsPerRow;
                if (typeof moduleItems[nextIndex] === 'undefined') {
                    nextIndex = totalModuleItems - 1;
                }
                moveFocus(moduleItems[index], moduleItems[nextIndex]);
            }

            // Up key.
            if (e.keyCode === 38) {
                // if the focus is on an element from the first visual row.
                if (indexRow === 1) {
                    return;
                }
                var prevIndex = index - moduleItemsPerRow;
                if (typeof moduleItems[prevIndex] === 'undefined') {
                    prevIndex = 0;
                }
                moveFocus(moduleItems[index], moduleItems[prevIndex]);
            }

            // End key.
            if (e.keyCode === 35) {
                var lastModuleItemIndex = totalModuleItems - 1;
                if (index === lastModuleItemIndex) {
                    return;
                }
                moveFocus(moduleItems[index], moduleItems[lastModuleItemIndex]);
            }

            // Home key.
            if (e.keyCode === 36) {
                var firstModuleItemIndex = 0;
                if (index === firstModuleItemIndex) {
                    return;
                }
                moveFocus(moduleItems[index], moduleItems[firstModuleItemIndex]);
            }

            // Enter, space key.
            if (e.keyCode === 13 || e.keyCode == 32) {
                var addModuleInfoElement = $(this).find(SELECTORS.MODULE_INFO_ACTIONS.ADD_MODULE);
                $(addModuleInfoElement).attr('tabindex', 0);
                $(addModuleInfoElement).focus();
                $(this).attr('tabindex', -1);
            }
        });
        listenevents.push(thisevent);



        thisevent = $(".actionelement").on('keydown', function(e) {
             e.preventDefault();
             e.stopPropagation();

            var actionElements = $(this).closest("label").find(".actionelement");
            var index = $(actionElements).index(this);

            // down key.
            if (e.keyCode === 40 || e.keyCode === 39) {
                var nextIndex = index + 1;
                if (typeof actionElements[nextIndex] === 'undefined') {
                    return;
                }
                moveFocus(actionElements[index], actionElements[nextIndex]);
            }

            // up key.
            if (e.keyCode === 38 || e.keyCode === 37) {
                var prevIndex = index - 1;
                if (typeof actionElements[prevIndex] === 'undefined') {
                    return;
                }
                moveFocus(actionElements[index], actionElements[prevIndex]);
            }

            if (e.keyCode === 27) {
                var optionElement = $(this).closest('.option');
                $(optionElement).attr('tabindex', 0);
                $(optionElement).focus();
                $(this).attr('tabindex', -1);
            }

            if (e.keyCode === 13) {
                if ($(this).hasClass('info')) {
                    var typeSummaryEl = $(this).closest('label').find('.modsummary');
                    openModSummary(typeSummaryEl);
                }
                return;
            }
        });
        listenevents.push(thisevent);
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
                // Find all anchors used to add an activity and append the section param to the url.
                var anchors = $(SELECTORS.MODULE_AREA)
                    .find(SELECTORS.MODULE_INFO_ACTIONS.ADD_MODULE + ', ' + SELECTORS.MODULE_SUMMARY_ACTIONS.ADD_MODULE);
                anchors.each(function(index, anchor) {
                    anchor.href += '&section=' + sectionid;
                });

                var modules = $(SELECTORS.MODULES_AREA).find(SELECTORS.MODULE_AREA);
                var firstModule = modules[0];
                $(firstModule).attr("tabindex", 0);

                var moreInfoLinks = $(SELECTORS.MODULE_SUMMARY_AREA).find('.helplinkpopup');
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

    // var handleKeyPress = function(e) {
    //     if (e.keyCode === 27) {
    //         cancelPopup(e);
    //     }
    //
    //     if (e.keyCode === 39) {
    //         var focusedElement = $(":focus");
    //     }
    // };

    var openModSummary = function(modSummaryElement) {
        // Get the current scroll position of the .alloption element.
        var topPosition = $(SELECTORS.MODULE_CHOOSER_CONTAINER).scrollTop();
        // Get the height of the .alloption element.
        var height = $(SELECTORS.MODULE_CHOOSER_CONTAINER).outerHeight();
        // Disable the scroll of .alloptions.
        $(SELECTORS.MODULE_CHOOSER_CONTAINER).addClass('noscroll');

        setTypeSummaryPositionAndHeight(modSummaryElement, topPosition, height);

        var typeSummaryContentElement = modSummaryElement.find('.content');
        // Set the scroll of the type summary content element to top.
        if (typeSummaryContentElement.scrollTop() > 0) {
            typeSummaryContentElement.scrollTop(0);
        }
        // Show the particular summary overlay.
        modSummaryElement.addClass('open');
        var cancelAction = modSummaryElement.find(SELECTORS.MODULE_SUMMARY_ACTIONS.CLOSE_MODULE_SUMMARY);
        var addAction = modSummaryElement.find(SELECTORS.MODULE_SUMMARY_ACTIONS.ADD_MODULE);

        $(cancelAction).attr('tabindex', 0);
        $(addAction).attr('tabindex', 0);
    };

    var setTypeSummaryPositionAndHeight = function(element, position, height) {
        var modSummaryContentElement = element.find('.content');
        var modSumarryFooterElement = element.find('.actions');
        var contentHeight = height - modSumarryFooterElement.outerHeight();
        modSummaryContentElement.css({'height' : contentHeight + 'px'});

        element.css({'top' : position + 'px', 'height' : height + 'px'});
    };

    var moveFocus = function(focusedElement, itemTo) {
        $(moduleItemTo).attr('tabindex', 0);
        $(moduleItemTo).focus();
        $(moduleItemFrom).attr('tabindex', -1);
    };

    /**
      * Display the module chooser.
      *
      * @method display_chooser
      * @param {EventFacade} e Triggering Event
      */
    var displayChooser = function(e, sectionid) {
        //var bb, dialogue, thisevent;
        prepareChooser(sectionid);

        // Stop the default event actions before we proceed.
        // e.preventDefault();

       // // bb = panel.get('boundingBox');
       //  dialogue = container.find('.alloptions');
       //
       //  // Find all anchors used to add an activity and append the section param to the url.
       //  var anchors = dialogue.find('.option .modicon a, .option .typesummary .action-footer .addbutton');
       //  anchors.each(function(index, anchor) {
       //      anchor.href += '&section=' + sectionid;
       //  });
       //
       //  thisevent = $('.info.actionelement').on('click', function(e) {
       //      var typeSummaryEl = $(e.target).closest('label').find('.typesummary');
       //      openModSummary(typeSummaryEl);
       //  });
       //  listenevents.push(thisevent);
       //
       //  thisevent = $('.closetypesummary').on('click', function(e) {
       //      setTimeout(function() {
       //          var typeSummaryEl = $(e.target).closest('label').find('.typesummary');
       //          typeSummaryEl.removeClass('open');
       //
       //          $('.alloptions').removeClass('noscroll');
       //      }, 100);
       //  });
       //  listenevents.push(thisevent);
       //
       //  // This will detect a change in orientation and retrigger centering.
       //  thisevent = $(document).on('orientationchange', function() {
       //      centerDialogue(dialogue);
       //  });
       //  listenevents.push(thisevent);
       //
       //  // Detect window resizes (most browsers).
       //  thisevent = $(window).on('resize', function() {
       //      centerDialogue(dialogue);
       //  });
       //  listenevents.push(thisevent);
       //
       //  // thisevent = container.on('keyup', function() {
       //  //     console.log("Herreee");
       //  //     //checkOptions();
       //  // });
       //  // listenevents.push(thisevent);
       //
       //  thisevent = $(".option").on('keydown', function(e) {
       //      var index = $(this).index();
       //      var optionOuterWidth = $('.option').outerWidth();
       //      var wrapperWidth = $('.alloptions').width();
       //      var rowOptions = Math.floor(wrapperWidth / optionOuterWidth);
       //      var totalOptions = options.length;
       //      var totalRowsCount = Math.floor(options.length / rowOptions);
       //      var indexRow = Math.ceil((index + 1) / rowOptions);
       //
       //      // Right key.
       //      if (e.keyCode === 39) {
       //          var nextIndex = index + 1;
       //          if (typeof options[nextIndex] === 'undefined') {
       //              return;
       //          }
       //          moveToOption(options[index], options[nextIndex]);
       //      }
       //
       //      // Left key.
       //      if (e.keyCode === 37) {
       //          var prevIndex = index - 1;
       //          if (typeof options[prevIndex] === 'undefined') {
       //              return;
       //          }
       //          moveToOption(options[index], options[prevIndex]);
       //      }
       //
       //      // down key.
       //      if (e.keyCode === 40) {
       //          // If the focus is on an element from the last visual row.
       //          if (indexRow == totalRowsCount) {
       //              return;
       //          }
       //          var nextIndex = index + rowOptions;
       //          if (typeof options[nextIndex] === 'undefined') {
       //              nextIndex = totalOptions - 1;
       //          }
       //          moveToOption(options[index], options[nextIndex]);
       //      }
       //
       //      // up key.
       //      if (e.keyCode === 38) {
       //          // if the focus is on an element from the first visual row.
       //          if (indexRow === 1) {
       //              return;
       //          }
       //          var prevIndex = index - rowOptions;
       //          if (typeof options[prevIndex] === 'undefined') {
       //              prevIndex = 0;
       //          }
       //          moveToOption(options[index], options[prevIndex]);
       //      }
       //
       //      // end key.
       //      if (e.keyCode === 35) {
       //          var lastOptionIndex = totalOptions - 1;
       //          if (index === lastOptionIndex) {
       //              return;
       //          }
       //          moveToOption(options[index], options[lastOptionIndex]);
       //      }
       //
       //      // home key.
       //      if (e.keyCode === 36) {
       //          var firstOptionIndex = 0;
       //          if (index === firstOptionIndex) {
       //              return;
       //          }
       //          moveToOption(options[index], options[firstOptionIndex]);
       //      }
       //
       //      if (e.keyCode === 13 || e.keyCode == 32) {
       //          var actionElements = $(this).find('.actionelement');
       //          // optionactions.each(function(index, optionaction) {
       //          //     $($(optionaction).children()[0]).attr({'tabindex' : 0, 'aria-hidden' : 'false'});
       //          // });
       //          // var modinfo = $(this).find('.info i');
       //          // //$(this).attr('tabindex', -1);
       //          // $(modanchor).attr('tabindex', 0);
       //          // $(modinfo).attr('tabindex', 0);
       //          // $(modinfo).attr('aria-hidden', 'false');
       //          // $(modanchor).focus();
       //           $(actionElements[0]).attr('tabindex', 0);
       //           $(actionElements[0]).focus();
       //           $(this).attr('tabindex', -1);
       //      }
       //  });
       //  listenevents.push(thisevent);
       //
       //  thisevent = $(".actionelement").on('keydown', function(e) {
       //       e.preventDefault();
       //       e.stopPropagation();
       //
       //      var actionElements = $(this).closest("label").find(".actionelement");
       //      var index = $(actionElements).index(this);
       //
       //      // down key.
       //      if (e.keyCode === 40 || e.keyCode === 39) {
       //          var next = index + 1;
       //          if (typeof actionElements[next] === 'undefined') {
       //              return;
       //          }
       //          var nextAction = actionElements[next];
       //          $(nextAction).attr('tabindex', 0);
       //          $(nextAction).focus();
       //          $(this).attr('tabindex', -1);
       //      }
       //
       //      // up key.
       //      if (e.keyCode === 38 || e.keyCode === 37) {
       //          var prev = index - 1;
       //          if (typeof actionElements[prev] === 'undefined') {
       //              return;
       //          }
       //          var prevAction = actionElements[prev];
       //          $(prevAction).attr('tabindex', 0);
       //          $(prevAction).focus();
       //          $(this).attr('tabindex', -1);
       //      }
       //
       //      if (e.keyCode === 27) {
       //          var optionElement = $(this).closest('.option');
       //          $(optionElement).attr('tabindex', 0);
       //          $(optionElement).focus();
       //          $(this).attr('tabindex', -1);
       //      }
       //
       //      if (e.keyCode === 13) {
       //          if ($(this).hasClass('info')) {
       //              var typeSummaryEl = $(this).closest('label').find('.typesummary');
       //              openModSummary(typeSummaryEl);
       //          }
       //          return;
       //      }
       //  });
       //  listenevents.push(thisevent);
       //
       //  // Hide will be managed by cancel_popup after restoring the body overflow.
       //  // thisevent = bb.one('button.closebutton').on('click', function(e) {
       //  //     cancelPopup(e);
       //  // });
       //  // listenevents.push(thisevent);
       //
       //  // Grab global keyup events and handle them
       //  // thisevent = $(document).on('keydown', handleKeyPress(e));
       //  // listenevents.push(thisevent);
       //
       //  // Display the panel
       //  panel.show(e);
       //
       //  // Re-centre the dialogue after we've shown it.
       //  centerDialogue(dialogue);
       //
       //  // Finally, focus the first radio element - this enables form selection via the keyboard.
       //  container.find('.option input[type=radio]').focus();
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
        displayChooser: displayChooser,
        updateHiddenRadioValue: updateHiddenRadioValue
    };
});
