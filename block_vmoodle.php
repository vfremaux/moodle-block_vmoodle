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
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/vmoodle/lib.php');

if (get_config('block_vmoodle_late_install')) {
    set_config('block_vmoodle_late_install', 0);
    require_once $CFG->dirroot.'/blocks/vmoodle/db/install.php';
    xmldb_block_vmoodle_late_install();
}

/**
 * Vmoodle block displays virtual platforms and link to the settings.
 */
class block_vmoodle extends block_base {
    /**
     * Initialize block.
     */
    public function init() {
        // Setting block parameters
        $this->title = get_string('blockname', 'block_vmoodle');
    }
    /**
     * Define the block preferred width.
     * @return int The block prefered width.
     */
    public function preferred_width() {
        return 200;
    }

    /**
     * Check if the block have a configuration file.
     * @return boolean True if the block have a configuration file, false otherwise.
     */
    public function has_config() {
        return true;
    }

    /**
     * Define the applicable formats to the block.
     * @return array The aplicable formats to the block.
     */
    public function applicable_formats() {
        return array('site' => true, 'learning' => false, 'admin' => true);
    }

    /**
     * Return the block content.
     * @uses $CFG
     * @return string The block content.
     */
    public function get_content() {
        global $CFG;
        // Checking content cached.
        if ($this->content !== NULL)
            return $this->content;
        // Creating new content.
        $this->content = new stdClass;
        $this->content->footer = '';

        $context = context_block::instance($this->instance->id);

        // Setting content depending on capabilities.
        if (isloggedin()) {
            if (has_capability('block/vmoodle:managevmoodles', $context)) {
                $this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/vmoodle/view.php">'.get_string('administrate', 'block_vmoodle').'</a><br/>';
                $this->content->text = $this->_print_status();
            } else {
                $this->content->text = get_string('notallowed', 'block_vmoodle');
            }
        }
        // Returning content
        return $this->content;
    }
    /**
     * Return status for all defined virtual moodles.
     * @return string Status for all defined virtual moodles.
     */
    private function _print_status() {
        global $DB;
        // Initializing.
        $str = '';
        // Getting virtual moodles.
        $vmoodles = $DB->get_records('block_vmoodle');
        // Creating table.
        if ($vmoodles) {
            $str = '<table>';
            foreach ($vmoodles as $vmoodle) {
                $str .= '<tr><td><a href="'.$vmoodle->vhostname.'" target="_blank">'.$vmoodle->shortname.' - '.$vmoodle->name.'</a></td><td>'.vmoodle_print_status($vmoodle, true).'</td></tr>';
            }
            $str .= '</table>';
        }
        // Returning table.
        return $str;
    }

    /**
     * Update subplugins.
     * @param string $return The URL to prompt to the user to continue.
     */
    public function update_subplugins($verbose) {
        global $DB;

        upgrade_plugins('vmoodlelib', '', '', $verbose);

        // Fix wrongly twicked rpc paths.
        if ($rpc_shifted_defines = $DB->get_records_select('mnet_rpc', " xmlrpcpath LIKE 'vmoodleadminset%' ", array())) {
            foreach ($rpc_shifted_defines as $rpc) {
                $rpc->xmlrpcpath = str_replace('vmoodleadminset', 'blocks/vmoodle/plugins');
                $DB->update_record('mnet_rpc', $rpc);
            }
        }
    }

    public function cron() {
        global $CFG, $MNET;

        include($CFG->dirroot.'/blocks/vmoodle/mnetcron.php');
        return true;
    }
}