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
 * Declare Vmoodle block.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @version Moodle 2.2
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/vmoodle/lib.php');

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
     * @return                boolean        True if the block have a configuration file, false otherwise.
     */
    public function has_config() {
        return false;
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
        global $CFG, $PAGE;

        // Checking content cached.
        if ($this->content !== null) {
            return $this->content;
        }

        // Creating new content.
        $this->content = new stdClass;
        $this->content->footer = '';

        // Getting context.
        $context = context_block::instance($this->instance->id);
        $renderer = $PAGE->get_renderer('block_vmoodle');

        // Setting content depending on capabilities
        if (isloggedin()) {
            if (has_capability('local/vmoodle:managevmoodles', $context)) {
                $viewurl = new moodle_url('/local/vmoodle/view.php');
                $this->content->footer = '<a href="'.$viewurl.'">'.get_string('administrate', 'block_vmoodle').'</a><br/>';
                $this->content->text = $renderer->status();
            } else {
                $this->content->text = get_string('notallowed', 'block_vmoodle');
            }
        }
        // Returning content
        return $this->content;
    }
}