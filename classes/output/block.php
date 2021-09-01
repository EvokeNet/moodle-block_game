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
 * Game block
 *
 * @package    block_game
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_game\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Ranking block renderable class.
 *
 * @package    block_game
 * @copyright  2020 Willian Mano http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block implements renderable, templatable {

    protected $config;
    protected $user;
    protected $courseid;

    /**
     * Block constructor.
     */
    public function __construct($config, $user = null, $courseid = null) {
        global $USER, $COURSE;

        $this->config = $config;
        $this->user = !$user ? $USER : $user;
        $this->courseid = !$courseid ? $COURSE->id : $courseid;
    }

    /**
     * Export the data.
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \coding_exception
     *
     * @throws \dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        // Load Game of user.
        $game = new \stdClass();
        $game->courseid = $this->courseid;
        $game->userid = $this->user->id;

        $game = block_game_load_game($game);

        if (!$game) {
            return [];
        }

        $game->config = $this->config;
        // Get block ranking configuration.
        $cfggame = get_config('block_game');
        if ($this->courseid == SITEID) {
            $game->config = $cfggame;
        }

        $showavatar = !isset($cfggame->use_avatar) || $cfggame->use_avatar == 1;
        $changeavatar = !isset($cfggame->change_avatar_course) || $cfggame->change_avatar_course == 1;
        $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
        $scoreactivities = !isset($game->config->score_activities) || $game->config->score_activities == 1;

        $coursedata = block_game_get_course_activities($this->courseid);
        $activities = $coursedata['activities'];
        $atvscheck = [];
        foreach ($activities as $activity) {
            $atvcheck = 'atv' . $activity['id'];
            if (isset($this->config->$atvcheck) && $this->config->$atvcheck > 0) {
                $atvscheck[] = $activity;
            }
        }

        $scoreok = true;
        // If of course score oly student.
        if ($this->courseid != SITEID && block_game_is_student_user($this->user->id, $this->courseid) == 0) {
            $scoreok = false;
        }

        $game = block_game_process_game($game, $scoreok, $showlevel, $scoreactivities, $atvscheck, $cfggame);

        $userpictureparams = array('size' => 80, 'link' => false, 'alt' => 'User');
        $userpicture = $output->user_picture($this->user, $userpictureparams);
        if ($showavatar) {
            $img = $CFG->wwwroot . '/blocks/game/pix/a' . $game->avatar . '.svg"';
            $fs = get_file_storage();
            if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $game->avatar . '.svg')) {
                $img = block_game_pix_url(1, 'imagens_avatar', 'a' . $game->avatar);
            }
            if ($this->courseid == SITEID || $changeavatar) {
                $userpicture = '<form action="' . $CFG->wwwroot;
                $userpicture .= '/blocks/game/set_avatar_form.php" method="get">';
                $userpicture .= '<input name="id" type="hidden" value="' . $this->courseid . '">';
                $userpicture .= '<input name="avatar" type="hidden" value="' . $game->avatar . '">';
                $userpicture .= ' <input class="img-fluid" type="image" src="' . $img . '" height="140" width="140" /> ';
                $userpicture .= '</form>';
            } else {
                $userpicture = '<img title="' . get_string('label_avatar', 'block_game');
                $userpicture .= '" hspace="5" src="' . $img . '" height="140" width="140"/>';
            }
        }

        return [
            'userpicture' => $userpicture,
            'score' => $game->scorefull,
            'courseid' => $this->courseid,
            'userfirstname' => $this->user->firstname
        ];
    }
}
