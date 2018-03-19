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
 * @package block_vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/vmoodle/lib.php');

class block_vmoodle_renderer extends plugin_renderer_base {

    public function image_url($image, $subplugin = null) {
        global $CFG, $OUTPUT;

        if (!$subplugin) {
            return $OUTPUT->image_url($image, 'block_vmoodle');
        }

        list($type, $plugin) = explode('_', $subplugin);

        $parts = pathinfo($image);

        $filepath = $CFG->dirroot.'/local/vmoodle/plugins/'.$plugin.'/pix/'.$parts['filename'];

        // We do not support SVG.
        $realpath = block_vmoodle_renderer::image_exists($filepath, false);
        $parts = pathinfo($realpath);

        return $CFG->wwwroot.'/local/vmoodle/plugins/'.$plugin.'/pix/'.$parts['filename'].'.'.$parts['extension'];
    }

    /**
     * Checks if file with any image extension exists.
     *
     * The order to these images was adjusted prior to the release of 2.4
     * At that point the were the following image counts in Moodle core:
     *
     *     - png = 667 in pix dirs (1499 total)
     *     - gif = 385 in pix dirs (606 total)
     *     - jpg = 62  in pix dirs (74 total)
     *     - jpeg = 0  in pix dirs (1 total)
     *
     * There is work in progress to move towards SVG presently hence that has been prioritiesed.
     *
     * @param string $filepath
     * @param bool $svg If set to true SVG images will also be looked for.
     * @return string image name with extension
     */
    private static function image_exists($filepath, $svg = false) {
        if ($svg && file_exists("$filepath.svg")) {
            return "$filepath.svg";
        } else  if (file_exists("$filepath.png")) {
            return "$filepath.png";
        } else if (file_exists("$filepath.gif")) {
            return "$filepath.gif";
        } else  if (file_exists("$filepath.jpg")) {
            return "$filepath.jpg";
        } else  if (file_exists("$filepath.jpeg")) {
            return "$filepath.jpeg";
        } else {
            return false;
        }
    }

    /**
     * Return status for all defined virtual moodles.
     * @return string Status for all defined virtual moodles.
     */
    public function print_status() {
        global $DB;

        $template = new StdClass;

        $vmoodles = $DB->get_records('local_vmoodle');

        if (!empty($vmoodles)) {
            foreach ($vmoodles as $vmoodle) {
                $vmoodletpl = new StdClass;
                $vmoodletpl->url = $vmoodle->vhostname;
                $vmoodletpl->shortname = $vmoodle->shortname;
                $vmoodletpl->fullname = $vmoodle->name;
                $vmoodle->status = vmoodle_print_status($vmoodle, true);
                $template->vmoodles[] = $vmoodletpl;
            }
        }

        return $this->output->render_from_template('block_vmoodle/vmoodles', $template);
    }
}
