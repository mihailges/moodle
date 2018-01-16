<?php

function glossary_show_entry_fullwithoutauthor($course, $cm, $glossary, $entry, $mode="", $hook="", $printicons=1, $aliases=true) {
    global $CFG, $USER, $OUTPUT;


    if ($entry) {
        echo html_writer::start_tag('div', array('class'=>'glossarypost fullwithoutauthor'));

        echo html_writer::start_tag('div', array('class' => 'd-inline-block w-100'));

        echo html_writer::start_tag('div', array('class'=>'entryheader pull-left'));
        echo html_writer::start_tag('div', array('class'=>'concept'));
        glossary_print_entry_concept($entry);
        echo html_writer::end_tag('div'); // concept
        echo html_writer::span(get_string('lastedited').': ', 'time');
        echo html_writer::end_tag('div'); // entryheader
        echo html_writer::start_tag('div', array('class'=>'entryapproval'));
        glossary_print_entry_approval($cm, $entry, $mode);
        echo html_writer::end_tag('div'); // entryapproval
        echo html_writer::end_tag('div'); // d-inline-block
        echo html_writer::start_tag('div', array('class'=>'entryattachment'));
        echo html_writer::end_tag('div'); // entryattachment

        echo html_writer::start_tag('div', array('class'=>'entry'));
        glossary_print_entry_definition($entry, $glossary, $cm);
        glossary_print_entry_attachment($entry, $cm, 'html');

        if (core_tag_tag::is_enabled('mod_glossary', 'glossary_entries')) {
            echo $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('mod_glossary', 'glossary_entries', $entry->id), null, 'glossary-tags m-t-1');
        }
        echo html_writer::end_tag('div'); // entry
        echo html_writer::start_tag('div', array('class'=>'entrylowersection'));
        glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, $printicons, $aliases);

        echo html_writer::end_tag('div'); // entrylowersection

        echo html_writer::empty_tag('hr');
        echo html_writer::end_tag('div'); // glossarypost
    } else {
        echo '<center>';
        print_string('noentry', 'glossary');
        echo '</center>';
    }
}

function glossary_print_entry_fullwithoutauthor($course, $cm, $glossary, $entry, $mode="", $hook="", $printicons=1) {

    //The print view for this format is exactly the normal view, so we use it

    //Take out autolinking in definitions un print view
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    //Call to view function (without icons, ratings and aliases) and return its result
    return glossary_show_entry_fullwithoutauthor($course, $cm, $glossary, $entry, $mode, $hook, false, false, false);

}


