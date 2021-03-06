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
 * View the mposter instance
 *
 * @package     mod_mposter
 * @copyright   Original Poster by 2015 David Mudrak <david@moodle.com>, modified by Roberto Becerra, 2020 <roberto.becerra@lmta.lt>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/completionlib.php');

$cmid = required_param('id', PARAM_INT);
$edit = optional_param('edit', null, PARAM_BOOL);

$cm = get_coursemodule_from_id('mposter', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$mposter = $DB->get_record('mposter', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
require_capability('mod/mposter:view', $PAGE->context);

$PAGE->set_url('/mod/mposter/view.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname.': '.$mposter->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($mposter);

if ($edit !== null and confirm_sesskey() and $PAGE->user_allowed_editing()) {
	$USER->editing = $edit;
    	redirect($PAGE->url);
}

// Trigger module viewed event.
$event = \mod_mposter\event\course_module_viewed::create(array(
   'objectid' => $mposter->id,
   'context' => $PAGE->context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('mposter', $mposter);
$event->trigger();

// Mark the module instance as viewed by the current user.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Define the custom block regions we want to use at the mposter view page.
// Region names are limited to 16 characters.
$PAGE->blocks->add_region('mod_mposter-pre', true);
$PAGE->blocks->add_region('mod_mposter-post', true);

$output = $PAGE->get_renderer('mod_mposter');

echo $output->view_page($mposter);


