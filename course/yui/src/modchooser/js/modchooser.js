/**
 * The activity chooser dialogue for courses.
 *
 * @module moodle-course-modchooser
 */

var CSS = {
    PAGECONTENT: 'body',
    SECTION: null,
    SECTIONMODCHOOSER: 'span.section-modchooser-link',
    SITEMENU: '.block_site_main_menu',
    SITETOPIC: 'div.sitetopic'
};

var MODCHOOSERNAME = 'course-modchooser';

/**
 * The activity chooser dialogue for courses.
 *
 * @constructor
 * @class M.course.modchooser
 * @extends M.core.chooserdialogue
 */
var MODCHOOSER = function() {
    MODCHOOSER.superclass.constructor.apply(this, arguments);
};

Y.extend(MODCHOOSER, M.core.chooserdialogue, {
    /**
     * The current section ID.
     *
     * @property sectionid
     * @private
     * @type Number
     * @default null
     */
    sectionid: null,

    /**
     * Set up the activity chooser.
     *
     * @method initializer
     */
    initializer: function() {
        var sectionclass = M.course.format.get_sectionwrapperclass();
        if (sectionclass) {
            CSS.SECTION = '.' + sectionclass;
        }
        var dialogue = Y.one('.chooserdialoguebody');
        var header = Y.one('.choosertitle');
        //var params = {};
        var params = {width: '800px'};
        this.setup_chooser_dialogue(dialogue, header, params);

        // Initialize existing sections and register for dynamically created sections
        this.setup_for_section();
        M.course.coursebase.register_module(this);
    },

    /**
     * Update any section areas within the scope of the specified
     * selector with AJAX equivalents
     *
     * @method setup_for_section
     * @param baseselector The selector to limit scope to
     */
    setup_for_section: function(baseselector) {
        if (!baseselector) {
            baseselector = CSS.PAGECONTENT;
        }

        // Setup for site topics
        Y.one(baseselector).all(CSS.SITETOPIC).each(function(section) {
            this._setup_for_section(section);
        }, this);

        // Setup for standard course topics
        if (CSS.SECTION) {
            Y.one(baseselector).all(CSS.SECTION).each(function(section) {
                this._setup_for_section(section);
            }, this);
        }

        // Setup for the block site menu
        Y.one(baseselector).all(CSS.SITEMENU).each(function(section) {
            this._setup_for_section(section);
        }, this);
    },

    /**
     * Update any section areas within the scope of the specified
     * selector with AJAX equivalents
     *
     * @method _setup_for_section
     * @private
     * @param baseselector The selector to limit scope to
     */
    _setup_for_section: function(section) {
        var chooserspan = section.one(CSS.SECTIONMODCHOOSER);
        if (!chooserspan) {
            return;
        }
        var chooserlink = Y.Node.create("<a href='#' />");
        chooserspan.get('children').each(function(node) {
            chooserlink.appendChild(node);
        });
        chooserspan.insertBefore(chooserlink);
        chooserlink.on('click', this.display_mod_chooser, this);
    },
    /**
     * Display the module chooser
     *
     * @method display_mod_chooser
     * @param {EventFacade} e Triggering Event
     */
    display_mod_chooser: function(e) {
        // Set the section for this version of the dialogue
        if (e.target.ancestor(CSS.SITETOPIC)) {
            // The site topic has a sectionid of 1
            this.sectionid = 1;
        } else if (e.target.ancestor(CSS.SECTION)) {
            var section = e.target.ancestor(CSS.SECTION);
            this.sectionid = section.get('id').replace('section-', '');
        } else if (e.target.ancestor(CSS.SITEMENU)) {
            // The block site menu has a sectionid of 0
        }
        this.sectionid = 0;

        this.display_chooser(e);

        // Prevent double click on star from redirecting
        this.container.delegate('dblclick', function(e) {
            // Stop link redirection and any further propagation.
            e.preventDefault();
            e.stopImmediatePropagation();
        }, '.star');

        // Create variable for click callback functions to access.
    //    var pinnedtools = this.userpinnedtools;

        // Listen to pin links.
        var thisevent = this.container.delegate('click', function(e) {
            // Stop link redirection and any further propagation.
            e.preventDefault();
            e.stopImmediatePropagation();

            // Get module details.
            // var module = this.ancestor('.tool');
            // var moduleid = this.ancestor().previous('input').getAttribute('id').split("item_")[1];

            // Add module to pinned tools preference.
      //      pinnedtools.push(moduleid);

            // Update user preferences.
       //     M.util.set_user_preference('pinnedtools', pinnedtools.join(','));

            // Add pinned class.
     //       module.addClass('pinned');

            // Change empty star to filled star.
            MODCHOOSER.toggle_star(this);

        }, '.star');


        this.listenevents.push(thisevent);
    },

    /**
     * Helper function to set the value of a hidden radio button when a
     * selection is made.
     *
     * @method option_selected
     * @param {String} thisoption The selected option value
     * @private
     */
    option_selected: function(thisoption) {
        // Add the sectionid to the URL.
        this.hiddenRadioValue.setAttrs({
            name: 'jump',
            value: thisoption.get('value') + '&section=' + this.sectionid
        });
    }
},
{
    NAME: MODCHOOSERNAME,
    ATTRS: {
        /**
         * The maximum height (in pixels) of the activity chooser.
         *
         * @attribute maxheight
         * @type Number
         * @default 800
         */
        maxheight: {
            value: 800
        }
    },

    /**
     * Static helper function to swap filled and empty stars.
     *
     * @method toggle_star
     * @param {Object} container The wrapper around the star image.
     * @private
     */
    toggle_star: function(container) {
        var star;
        if (container.data('starred')) {
            container.data('starred', false);
            star = container.one('img');
            // star.setAttribute('title', M.util.get_string('addtool', 'moodle'));
            star.setAttribute('src', M.util.image_url('t/emptystar'));
        } else {
            container.data('starred', false);
            star = container.one('img');
            // star.setAttribute('title', M.util.get_string('addtool', 'moodle'));
            star.setAttribute('src', M.util.image_url('i/star'));
        }


        // var star;
        // if (container.hasClass('star_empty')) {
        //     // Toggle from empty star to filled star.
        //     container.removeClass('star_empty');
        //     container.addClass('star');
        //     star = container.one('img');
        //     star.setAttribute('title', M.util.get_string('removetool', 'moodle'));
        //     star.setAttribute('src', M.util.image_url('i/star'));
        // } else {
        //     // Toggle from filled star to empty star.
        //     container.removeClass('star');
        //     container.addClass('star_empty');
        //     star = container.one('img');
        //     star.setAttribute('title', M.util.get_string('addtool', 'moodle'));
        //     star.setAttribute('src', M.util.image_url('i/star_empty'));
        // }
    },

});
M.course = M.course || {};
M.course.init_chooser = function(config) {
    return new MODCHOOSER(config);
};
