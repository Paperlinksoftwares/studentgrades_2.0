<?php

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
 * The gradebook overview report
 *
 * @package   gradereport_overview
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
global $USER;
global $CFG;
$admins = get_admins();
$if_student = 1;

foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}

if($USER->id==74 || $USER->id==213 || $USER->id==1187)
{
	$if_student = 0;
}

if($USER->id==1160)
{
	$if_student = 0;
}

$scv_cat_array=array('138','137','114','128','117','115','113');
$supervised_cat_array=array('116','112','104','98','97');


function getObservationChecklistRating($userid , $course, $assignmentname)
{
	global $DB;
	$percentage = '';
	//echo "hhh".addslashes ($assignmentname); 
	//echo '<hr>';
	$sql_checklist_first = "SELECT count(`id`) as count FROM  `mdl_checklist` WHERE `name` LIKE '%".addslashes($assignmentname)." | Observation Checklist%'";
	$list_count = $DB->get_record_sql($sql_checklist_first);
	
	if($list_count->count>0)
	{
	$sql_checklist = "SELECT mi.`id`, mcl.`id` as assignid , mc.`teachermark` , mcl.`course` , mcl.`name` , mc.`userid` , mc.`item` , mc.`userid`
	, mcm.id as checklistid
	FROM `mdl_checklist_item` as mi 
	LEFT JOIN `mdl_checklist_check` as mc
	ON mc.`item` = mi.`id` 
	LEFT JOIN `mdl_checklist` as mcl 
	ON mi.`checklist` = mcl.`id` 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mc.`userid` = '".$userid."' AND mcl.`course` = '".$course."' AND mcl.`name` LIKE '%".addslashes($assignmentname)." | Observation Checklist%'";

	$list_all_deb = $DB->get_records_sql($sql_checklist);
	
	$arr=array();
	if(count($list_all_deb)>0)
	{
		foreach($list_all_deb as $key=>$val)
		{
			$arr[$val->assignid][]=$val->teachermark;		
		}	
	}
	if(count($arr)>0)
	{
		foreach($arr as $key2=>$val2)
		{
			$pass=0;
			for($k=0;$k<count($val2);$k++)
			{
				if($val2[$k]==1)
				{
					$pass++;
				}
			}
			
		}
		$percentage = (($pass/count($val2))*100);
		$percentage = round($percentage,2);
	}
	}
	else
	{
		$percentage = 'NA';
	}
	return $percentage;
}

//echo '<pre>';
//print_r($rate_arr);
//die;

//$context = get_context_instance(CONTEXT_COURSE, 773, MUST_EXIST);
//$enrolled = is_enrolled($context, 1048, '', true);
//echo $enrolled; 

if(!isset($_POST['studentid']) && isset($_POST['search']))
{
	?>
	<script>
	alert('please select the student from the list only!');
	window.location.href='studentgrades.php';
	</script>
	<?php
}
?>
<script>
    function generateReport(type)
    {
        if(type!='')
        {
            //window.location.href='studentgradereport.php?type='+type;
            //var win = window.open('studentgradereport.php?type='+type, '_blank');
            //win.focus();
            document.getElementById('type').value=type;
            document.getElementById('f1').submit();
        }
    }
    function sendSMS(phone,name)
    {
        
            document.getElementById('phone').value=phone;
            document.getElementById('name').value=name;
            document.getElementById('type').value=3;
            document.getElementById('f1').submit();
       
    }
	function selectAllCheckbox(qualification,countall)
	{
		//alert('Here'+countall+qualification);
		//return false;
		//document.querySelector(qualification+'|'+i).checked = true;
		for(var s=1; s<=countall; s++)
		{
			if (document.getElementById(qualification+s).checked==false)
			{
document.getElementById(qualification+s).checked=true;
			}
			else
			{
				document.getElementById(qualification+s).checked=false;
			}
		}
				//alert('ok');
			
	}
    </script>
    <?php
//$PAGE->set_url(new moodle_url('/grade/report/overview/studentgrades.php', array('id' => $courseid, 'userid' => $userid)));
$PAGE->set_url(new moodle_url('/grade/report/overview/studentgrades.php'));
echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
echo '<style>
#myProgress {
  width: 100%;
  background-color: #ddd;
}
.mySpan{
   display: inline-block;
   width: 117px;
   height: 26px;
   background: red;
   color: white;
   font-weight: bold;
   font-size: 14px;
   padding-left: 5px;
   padding-top:2px;
   margin-left:6px;
   margin-bottom: 4px;
   
}
#myBar {

  height: 30px;
  background-color: #4CAF50;
  text-align: center;
  padding-top:5px;
}
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
#info {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

#customers td, #customers th {
    border: 1px solid #aaa;
    padding: 8px;
}
#info td, #info th {
    border: 1px solid #aaa;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #193779;
    color: white;
}




table#customers2 {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
	border-radius:6px;
	
}
#info {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

#customers2 td, #customers th {
    border: 1px solid #aaa;
    padding: 8px;
	
}
#info td, #info th {
    border: 1px solid #aaa;
    padding: 8px;
}

