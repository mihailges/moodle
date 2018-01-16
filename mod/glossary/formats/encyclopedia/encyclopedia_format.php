<?php

function glossary_show_entry_encyclopedia($course, $cm, $glossary, $entry, $mode='',$hook='',$printicons=1, $aliases=true) {
    global $CFG, $USER, $DB, $OUTPUT;

    $user = $DB->get_record('user', array('id'=>$entry->userid));
    $strby = get_string('writtenby', 'glossary');

    if ($entry) {
        echo html_writer::start_tag('div', array('class'=>'glossarypost encyclopedia'));

        echo html_writer::start_tag('div', array('class' => 'd-inline-block w-100'));

        echo html_writer::start_tag('div', array('class'=>'left picture pull-left'));
        echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));
        echo html_writer::end_tag('div'); // left picture
        echo html_writer::start_tag('div', array('class'=>'entryheader pull-left'));
        echo html_writer::start_tag('div', array('class'=>'concept'));
        glossary_print_entry_concept($entry);
        echo html_writer::end_tag('div'); // concept
        $fullname = fullname($user);
        $by = new stdClass();
        $by->name = html_writer::link($CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id, $fullname);
        $by->date = userdate($entry->timemodified);
        echo html_writer::span(get_string('bynameondate', 'forum', $by), 'author');
        echo html_writer::end_tag('div'); // entryheader
        echo html_writer::start_tag('div', array('class'=>'entryapproval pull-right'));
        glossary_print_entry_approval($cm, $entry, $mode);
        echo html_writer::end_tag('div'); // entryapproval

        echo html_writer::end_tag('div'); // d-inline-block

        echo html_writer::start_tag('div', array('class'=>'entry m-t-1'));
        glossary_print_entry_definition($entry, $glossary, $cm);
        glossary_print_entry_attachment($entry, $cm, null);
        if (core_tag_tag::is_enabled('mod_glossary', 'glossary_entries')) {
            echo $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('mod_glossary', 'glossary_entries', $entry->id), null, 'glossary-tags m-t-1');
        }
        echo html_writer::end_tag('div'); // entry

        if ($printicons or $aliases) {
            echo html_writer::start_tag('div', array('class'=>'entrylowersection'));
            glossary_print_entry_lower_section($course, $cm, $glossary, $entry,$mode,$hook,$printicons,$aliases);
            echo html_writer::end_tag('div'); // entrylowersection
        }
        echo html_writer::empty_tag('hr');
        echo html_writer::end_tag('div'); // glossarypost

    } else {
        echo html_writer::div(get_string('noentry', 'glossary'), '',
            array('style' => 'text-align:center;'));
    }
}

function glossary_print_entry_encyclopedia($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1) {

    //The print view for this format is exactly the normal view, so we use it

    //Take out autolinking in definitions un print view
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    //Call to view function (without icons, ratings and aliases) and return its result

    return glossary_show_entry_encyclopedia($course, $cm, $glossary, $entry, $mode, $hook, false, false);

}


