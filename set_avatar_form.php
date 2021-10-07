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
 * Game block config form definition
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/lib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php' );

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;

$courseid = required_param('id', PARAM_INT);

$avatar = optional_param('avatar', 0, PARAM_INT);
$back = optional_param('back', 0, PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$game = $DB->get_record('block_game', array('courseid' => $courseid, 'userid' => $USER->id));

require_login($course);

$changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
if ($courseid == SITEID) {
    $config = get_config('block_game');
} else {
    $config = block_game_get_config_block($courseid);
}

if ($avatar > 0) {
    $gamenew = new stdClass();
    $gamenew->id = $game->id;
    $gamenew->userid = $USER->id;
    $gamenew->avatar = $avatar;
    block_game_update_avatar_game($gamenew);
    if ($back > 0) {
        redirect($CFG->wwwroot . "/course/view.php?id=" . $courseid);
    }
}

$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/set_avatar_form.php', array('id' => $courseid, 'back' => $back, 'avatar' => $avatar));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('set_avatar_title', 'block_game'));
$PAGE->set_heading(get_string('set_avatar_title', 'block_game'));
echo $OUTPUT->header();

echo html_writer::tag('h3', get_string('selectyouragentavatar', 'block_game'));

$outputhtml = "";
if ($changeavatar || $courseid == SITEID) {
    $outputhtml .= '<table style="max-width: 750px;" border="0">';
    $outputhtml .= '<tr>';
    $contlevel = 0;
    $imgsize = ' height="100" width="100" ';
    for ($i = 1; $i < 57; $i++) {
        $outputhtml .= '<td width="25%">';
        $outputhtml .= '<form action="" method="post">';
        $outputhtml .= '<input name="id" type="hidden" value="' . $courseid . '"/>';
        $outputhtml .= '<input name="avatar" type="hidden" value="' . $i . '"/>';
        $outputhtml .= '<input name="back" type="hidden" value="1"/>';

        $fs = get_file_storage();
        if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $i . '.svg')) {
            $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $i);
        } else {
            $img = $CFG->wwwroot . "/blocks/game/pix/a" . $i . ".svg";
        }

        $border = '';
        if ($i == $avatar) {
            $border = ' border="1" ';
        }

        if ($i <= 56) {
            $outputhtml .= ' <input class="img-fluid" type="image" ' . $border . ' src="' . $img . '" ' . $imgsize . '/> ';
        }

        $outputhtml .= '</form>';
        $outputhtml .= '</td>';
        if ($i % 4 == 0 && $i < 56) {
            $outputhtml .= '</tr><tr>';
            $contlevel++;
        } else if ($i == 56) {
            $outputhtml .= '</tr>';
        }

        if ($contlevel == ($config->level_number + 2)) {
            break;
        }
    }
    $outputhtml .= '</table>';
}
echo $outputhtml;

echo $OUTPUT->footer();