#customers2 tr:nth-child(even){background-color: #f2f2f2;}

#customers2 tr:hover {background-color: #ddd;}

#customers2 th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #193779;
    color: white;
	
}




#info tr:nth-child(even){background-color: #f2f2f2;}

#info tr:hover {background-color: #ddd;}

#info th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>';
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>';
echo '<style>

ul.pagination {
    display: inline-block;
    padding: 0;
    margin: 0;
}
ul.pagination li a.current_page {
    background-color: #4CAF50;
    color: white;
}
ul.pagination li.dot {
   
    color: #000;
}
ul.pagination li {display: inline;}

ul.pagination li a {
    color: black;
    float: left;
    padding: 8px 16px;
    text-decoration: none;
    border: 1px solid #ddd; /* Gray */
 margin: 0 4px; /* 0 is for top and bottom. Feel free to change it */
}
ul.pagination li a:hover:not(.active) {background-color: #ddd;}
.info-msg,
.success-msg,
.warning-msg,
.error-msg {
  margin: 10px 0;
  padding: 10px;
  border-radius: 3px 3px 3px 3px;
}
.info-msg {
  color: #059;
  background-color: #BEF;
}
.success-msg {
  color: #270;
  background-color: #DFF2BF;
}
.warning-msg {
  color: #9F6000;
  background-color: #FEEFB3;
}
.error-msg {
  color: #D8000C;
  background-color: #FFBABA;
}

.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}

.autocomplete-items {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 0;
  right: 0;
}
.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color: #cef9dd; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #fff; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
input[type=text] {
    width: 100%!important;
    padding: 4px 7px!important;
    margin: 3px 0!important;
    box-sizing: border-box!important;
    border: 1px solid #555!important;
    outline: none!important;
    height:35px!important;
}

input[type=text]:focus {
    background-color: #d9f1fc!important;
}
.accordion_container {
  width: 100%;
}

.accordion_head {
 background-color: #dae2ef;
    color: #020201;
    cursor: pointer;
    font-family: arial;
    font-size: 16px;
    margin: 0 0 1px 0;
    padding: 7px 11px;
    border-radius: 12px;
    border: 1px solid #6f7aca;
    /* padding: 20px; */
    width: 100%;
    height: 50px;
    /* padding-bottom: 11px; */
    padding-top: 11px;
    margin-top: 11px;
  padding-top: 15px;
  height: auto;
}

.accordion_body {
  background: #fff;
}

.accordion_body p {
  padding: 18px 5px;
  margin: 0px;
}

.plusminus {
  float: right;
  font-size: 22px;
}
 .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white!important;
            border-radius: 4px!important;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2)!important;
            padding: 8px 13px!important;
            text-decoration: none!important;
        }

        .button-success {
            background: rgb(28, 184, 65)!important; 
        }
        .styled select {
   background: transparent;
   width: 150px;
   font-size: 16px;
   border: 1px solid #ccc;
   height: 34px; 
} 

.styled{
   float: right;
   width: 136px;
   height: 34px;
   border: 1px solid #111;
   border-radius: 3px;
   overflow: hidden;
}
</style>';

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login(null, false);
$PAGE->set_course($course);

$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;
if(isset($_POST['search']) && $_POST['search'])
{   
   $userid = $_POST['studentid'];
   $userdetails = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
   $_SESSION['userdetails'] = $userdetails;
}
 else {
     $userdetails = $DB->get_record('user', array('id' => $USER->id), '*', MUST_EXIST);
   $_SESSION['userdetails'] = $userdetails;
    
}

// If we are accessing the page from a site context then ignore this check.
if ($courseid != SITEID) {
    require_capability('gradereport/overview:view', $context);
}

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', array('id'=>$userid, 'deleted'=>0)) or isguestuser($userid)) {
        print_error('invaliduserid');
    }
    $personalcontext = context_user::instance($userid);
}

if (isset($personalcontext) && $courseid == SITEID) {
    $PAGE->set_context($personalcontext);
} else {
    $PAGE->set_context($context);
}
if ($userid == $USER->id) {
    $settings = $PAGE->settingsnav->find('mygrades', null);
    $settings->make_active();
} else if ($courseid != SITEID && $userid) {
    // Show some other navbar thing.
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    $PAGE->navigation->extend_for_user($user);
}

$access = false;
if (has_capability('moodle/grade:viewall', $systemcontext)) {
    // Ok - can view all course grades.
    $access = true;

} else if (has_capability('moodle/grade:viewall', $context)) {
    // Ok - can view any grades in context.
    $access = true;

} else if ($userid == $USER->id and ((has_capability('moodle/grade:view', $context) and $course->showgrades)
        || $courseid == SITEID)) {
    // Ok - can view own course grades.
    $access = true;

} else if (has_capability('moodle/grade:viewall', $personalcontext) and $course->showgrades) {
    // Ok - can view grades of this user - parent most probably.
    $access = true;
} else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext) and $course->showgrades) {
    // Ok - can view grades of this user - parent most probably.
    $access = true;
}
 else if ($USER->id==213 || $USER->id==1187) {
    // Ok - can view grades of this user - parent most probably.
    $access = true;
}

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$course->id, 'userid'=>$userid));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'overview';

