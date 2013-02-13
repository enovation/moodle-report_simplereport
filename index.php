<?php

/* This is Admin report that display all courses and
 * number of students enrolled in each course
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/lib/statslib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

$report_table = new html_table();
$report_table->head = array("Course", "Enrolled Teachers", "Enrolled Students", "Enrolled Other Users", "Total");
$report_table->data = array();
//define global variables
global $DB;
global $PAGE;

$page = optional_param('page', 0, PARAM_INT);

//define and set url for a page
$url = new moodle_url('/report/simplereport/index.php');
$PAGE->set_url($url);
require_login();
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_pagelayout('report');
//$PAGE->requires->css('styles.css');
//add report name to navigation
$navigation = $PAGE->navigation;
$navigation->add(get_string('pluginname', 'report_simplereport'), $url, navigation_node::TYPE_SETTING, null, null,
        new pix_icon('i/report', ''));



//get total number of courses
$totalcourses = $DB->count_records_sql('SELECT count(id) FROM {course} WHERE shortname <> ?', array('mysite'));
//print $totalcourses;


$teachers = array();
$students = array();
$total = array();
$courses = array();
$pagingbar = new paging_bar($totalcourses, $page, 5, $CFG->wwwroot . '/report/simplereport/index.php');
$cur_page = $pagingbar->page;
$first = 0 + ($cur_page * 5);
$left = $totalcourses - $first;
if ($left >= 5) {
    $last = 5;
} else {
    $last = $left;
}

$limit = 'LIMIT ' . $first . ', ' . $last;

$result_students = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
FROM {role_assignments} ra, {user} u, {course} c, {context} cxt 
WHERE ra.userid = u.id
AND ra.contextid = cxt.id
AND cxt.contextlevel =50
AND cxt.instanceid = c.id
AND ra.roleid =5 
group by c.id ' . $limit);
$result_teachers = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
WHERE ra.userid = u.id
AND ra.contextid = cxt.id
AND cxt.contextlevel =50
AND cxt.instanceid = c.id
AND ra.roleid =3 
group by c.id ' . $limit);
$result_all = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
FROM {role_assignments} ra, {user} u, {course} c, {context} cxt 
WHERE ra.userid = u.id
AND ra.contextid = cxt.id
AND cxt.contextlevel =50
AND cxt.instanceid = c.id 
group by c.id ' . $limit);


//loop throught each record
foreach ($result_students as $record) {
    array_push($courses, $record->fullname);
    array_push($students, $record->total);
}
foreach ($result_teachers as $record) {
    array_push($teachers, $record->total);
}
foreach ($result_all as $record) {
    array_push($total, $record->total);
}

$i = 0;
foreach ($courses as $course) {
    $other = $total[$i] - ($teachers[$i] + $students[$i]);
    array_push($report_table->data, array($course, $teachers[$i], $students[$i], $other, $total[$i]));
    //$message = $message . "<tr><td>$course</td><td>$teachers[$i]</td><td>$students[$i]</td><td>$other</td><td>$total[$i]</td></tr>";
    $i++;
}

//output report on the page
echo $OUTPUT->header();
//echo $table_start . $message . $table_end;

echo html_writer::table($report_table);
echo $OUTPUT->render($pagingbar);
echo $OUTPUT->footer();
