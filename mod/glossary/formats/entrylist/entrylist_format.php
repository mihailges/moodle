<?php

function glossary_show_entry_entrylist($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1, $aliases=true) {
    global $USER, $OUTPUT;

    $return = false;

    if ($entry) {
        echo html_writer::start_tag('div', array('class'=>'glossarypost entrylist'));
        echo html_writer::start_tag('div', array('class'=>'entryapproval m-b-2'));
        glossary_print_entry_approval($cm, $entry, $mode);
        echo html_writer::end_tag('div'); // entryapproval
        echo html_writer::start_tag('div', array('class' => 'd-inline-block w-100'));

        echo html_writer::start_tag('div', array('class'=>'entry pull-left'));
        $anchortagcontents = glossary_print_entry_concept($entry, true);
        $link = new moodle_url('/mod/glossary/showentry.php', array('courseid' => $course->id,
                'eid' => $entry->id, 'displayformat' => 'dictionary'));
        $anchor = html_writer::link($link, $anchortagcontents);
        echo html_writer::div($anchor, 'concept');
        echo html_writer::end_tag('div'); // entry
        echo html_writer::start_tag('div', array('class'=>'entrylowersection pull-right'));
        if (!empty($entry->rating)) {
            echo html_writer::start_tag('div', array('class'=>'ratings m-b-1'));
            $return = glossary_print_entry_ratings($course, $entry);
            echo html_writer::end_tag('div'); // ratings
        }
        if ($printicons) {
            glossary_print_entry_icons($course, $cm, $glossary, $entry, $mode, $hook,'print');
        }
        echo html_writer::end_tag('div'); // entrylowersection

        echo html_writer::end_tag('div'); // d-inline-block

        echo html_writer::empty_tag('hr');
        echo html_writer::end_tag('div'); // glossarypost
    } else {
        echo html_writer::div(get_string('noentry', 'glossary'), '',
            array('style' => 'text-align:center;'));
    }
    return $return;
}

function glossary_print_entry_entrylist($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1) {
    //Take out autolinking in definitions un print view
    // TODO use <nolink> tags MDL-15555.
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    echo html_writer::start_tag('table', array('class' => 'glossarypost entrylist mod-glossary-entrylist'));
    echo html_writer::start_tag('tr');
    echo html_writer::start_tag('td', array('class' => 'entry mod-glossary-entry'));
    echo html_writer::start_tag('div', array('class' => 'mod-glossary-concept'));
    glossary_print_entry_concept($entry);
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'mod-glossary-definition'));
    glossary_print_entry_definition($entry, $glossary, $cm);
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'mod-glossary-lower-section'));
    glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, false, false);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('table');
}


