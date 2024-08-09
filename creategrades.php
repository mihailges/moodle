<?php
define('CLI_SCRIPT', true);

require(__DIR__ . '/config.php');
require_once(__DIR__ . '/mod/assign/lib.php');

$courseid = (int)$argv[1];
$skip = 0;
if (count($argv) > 2) {
    $skip = (int)$argv[2];
}

$assigns = $DB->get_records('assign', ['course' => $courseid], 'id');
if (count($assigns) !== 100) {
    echo "You must run this on a 'medium' test course which should have 100 assignments.\n";
    exit;
}

$context = \context_course::instance($courseid);
$users = get_enrolled_users($context);

echo "Creating grades for ". count($users) . " users on " . (100 - $skip) . " assignments.\n\n";

$index = 1;
foreach ($assigns as $assign) {
    if ($skip) {
        $skip--;
        continue;
    }
    echo $assign->name . ": ";

    // Make up different random grade for each user on each assignment, to keep it interesting.
    $grades = [];
    foreach ($users as $user) {
        $grades[$user->id] = (object)['rawgrade' => mt_rand(0, 100), 'userid' => $user->id];
    }
    // Update the grades.
    $assign->cmidnumber = null;
    $transaction = $DB->start_delegated_transaction();
    $before = microtime(true);
    assign_grade_item_update($assign, $grades);
    $after = microtime(true);
    $transaction->allow_commit();
    echo "done (" . round($after - $before, 1) . " seconds)\n";
}

echo "\nAll done\n";
