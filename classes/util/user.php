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
 * Game block utility user class.
 *
 * @package   block_game
 * @copyright 2021 Willian Mano http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_game\util;

defined('MOODLE_INTERNAL') || die();

class user {

    protected $user;
    protected $output;

    public function __construct($user, $output) {
        $this->user = $user;
        $this->output = $output;
    }

    public function get_user_avatar_or_image() {
        global $DB, $CFG, $PAGE;

        $gameconfig = get_config('block_game');

        if (!isset($gameconfig->use_avatar) || !$gameconfig->use_avatar) {
            $userpicture = new \user_picture($this->user);
            $userpicture->size = 1;

            return $userpicture->get_url($PAGE);
        }

        $sql = 'SELECT * FROM {block_game} WHERE userid = :userid LIMIT 1';
        $usergameentry = $DB->get_record_sql($sql, ['userid' => $this->user->id]);

        if (!$usergameentry) {
            $userpicture = new \user_picture($this->user);
            $userpicture->size = 1;

            return $userpicture->get_url($PAGE);
        }

        $fs = get_file_storage();

        if ($fs->file_exists(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $usergameentry->avatar . '.svg')) {
            return strval(\moodle_url::make_pluginfile_url(1, 'block_game', 'imagens_avatar', 0, '/', 'a' . $usergameentry->avatar));
        }

        return $CFG->wwwroot . '/blocks/game/pix/a' . $usergameentry->avatar . '.svg';
    }
}
