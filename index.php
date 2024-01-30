<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Index for local_hugosoares.
 *
 * @package    local_hugosoares
 * @copyright  2024 Hugo Soares
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir.'/completionlib.php');

use core_course\external\course_summary_exporter;

// Only available to site admins.
// TODO: Consider implementing capability checks instead if needed.
if (!is_siteadmin()) {
  // TODO: Is this okay or do we prefer throwing access exception?
  redirect(new moodle_url("/"));
}

if (empty($CFG->enablecompletion)) {  
  echo $OUTPUT->header();
  echo get_string('completionnotenabledsitewide', 'local_hugosoares');
  echo $OUTPUT->footer();
  exit;
}

$userid = optional_param('userid', null, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url("/local/hugosoares/index.php"));
$PAGE->set_pagelayout("admin");
$PAGE->set_title(get_string("pluginname", "local_hugosoares"));
$PAGE->set_heading($SITE->fullname);
$PAGE->requires->js_call_amd("local_hugosoares/userselector", 'init', []);
$PAGE->navbar->add(get_string("pluginname", "local_hugosoares"));

$userselector = new local_hugosoares\user_selector('local_hugosoares_user_selector');
$form = new local_hugosoares\user_form();

$html = $OUTPUT->header();
if ($data = $form->get_data() && $userid !== null) {
  // Display report for the user here.
  $user = $DB->get_record('user', array('id'=> $userid),'*', MUST_EXIST);
  $courses = enrol_get_users_courses($userid, false, null, 'fullname');
  
  $html .= html_writer::span(get_string('showinguser', 'local_hugosoares', $user->username));

  $table = new html_table();
  $table->head = [
    get_string('coursename', 'local_hugosoares'),
    get_string('completionstatus', 'local_hugosoares'),
    get_string('completiondate', 'local_hugosoares')
  ];

  foreach ($courses as $course) {
    // There are so many ways of doing this, but I just want something simple.
    $info = new completion_info($course);
    $complete = course_summary_exporter::get_course_progress($course);
    $timecompleted = $info->timecompleted ?? false;
    if ($complete && !$timecompleted) {
      $modules = $info->get_activities();
      // There are better ways of doing this, I just don't have enough time.
      if (!count($modules)) {
        $complete = false;
      }

      $timecompleted = 0;
      $completedstatuses = [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS];
      foreach ($modules as $module) {
        $data = $info->get_data($module, true, $userid);
        if ($data->timemodified < $timecompleted) {
          continue;
        }
        if (!in_array($data->completionstate, $completedstatuses)) {
          continue;
        }
        $timecompleted = $data->timemodified;
      }
    }

    $url = new moodle_url('/course/view.php', ['id' => $course->id]);
    $row = new html_table_row();

    $row->cells[] = html_writer::link($url, $course->fullname);
    $row->cells[] = $complete == true ? 
      get_string('complete', 'local_hugosoares') : 
      get_string('notcomplete', 'local_hugosoares');
    $row->cells[] = $complete ? 
      userdate($timecompleted) :
      get_string('na', 'local_hugosoares');

    $table->data[] = $row;
  }
  $html .= html_writer::table($table);
 
  $html .= $OUTPUT->single_button(new moodle_url($PAGE->url), get_string('back'), 'post'); 
} else {
  // Display user selection here.
  $html .= $userselector->display(true);
  $html .= $form->render();
}
$html .= $OUTPUT->footer();

echo $html;
