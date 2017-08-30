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

defined('MOODLE_INTERNAL') || die();

function vmoodle_get_jump_link_url($vmoodleid, $wantsurl = '') {
    global $DB, $CFG;

    $vmoodle = $DB->get_record('block_vmoodle', array('id' => $vmoodleid));
    if (($vmoodle->mnet > -1) && ($vmoodle->vhostname != $CFG->wwwroot)) {
        $url = new moodle_url('/auth/mnet/jump.php', array('hostwwwroot' => $vmoodle->vhostname));
        if (!empty($wantsurl)) {
            $url->param('wantsurl', $wantsurl);
        }
    } else {
        // If not mnet.
        $url = $vmoodle->vhostname;
        if (!empty($wantsurl)) {
            $url.= '?wantsurl='.$wantsurl;
        }
    }
    return $url;
}