// First make sure we have proper final grades.
grade_regrade_final_grades_if_required($course);


    // Please note this would be extremely slow if we wanted to implement this properly for all teachers.
    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups and (!$currentgroup)) {
        // no separate group access, can view only self
        $userid = $USER->id;
        $user_selector = false;
    } else {
        $user_selector = true;
    }
	
	/*if($USER->id==74 || $USER->id==213 || $USER->id==1187)
{
	$user_selector = true;
	$userid = $USER->id;
}
*/

if($USER->id==74 || $USER->id==213)
{
	$user_selector = true;
	$userid = $USER->id;
}
    if (empty($userid)) { 
        // Add tabs
        print_grade_page_head($courseid, 'report', 'overview',null, false, false, false, null, null, null);

    //    groups_print_course_menu($course, $gpr->get_return_url('studentgrades.php?id='.$courseid, array('userid'=>0)));
		groups_print_course_menu($course, $gpr->get_return_url('studentgrades.php'));


        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_overview');
            //echo $renderer->graded_users_selector_autosuggest('overview', $course, $userid, $currentgroup, false);
         //   $studentsstr = $renderer->graded_users_selector_autosuggest('overview', $course, $userid, $currentgroup, false);
		 
		 
		 $sql_trainers_list = "SELECT DISTINCT u.id AS userid, u.firstname as fname , u.lastname as lname 
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_role r ON r.id = ra.roleid WHERE r.shortname != ''";
$trainers_list = $DB->get_records_sql($sql_trainers_list);

$arr_check_tr=array();

$st = '';
$ct = count($trainers_list);
$cc = 0;

foreach($trainers_list as $key=>$valu)
{
	if(($ct-$cc)>1)
	{
	$st = $st . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'",';
	}
	else 
	{
		$st = $st . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'"';
	}
	$cc++;
}
	
$studentsstr = "[".$st."]"; 
		 
		 
		 
		 
		 
		 
            if($if_student==0) { echo '<form name="search" autocomplete="off" action="" method="POST">
   <table ><tr><td><div class="autocomplete" style="width:220px;">
   <input id="studentname" type="text" name="studentname" placeholder="Type your Student Name" value="" />
 <input id="studentid" type="hidden" name="studentid" value="" />
            </div></td><td>&nbsp;&nbsp;&nbsp;</td><td><input type="submit" name="search" value=" Search " style="margin: 0 0 0px 0px!important;" /></td></tr></table></form>'; }
        if(isset($userdetails) && $if_student==0 && isset($_POST['search'])) { 
            
            $other_details1 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('15')");
            $other_details2 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('1')");
             echo '&nbsp;Search results for <a href="'.$CFG->wwwroot.'/user/profile.php?id='.$userdetails->id.'" target="_blank"><strong>'.@$userdetails->firstname.' '.@$userdetails->lastname.'</strong></a>'
                    .'<div style="border-radius: 17px;
  border: 2px solid #73AD21;
  padding: 14px; 
  margin-top:13px;
  margin-bottom:18px;
  width: 100%;
  height: 141px;">
  <strong>Username</strong> : '.$userdetails->username.'<br><div style="height:6px;"></div>
  
  <strong>Email</strong> : '.$userdetails->email.'<br><div style="height:6px;"></div>'.
                     '<strong>Gender</strong>: '; if($other_details1->data!='') { echo $other_details1->data; } else { echo 'NA'; } echo '<br><div style="height:6px;"></div>'.
        '<strong>Phone</strong>: '; if($other_details2->data!='') { echo $other_details2->data; } else { echo 'NA'; } echo '</div>'; }
            
                    echo '- <div style="padding-left:10px;float:right; padding-top:5px;"><a onclick="javascript: sendSMS('.$other_details2->data.' , '.@$userdetails->firstname.@$userdetails->lastname.');" class="button-success" href="#">Send SMS</a></div><div style="float:right;"><a class="button-success" href="mailto: '.@$userdetails->email.'">Send Email</a></div>';  
    } 
         if($if_student==0) { echo '<div style="padding-left:10px;float:right; padding-top:5px;">'; ?>
    <a onclick="javascript: sendSMS('<?php echo $other_details2->data; ?>','<?php echo @$userdetails->firstname.' '.@$userdetails->lastname; ?>');" class="button-success" href="#">Send SMS</a></div><div style="padding-left:10px;float:right; padding-top:5px;"><a class="button-success" href="mailto: <?php echo @$userdetails->email; ?>">Send Email</a></div>  <?php } echo '<div class="styled">
   <select onchange="javascript: generateReport(this.value);">
        <option selected value="">Download as</option>
        <option value="1">PDF</option>
        <option value="2">Image</option>
		<option value="4">Word</option>
        
    </select>
</div>'; 



// do not list all users

    } else { // Only show one user's report
        
        $report = new grade_report_overview($userid, $gpr, $context);
        
        print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview') .
                ' - ' . fullname($report->user), false, false, false, null, null, $report->user);
      //  groups_print_course_menu($course, $gpr->get_return_url('studentgrades.php?id='.$courseid, array('userid'=>0)));
	  groups_print_course_menu($course, $gpr->get_return_url('studentgrades.php'));

        if ($user_selector) { 
            $renderer = $PAGE->get_renderer('gradereport_overview');
            //echo $renderer->graded_users_selector_autosuggest('overview', $course, $userid, $currentgroup, false);
           // $studentsstr = $renderer->graded_users_selector_autosuggest('overview', $course, $userid, $currentgroup, false);
		   
		   
		   
		   
		   $sql_trainers_list = "SELECT DISTINCT u.id AS userid, u.firstname as fname , u.lastname as lname 
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_role r ON r.id = ra.roleid WHERE r.shortname != ''";
$trainers_list = $DB->get_records_sql($sql_trainers_list);

$arr_check_tr=array();

$st = '';
$ct = count($trainers_list);
$cc = 0;
$data_all_new = array();
foreach($trainers_list as $key=>$valu)
{
	if(($ct-$cc)>1)
	{
	$st = $st . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'",';
	}
	else 
	{
		$st = $st . '"'.$valu->fname.' '.$valu->lname.'|'.$valu->userid.'"';
	}
	$cc++;
}
	
$studentsstr = "[".$st."]"; 
		   
		   
		   
		   
		   
		   
           if($if_student==0) { echo '<form name="search" autocomplete="off" action="" method="POST">
   <table ><tr><td><div class="autocomplete" style="width:220px;">
   <input id="studentname" type="text" name="studentname" placeholder="Type your Student Name" value="" />
 <input id="studentid" type="hidden" name="studentid" value="" />
           </div></td><td>&nbsp;&nbsp;&nbsp;</td><td><input class="button-success" type="submit" name="search" value=" Search " style="border: 0px solid #ccc!important;" /></td></tr></table></form>'; }
        if(isset($userdetails) && $if_student==0 && isset($_POST['search'])) { 
            
              $other_details1 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('15')");
            $other_details2 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('1')");
            echo '&nbsp;Search results for <a href="'.$CFG->wwwroot.'/user/profile.php?id='.$userdetails->id.'" target="_blank"><strong>'.@$userdetails->firstname.' '.@$userdetails->lastname.'</strong></a>'
                    .'<div style="border-radius: 17px;
  border: 2px solid #73AD21;
  padding: 14px; 
  margin-top:13px;
  margin-bottom:18px;
  width: 100%;
  height: 141px;">
  <strong>Username</strong> : '.$userdetails->username.'<br><div style="height:6px;"></div>
  <strong>Email</strong> : <a href="mailto: '.$userdetails->email.'">'.$userdetails->email.'</a><br><div style="height:6px;"></div>'.
                    '<strong>Gender</strong>: '; if($other_details1->data!='') { echo $other_details1->data; } else { echo 'NA'; } echo '<br><div style="height:6px;"></div>'.
        '<strong>Phone</strong>: '; if($other_details2->data!='') { echo $other_details2->data; } else { echo 'NA'; } echo '</div>'; }
            
            
        if($if_student==0) { echo '<div style="padding-left:10px;float:right; padding-top:5px;">'; ?> <a onclick="javascript: sendSMS('<?php echo $other_details2->data; ?>','<?php echo @$userdetails->firstname.' '.@$userdetails->lastname; ?>');" class="button-success" href="#">Send SMS</a></div><div style="padding-left:10px;float:right; padding-top:5px;"><a class="button-success" href="mailto: <?php echo @$userdetails->email; ?>">Send Email</a></div> 
		 <?php } ?>
		 <div style="padding-left:10px;float:right; padding-top:5px;"><a class="button-success" href="http://localhost/accit-moodle/accit/grade/report/overview/studentgradeprogressadmin.php?userid=<?php echo $userdetails->id; ?>" target="_blank">View Progress</a></div>
         <!-- Download All Submitted Images button -->
        <div style="padding-left:10px;float:right; padding-top:5px;">
        <a class="button-success"
            href="<?php echo $CFG->wwwroot; ?>/mod/assign/export_all_images.php?studentid=<?php echo $userdetails->id; ?>"
            target="_blank">
            Download All Submitted Images
        </a>
        </div>
        <div style="padding-left:10px;float:right; padding-top:5px;">
  <a href="javascript:void(0);"
     class="button-success"
     onclick="submitUnitImages();">
    Download Unit Images
  </a>
</div>

		 <?php
		 echo '<div class="styled">
   <select onchange="javascript: generateReport(this.value);">
        <option selected value="">Download as</option>
        <option value="1">PDF</option>
        <option value="2">Image</option>
		<option value="4">Word</option>
        
    </select>
</div>';  }
                

        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
        } else {
            if ($report->generate_table_data()) {
                //echo '<br />'.$report->print_table(true);
                $data_all = $report->generate_table_data();
                $_SESSION['data_all'] = $data_all;
               // echo '<pre>';
               // print_r($data_all);
				
				foreach($data_all as $kk=>$vv)
				{
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",trim($vv[0]));
					
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}

                    if (stristr($c_name_arr[0],'CMTT')==true) 
                    { 
                        $data_all_new['MSF30322 Certificate III in Cabinet Making and Timber Technology'][] = $vv; 
                    }
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
				}
				
				
			//	echo '<pre>';
              //  print_r($data_all_new);
                ?>
<br/><br/>
<form action="studentgradereport.php" name="f1" id="f1" method="post" target="_blank">

<div class="accordion_container">
    <?php $course_name = array(); 
	foreach($data_all_new as $mm=>$pp) 
	{ 
	if($mm!='NA') { 
	?>
		
	<div style="border: 2px solid black;
    border-radius: 10px;
    height: 50px;
    /* padding-left: 10px; */
    /* padding-top: 9px; */
    font-size: 25px;
    background-color: #35356a;
    color: white;
    padding: 3px 10px 10px 10px; margin-top: 11px;">
	
	<input type="checkbox" onclick="javascript: selectAllCheckbox('<?php echo $mm; ?>','<?php echo count($pp); ?>');" name="selectall" value="" />&nbsp;&nbsp;<?php echo $mm; ?>
	
	</div>
	<?php } else { ?>
	<div style="border: 2px solid black;
    border-radius: 10px;
    height: 50px;
    /* padding-left: 10px; */
    /* padding-top: 9px; */
    font-size: 25px;
    background-color: red;
    color: white;
    padding: 3px 10px 10px 10px; margin-top: 11px;">
	
	<!-- <input type="checkbox" onclick="javascript: selectAllCheckbox('<?php echo $mm; ?>','<?php echo count($pp); ?>');" name="selectall" value="" />&nbsp;&nbsp; -->No Qualification Found
	
	</div>
	<?php } ?>
	
	<div id="<?php echo $mm; ?>" style='display:display;' >
	<?php
	$rowc = 1;
	foreach($pp as $key=>$val) { 
	
	//echo '<pre>';
	//print_r($val);
	$cat_id = $val[2]->category;
	$unit_id = $val[2]->id;
    $context = context_course::instance($val[2]->id);
    $enrolled = is_enrolled($context, $userdetails->id, '', true);
	$course_name[] = $val[0]; $startdate = $val[2]->startdate; 
	if($val[2]->category!=93 && $val[2]->category!=64) {
	?>
    

	
	
        <div class="accordion_head"  <?php echo $val[2]->category; ?>
		<?php 
		
		if($enrolled!=1 && !in_array($val[2]->category,$scv_cat_array) && !in_array($val[2]->category,$supervised_cat_array)) { ?>
		style="border: 2px dotted #ff0000!important; background-color: white!important;" <?php } ?>
		<?php if(date('Y',$startdate)<2017) { ?> style="background-color: #f7f4f4!important;" <?php } ?> 
		>
		<input class='<?php echo $mm; echo '|
        '; echo $rowc;	?>' 
		style="outline: 1px solid black; height: 15px; width:15px; border: none;" type="checkbox" name="select_course[]" id="<?php echo $mm.$rowc;	?>" value="<?php echo $val[2]->id; ?>" data-assignid="<?php echo $list->assignmentid; ?>" />&nbsp;&nbsp;<a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=<?php echo $val[2]->id; ?>" target="_blank" <?php if($val[2]->category==97 || $val[2]->category==98 || $val[2]->category==104 || $val[2]->category==111 || $val[2]->category==112 || $val[2]->category==116 || $val[2]->category==144 || $val[2]->category==164 || $val[2]->category==165 || $val[2]->category==166 || $val[2]->category==168 || $val[2]->category==195 || $val[2]->category==194 || $val[2]->category==201) { ?> style="color: red!important; font-weight: bold!important;" <?php } ?> <?php if($val[2]->category==113 || $val[2]->category==115 || $val[2]->category==117 || $val[2]->category==128 || $val[2]->category==114 || $val[2]->category==137 || $val[2]->category==138) { ?> style="color: green!important; font-weight: bold!important;" <?php } ?> style="color: #000000; font-weight: bold!important;" ><?php echo $val[0]; ?></a>
		<span class="mySpan" id='<?php echo $unit_id; ?>' style='display: none;'>Not Competent</span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #6aad1d; font-weight: bold;"><?php //echo $val[1]; ?></span><span class="plusminus">+</span></div>
        <div class="accordion_body" style="display: none;">
            <p>
         
                <?php
		 
         
         
    //         $context = context_course::instance($val[2]->id);
    //         $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$val[2]->id, 'userid'=>$userid));
    //     $report = new grade_report_user($val[2]->id, $gpr, $context, $userid);
    //    
    //       
    //
    //        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
    //            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
    //        } else {
    //            if ($report->fill_table()) {
    //               // echo '<br />'.$report->print_table(true);
    //            }
    //        }
         
         
         $contextid = context_course::instance($val[2]->id);
    $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '   
            .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id AND app.userid = '.$userid                   
            .' WHERE z.userid = '.$userid.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = '.$userid.') GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id AND app.userid = '.$userid                   
            .' WHERE z.userid = '.$userid.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = '.$userid.') GROUP BY z.assignment , z.userid ';
    
    
	
	$sql_other_checklist_result = 'SELECT gg.id as rowid , gi.itemname, gi.itemmodule , gg.finalgrade, gg.timemodified, gg.itemid FROM {grade_grades} 
	as gg LEFT JOIN {grade_items} as gi ON gg.itemid = gi.id
		WHERE gi.courseid = "'.$val[2]->id.'" AND gg.userid = "'.$userid.'" AND gi.itemmodule ="checklist"';
		
		
		$sql_other_quiz_result = 'SELECT gg.id as rowid , gi.itemname, gi.itemmodule, gg.finalgrade, gg.timemodified , gg.itemid FROM {grade_grades} 
	as gg LEFT JOIN {grade_items} as gi ON gg.itemid = gi.id
		WHERE gi.courseid = "'.$val[2]->id.'" AND gg.userid = "'.$userid.'" AND gi.itemmodule ="quiz" AND gi.itemname LIKE "%compulsory%"';
		
	$list_result_quiz = $DB->get_records_sql($sql_other_quiz_result);
	$list_result_checklist = $DB->get_records_sql($sql_other_checklist_result);
	/*echo '<pre>';
	print_r($list_result_checklist); 
	
	echo '<pre>';
	print_r($list_result_quiz);*/
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}
//echo '<pre>';
//print_r($scale_array);
//echo $sql_all;
//echo '<hr>';
//echo $sql;
//$list_all = $DB->get_records_sql($sql_all);

//$list_all_count = count($list_all);

$list = $DB->get_records_sql($sql);

$arr = array();
//echo '<pre>';
//print_r($list); 



foreach($list as $list)
{
    $sql_module = "SELECT id , deletioninprogress FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
//	echo '<pre>';
//	print_r($list_module);
	if($list_module->deletioninprogress==0)
	{
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>@$list->feedbackposted1,"feedbackposted2"=>@$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
}
            
         
         
         //echo '<pre>';
        // print_r($arr);
if($list_all_count>0) {
    //echo '<pre>';
    //print_r($arr);
    
         ?>
        

         <table id="customers">
  <tr>
    <th>Assignment</th>
    <th>Submission Status</th>
	<?php if(date('Y',$startdate)>2016) { ?>
    <th>Last Updated On</th>
    <th>Graded On</th>
	<?php } ?>
    <th>Result</th>
    <th>Feedback</th>
  </tr>
  <?php $cn = 0; $ct = 0; $rate_arr=array(); foreach($arr as $key=>$val) { 

	//$textWithoutLastWord = str_replace('SUBMISSION','',$val['assignmentname']);
	//$arr_assignname = explode(" ",$val['assignmentname']);
	//$textWithoutLastWord = $arr_assignname[0]." ".$arr_assignname[1]." ".$arr_assignname[2]." ".$arr_assignname[3];
	$rate = getObservationChecklistRating($list->userid,$list->courseid,$val['assignmentname']);
	if($rate!='')
	{
		$rate_percentage = $rate;
	}
	else
	{
		$rate_percentage = '';
	}
		
  if(strtolower($val['result'])=='not satisfactory')
  {
	  ?>
	  <script>
$(document).ready(function(){
	$("#<?php echo $unit_id; ?>").show();
	  //document.getElementById('result<?php echo $mm.$rowc;	?>').style.display='display';
	  });
	  </script>
	  <?php
	  
  }
  
  
        $sql_feedback_file = "SELECT `filename` , `contextid` FROM {files} WHERE `itemid`  = '".$val['itemidother']."' AND `component` = 'assignfeedback_file' AND `filearea` = 'feedback_files' AND `filename`!=''";
        $list_feedback_file = $DB->get_records_sql($sql_feedback_file);
      $cn++; ?>
  <tr >
      <td><?php if($val['countsubmission']>1) { ?> <img style="height:23px; width: 20px;" alt="It has <?php echo $val['countsubmission']; ?> submissions! Please check." title="It has <?php echo $val['countsubmission']; ?> submissions! Please check." src="<?php echo $CFG->wwwroot; ?>/pix/exclamation.png" border="0" /> <?php } ?> 
          <?php if($if_student==0) { ?><a href="<?php echo $CFG->wwwroot; ?>/mod/assign/view.php?id=<?php echo $val['moduleid']; ?>&action=grading" target="_blank"><?php echo $val['assignmentname']; ?></a><?php } else { ?><?php echo $val['assignmentname']; ?><?php } ?></td>
    <td><?php if(strtolower($val['status'])=="new") { echo 'No Submission'; } else { echo $val['status']; } ?></td>
   <!-- <td><?php //echo $val['grademin']; ?></td>
    <td><?php //echo $val['grademax']; ?></td> -->
    <?php if(date('Y',$startdate)>2016) { ?>
	<td><?php echo $val['activitydate']; ?></td>
    <td><?php echo $val['timemodified']; ?></td>
	<?php } ?>
    <td><?php echo $val['result']; ?></td>
    <td>
        
        
       <div class="modal fade" id="myModal<?php echo $val['rowid'].$cn; ?>" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
          <div class="modal-header" style="background-color: #99ccff!important;">
          
          <h4 class="modal-title">Feedback - <?php echo $val['assignmentname']; ?></h4>
        </div>
        <div class="modal-body">
          <?php if($val['feedback']!='') { echo $val['feedback']; } else { echo 'No Feedback found!'; }
          
          foreach($list_feedback_file as $key2=>$val2) 
              { 
          
              if($val2->filename!='.') 
            { echo '<br/><br/>';
         //   echo $val2->filename; 
            $fullpath = $CFG->wwwroot."/pluginfile.php/".$val2->contextid."/assignfeedback_file/feedback_files/".$val['itemidother']."/".$val2->filename."?forcedownload=1";
            echo "&nbsp;<a href='".$fullpath."'>Download ".$val2->filename."</a>";
            }
              }
          ?>
        <?php //if($val['feedback']!='') { ?>  <!-- <p><i>Posted on --> <?php //if($val['feedbackposted1']!='') { echo @date("F j, Y, g:i a",$val['feedbackposted1']); } else { echo @date("F j, Y, g:i a",$val['feedbackposted2']); }  ?></i></p> <?php //} ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>
 
        
       <button style="padding: 5px 11px 5px 11px!important;" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal<?php echo $val['rowid'].$cn; ?>">View</button></td>
  </tr>
  <?php if(!in_array($cat_id,array(100, 101, 102, 103, 94, 91, 90, 84, 80, 77, 74, 71, 68, 66, 62, 61, 56)))
  {
  
  if($rate_percentage!='NA') { 
  
  
  $sql_checklist2 = "SELECT mcl.`id` as assignid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` LIKE '%".addslashes($val['assignmentname'])." | Observation Checklist%'";

	$list_all_deb2 = $DB->get_record_sql($sql_checklist2);
  

  
  
  ?>
  <tr>
  <td colspan="7">


  <table style="width:100%!important;">
  <?php if($list_all_deb2->checklistid>0) { ?>
  <tr>
  <td style="width:20%!important;">
  <strong><a style="color: blue!important;" href="<?php echo $CFG->wwwroot; ?>/mod/checklist/view.php?id=<?php echo $list_all_deb2->checklistid; ?>" target="_blank">Observation checklist</a></strong></td><td><?php if($rate_percentage!='') { ?>
  <div id="myProgress" style="border: 1px solid blue!important;">
  <div id="myBar" style="width:<?php echo $rate_percentage; ?>%!important; color: white!important; font-weight:bold!important;"><?php echo $rate_percentage."%"; ?></div>
  </div><?php } else { ?><div style="text-align:center; color: red!important; font-weight: bold!important;">No attempts yet made</div><?php } ?></td>
  </tr>
  <?php } else { ?>
  <tr>
  <td colspan="2"><div style="text-align:center; color: red!important; font-weight: bold!important;">No Observation Checklist Found</div></td>
  </tr>
  <?php } ?>
  </table>
  </td>
  </tr>
  <?php } } ?>
  <?php $ct++; unset($rate_arr); unset($rate_percentage); unset($rate); } ?>
  
  
</table>
<?php if(count($list_result_checklist)>0 || count($list_result_quiz)>0) { ?>
<p>

 <table id="customers2">
  <tr>
    <th>Item</th>
    <th>Type</th>
	<th>Score</th>
    <th>Result</th>
   <!-- <th>Last Modified</th> -->
	
  </tr>
  <?php
  foreach($list_result_checklist as $key=>$val)
  {
	  
	  
	/*$sql_checklist22 = "SELECT mcl.`id` as itemid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` = '".$val->itemname."'";

	$list_all_deb22 = $DB->get_record_sql($sql_checklist22);*/

  
	  ?>
	   <tr>
      <td>
	  <?php print_r($list_all_deb22); ?>
	  <a target="_blank" href="<?php echo $CFG->wwwroot; ?>/mod/<?php echo $val->itemmodule; ?>/view.php?id=<?php echo $list_all_deb22->checklistid; ?>"><?php echo $val->itemname; ?></a></td>
	  <td><?php echo $val->itemmodule; ?></td>
	  <td><?php if($val->finalgrade!='') { echo intval($val->finalgrade); } else { echo 'NA'; } ?></td>
	  <td><?php if($val->itemmodule=='checklist') { 
	  if($val->finalgrade!='') { if(intval($val->finalgrade)==100) { echo 'Satisfactory'; } else { echo 'Non Satisfactory'; } } else { echo 'Not Yet Graded'; } }
else if($val->itemmodule=='quiz') { if($val->finalgrade!='') { if(intval($val->finalgrade)==10) { echo 'Satisfactory'; } else { echo 'Not Satisfactory'; } } else { echo 'Not Yet Graded'; } }
	 else { } ?></td>
	  <?php //if($val->timemodified!='') { echo @date("F j, Y, g:i a",$val->timemodified); } ?>
</tr>
	  <?php unset($sql_checklist22); unset($list_all_deb22); } ?> 
	  
	  
	  <?php
  foreach($list_result_quiz as $key2=>$val2)
  {
	  
	  
	  $sql_checklist222 = "SELECT mcl.`id` as itemid , mcl.`course` , mcl.`name` 
	, mcm.id as quizid
	FROM `mdl_quiz` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '12'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` = '".$val2->itemname."'";

	$list_all_deb222 = $DB->get_record_sql($sql_checklist222);
	
	
	  ?>
	   <tr >
        <td><a target="_blank" href="<?php echo $CFG->wwwroot; ?>/mod/<?php echo $val2->itemmodule; ?>/view.php?id=<?php echo $list_all_deb222->quizid; ?>"><?php echo $val2->itemname; ?></a></td>
	   <td><?php echo $val2->itemmodule; ?></td>
	  <td><?php if($val2->finalgrade!='') { echo intval($val2->finalgrade); } else { echo 'NA'; } ?></td>
	  <td><?php if($val2->itemmodule=='quiz') { if($val2->finalgrade!='') { if(intval($val2->finalgrade)==10) { echo 'Satisfactory'; } else { echo 'Not Satisfactory'; } } else { echo 'Not Yet Graded'; } }
	 else { } ?></td>
	  
</tr>
	  <?php unset($sql_checklist222); unset($list_all_deb222); } ?> 
       
</table>
  </p><?php } ?>
<?php } else { echo '&nbsp;&nbsp;<span style="color: red!important; font-weight: bold!important;">Student did not open the submission link yet!</span>'; }
         
    
         ?>
     
     </p>
  </div>
  
			<?php  unset($context);  } $rowc++; unset($unit_id); } ?> </div> <?php } ?>
  
</div>
    <input type="hidden" name="type" id="type" value="" />
    <input type="hidden" name="phone" id="phone" value="" />
        <input type="hidden" name="name" id="name" value="" />

        

    </form>
<br/><br/>
<?php
                
            }
        }
    }


//$event = \gradereport_overview\event\grade_report_viewed::create(
//    array(
//        'context' => $context,
//        'courseid' => $courseid,
//        'relateduserid' => $userid,
//    )
//);
//$event->trigger();
    if($if_student==0) {
?>
<script>
function autocomplete(inp, arr, type) { 


  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
var res = arr[i].split("|"); 
var idval = res[1];
var coursename = res[0];
//document.getElementById('coursename').value=coursename;
        /*check if the item starts with the same letters as the text field value:*/
        if (coursename.substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + coursename.substr(0, val.length) + "</strong>";
          b.innerHTML += coursename.substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input name='stuid' id='stuid' type='hidden' value='" + idval + "'>";
          b.innerHTML += "<input name='stuname' id='stuname' type='hidden' value='" + coursename + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) { 
              /*insert the value for the autocomplete text field:*/
             // inp.value = this.getElementsByTagName("input")[0].value; 
                if(type==1)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("courseid").value = this.getElementsByTagName("input")[0].value; 
                }
                else if(type==2)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentid").value = this.getElementsByTagName("input")[0].value; 
                }
                else
                {
                }
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
var res='';
var studentname = '';
var idval = '';
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});
}
//var final_arr = {{{coursestr}}}; 
var final_arr_stu = <?php echo $studentsstr; ?> ; 
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
//autocomplete(document.getElementById("coursename"), final_arr,'1');
autocomplete(document.getElementById("studentname"), final_arr_stu,'2');
</script>
    <?php } ?>
<script>
$(document).ready(function() {
  //toggle the component with class accordion_body
  $(".accordion_head").click(function() {
    if ($('.accordion_body').is(':visible')) {
      $(".accordion_body").slideUp(300);
      $(".plusminus").text('+');
    }
    if ($(this).next(".accordion_body").is(':visible')) {
      $(this).next(".accordion_body").slideUp(300);
      $(this).children(".plusminus").text('+');
    } else {
      $(this).next(".accordion_body").slideDown(300);
      $(this).children(".plusminus").text('-');
    }
  });
});
</script>

<script>
function submitUnitImages() {
    const boxes = document.querySelectorAll(
        'input[name="select_course[]"]:checked'
    );
    if (boxes.length === 0) {
        alert('Please tick at least one unit.');
        return;
    }

    const params = new URLSearchParams();
    params.append('studentid', <?php echo (int)$userdetails->id; ?>);

    // ❌ WRONG: uses assignment IDs
    // boxes.forEach(cb => params.append('assignid[]', cb.dataset.assignid));

    // ✅ CORRECT: use the course IDs already in the checkbox value
    boxes.forEach(cb => {
        params.append('assignid[]', cb.value);
    });

    window.open(
        '<?php echo $CFG->wwwroot; ?>/mod/assign/export_all_images.php?' +
        params.toString(),
        '_blank'
    );
}
</script>


<?php
echo $OUTPUT->footer();