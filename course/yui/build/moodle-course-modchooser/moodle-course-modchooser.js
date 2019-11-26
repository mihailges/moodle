YUI.add('moodle-course-modchooser', function (Y, NAME) {

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
        var params = {width: '800'};
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
            this.sectionid = 0;
        }

        this.prepare_chooser();

        // Stop the default event actions before we proceed
        e.preventDefault();

        var bb = this.panel.get('boundingBox');
        var dialogue = this.container.one('.alloptions');

        // This will detect a change in orientation and retrigger centering
        var thisevent = Y.one('document').on('orientationchange', function() {
            this.center_dialogue(dialogue);
        }, this);
        this.listenevents.push(thisevent);

        // Detect window resizes (most browsers)
        thisevent = Y.one('window').on('resize', function() {
            this.center_dialogue(dialogue);
        }, this);
        this.listenevents.push(thisevent);

        // These will trigger a check_options call to display the correct help
        thisevent = this.container.on('click', this.check_options, this);
        this.listenevents.push(thisevent);
        thisevent = this.container.on('key_up', this.check_options, this);
        this.listenevents.push(thisevent);
        thisevent = this.container.on('dblclick', function(e) {
            if (e.target.ancestor('div.option')) {
                this.check_options();

                // Prevent duplicate submissions
                this.submitbutton.setAttribute('disabled', 'disabled');
                this.options.setAttribute('disabled', 'disabled');
                this.cancel_listenevents();

                this.container.one('form').submit();
            }
        }, this);
        this.listenevents.push(thisevent);

        this.container.one('form').on('submit', function() {
            // Prevent duplicate submissions on submit
            this.submitbutton.setAttribute('disabled', 'disabled');
            this.options.setAttribute('disabled', 'disabled');
            this.cancel_listenevents();
        }, this);

        // Hook onto the cancel button to hide the form
        thisevent = this.container.one('.addcancel').on('click', this.cancel_popup, this);
        this.listenevents.push(thisevent);

        // Hide will be managed by cancel_popup after restoring the body overflow
        thisevent = bb.one('button.closebutton').on('click', this.cancel_popup, this);
        this.listenevents.push(thisevent);

        // Grab global keyup events and handle them
        thisevent = Y.one('document').on('keydown', this.handle_key_press, this);
        this.listenevents.push(thisevent);

        // Add references to various elements we adjust
        this.submitbutton = this.container.one('.submitbutton');

        // Disable the submit element until the user makes a selection
        this.submitbutton.set('disabled', 'true');

        // Ensure that the options are shown
        this.options.removeAttribute('disabled');

        // Display the panel
        this.panel.show(e);

        // Re-centre the dialogue after we've shown it.
        this.center_dialogue(dialogue);

        // Finally, focus the first radio element - this enables form selection via the keyboard
        this.container.one('.option input[type=radio]').focus();

        // Trigger check_options to set the initial jumpurl
        this.check_options();
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
    }
});
M.course = M.course || {};
M.course.init_chooser = function(config) {
    return new MODCHOOSER(config);
};


}, '@VERSION@', {"requires": ["moodle-core-chooserdialogue", "moodle-course-coursebase"]});
