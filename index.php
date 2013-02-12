<?php

/* This is Admin report that display all courses and
 * number of students enrolled in each course
 */
require_once('../../config.php');
require_once($CFG->dirroot . '/lib/statslib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

//define variables
//$message = "";
//$table_start = "<table><tr><th rowspan='2'>Course</th><th colspan='4'>Enrolled Users</th></tr>
//                <tr><th>Teachers</th><th>Students</th><th>Other</th><th>Total</th>";
//$table_end = "</table>";

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


//select all courses and count students on each course
/* $result = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =5 group by c.id');
 */

/*
  $result = $DB->get_records_sql('SELECT c.id, ra.id, ra.roleid, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  group by c.id');
  //print_object($result);
  $teachers = 0;
  $students = 0;
  $other = 0;
  //loop throught each record
  foreach($result as $record){
  //print_object($record);
  $course_name = $record->fullname;
  $totals = $record->total;

  print $record->roleid;
  if($record->roleid==5){
  $students++;
  }else if($record->roleid==3){
  $teachers++;
  }else{
  $other++;
  }




  $message = $message . "<tr><td>$course_name</td><td>$teachers</td><td>$students</td><td>$other</td><td>$totals</td></tr>";

  }

 */
/*
  $result_students = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =5 group by c.id');
  //loop throught each record
  foreach($result_students as $record){
  $course_name = $record->fullname;
  $students = $record->total;
 */

/*
  $result_students = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =5
  group by c.id');
  $result_teachers = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =3
  group by c.id');
  $result_all = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  group by c.id');
 */

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

/*
  if ($pagingbar->page == 0) {
  $result_students = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =5
  group by c.id LIMIT 0, 5');
  $result_teachers = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =3
  group by c.id LIMIT 0, 5');
  $result_all = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  group by c.id LIMIT 0, 5');
  } else {
  $result_students = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =5
  group by c.id LIMIT 5, 2');
  $result_teachers = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  AND ra.roleid =3
  group by c.id LIMIT 5, 2');
  $result_all = $DB->get_records_sql('SELECT ra.id, c.id, c.fullname, count(u.username) as total
  FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
  WHERE ra.userid = u.id
  AND ra.contextid = cxt.id
  AND cxt.contextlevel =50
  AND cxt.instanceid = c.id
  group by c.id LIMIT 5, 2');
  }
 */



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

//$total = count($courses);


/*
  $table_data = $report_table->data;
  $page1_data = array_slice($table_data, 0, 5);
  $page2_data = array_slice($table_data, 6, (count($table_data) - 1));
 */
//output report on the page
echo $OUTPUT->header();
//echo $table_start . $message . $table_end;

/*
  if ($pagingbar->page == 0) {
  $report_table->data = $page1_data;
  } else {
  $report_table->data = $page2_data;
  }
 */
echo html_writer::table($report_table);
echo $OUTPUT->render($pagingbar);
echo $OUTPUT->footer();
