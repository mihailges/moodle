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
    initializer: function(config) {
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

        console.log(config.starredmodules);
        // Save preferences for pinned tools
        if (config.starredmodules) {
            MODCHOOSER.STARREDMODULES = config.starredmodules.split(",");
        }
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

        // Prevent double click on star from redirecting
        Y.delegate('dblclick', function(e) {
            // Stop link redirection and any further propagation.
            e.preventDefault();
            e.stopImmediatePropagation();
        }, '.star, .star_empty');

        this.display_chooser(e);

        // Listen to pin links.
        var thisevent = this.container.delegate('click', function(e) {
            // Stop link redirection and any further propagation.
            e.preventDefault();
            e.stopImmediatePropagation();

            // Get module details.
            // var module = this.ancestor('.tool');
            var module_id = this.ancestor(".option").one("input[type='radio']").getAttribute('id');
            var module = module_id.split("item_")[1];

            // Change empty star to filled star.
            MODCHOOSER.toggle_star(this, module_id, function(starredel) {
                var starred_section = Y.one('#starred');

                if (starredel.getAttribute('data-starred') == "true") {
                    if (MODCHOOSER.STARREDMODULES.indexOf(module) == -1) {
                        MODCHOOSER.STARREDMODULES.push(module);
                    }
                } else {
                    if (starred_section.one('#' + module_id)) {
                       if (MODCHOOSER.STARREDMODULES.indexOf(module) != -1) {
                           var index = MODCHOOSER.STARREDMODULES.indexOf(module);
                           MODCHOOSER.STARREDMODULES.splice(index, 1);
                       }
                    }
                }
                // Update user preferences.
                M.util.set_user_preference('userstarredmodules', MODCHOOSER.STARREDMODULES.sort().join(','));
                MODCHOOSER.update_starred_section();
            });
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

    STARREDMODULES: [],

    /**
     * Static helper function to swap filled and empty stars.
     *
     * @method toggle_star
     * @param {Object} container The wrapper around the star image.
     * @private
     */
    toggle_star: function(container, module_id, callback) {
        var star = container.one('i');
        // TODO: set to use templates.
        if (container.getAttribute('data-starred') == "true") {
            container.setAttribute('data-starred', false);
            star.replaceClass('fa-star', 'fa-star-o');
            // If the unstarring action is taken in the starred section, unstar the option the activities/resources section as well.
            if (container.ancestor("#starred")) {
                if (Y.one('#activities #' + module_id)) {
                    container = Y.one('#activities #' + module_id).ancestor().one('.star');
                } else {
                    container = Y.one('#resources #' + module_id).ancestor().one('.star');
                }
                MODCHOOSER.toggle_star(container, module_id);
            }
        } else {
            container.setAttribute('data-starred', true);
            // star.setAttribute('title', M.util.get_string('addtool', 'moodle'));
            star.replaceClass('fa-star-o', 'fa-star');
        }

        if (callback) {
            callback(container);
        }
    },

    update_starred_section: function() {
        var starred_section = Y.one('#starred');
        starred_section.empty();
        var starredmodules = MODCHOOSER.STARREDMODULES.sort();
        console.log(starredmodules);
        if (starredmodules.length) {
            starredmodules.forEach(function(module) {
                var option = Y.one('#item_' + module).ancestor('.option');
                var clone_option = option.cloneNode(true);
                starred_section.append(clone_option);
            });
        }
    },

});
M.course = M.course || {};
M.course.init_chooser = function(config) {
    return new MODCHOOSER(config);
};


}, '@VERSION@', {"requires": ["moodle-core-chooserdialogue", "moodle-course-coursebase"]});
