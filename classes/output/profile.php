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

use block_game\util\user;
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
class profile implements renderable, templatable {
    protected $user;
    protected $courseid;

    /**
     * Block constructor.
     */
    public function __construct($user = null, $courseid = null) {
        global $USER, $COURSE;

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
        $game = new \stdClass();
        $game->courseid = $this->courseid;
        $game->userid = $this->user->id;

        $game = block_game_load_game($game);
        $cfggame = get_config('block_game');

        $game->config = $cfggame;
        if ($game->courseid != SITEID) {
            $game->config = block_game_get_config_block($game->courseid);
        }

        $showlevel = !isset($game->config->show_level) || $game->config->show_level == 1;
        $scoreactivities = !isset($game->config->score_activities) || $game->config->score_activities == 1;

        $coursedata = block_game_get_course_activities($this->courseid);
        $activities = $coursedata['activities'];
        $atvscheck = [];
        foreach ($activities as $activity) {
            $atvcheck = 'atv' . $activity['id'];
            if (isset($game->config->$atvcheck) && $game->config->$atvcheck > 0) {
                $atvscheck[] = $activity;
            }
        }

        $scoreok = true;
        // If of course score oly student.
        if ($this->courseid != SITEID && block_game_is_student_user($this->user->id, $this->courseid) == 0) {
            $scoreok = false;
        }

        $game = block_game_process_game($game, $scoreok, $showlevel, $scoreactivities, $atvscheck, $cfggame);

        $hasbadges = block_game_get_course_badges($this->courseid);
        $badges = [];
        if ($hasbadges) {
            $badges = block_game_get_course_badges_with_user_award($this->user->id, $this->courseid);
        }

        $usergameutil = new user($this->user, $output);

        return [
            'userpicture' => $usergameutil->get_user_avatar_or_image(),
            'score' => $game->scorefull,
            'courseid' => $this->courseid,
            'userfirstname' => $this->user->firstname,
            'hasbadges' => $hasbadges,
            'badges' => $badges
        ];

    }
}