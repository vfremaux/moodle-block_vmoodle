<?php
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

        // Checking content cached
        if ($this->content !== NULL)
            return $this->content;
        // Creating new content
        $this->content = new stdClass;
        $this->content->footer = '';
        // Getting context
        $context = context_block::instance($this->instance->id);
           
        // Setting content depending on capabilities
        if (isloggedin()) {
            if (has_capability('local/vmoodle:managevmoodles', $context)) {
                $this->content->footer = '<a href="'.$CFG->wwwroot.'/local/vmoodle/view.php">'.get_string('administrate', 'block_vmoodle').'</a><br/>';
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
    private function _print_status(){
        global $DB;
        // Initializing
        $str = '';
        // Getting virtual moodles
        $vmoodles = $DB->get_records('local_vmoodle');
        // Creating table
        if ($vmoodles) {
            $str = '<table>';
            foreach($vmoodles as $vmoodle)
                $str .= '<tr><td><a href="'.$vmoodle->vhostname.'" target="_blank">'.$vmoodle->shortname.' - '.$vmoodle->name.'</a></td><td>'.vmoodle_print_status($vmoodle, true).'</td></tr>';
            $str .= '</table>';
        }
        // Returning table
        return $str;
    }


}