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
 * @package blocks_vmoodle
 * @category
 * @author Valery Fremaux <valery.fremaux@gmail.com>, <valery@edunao.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2014 onwards Microsoft Open Technologies, Inc. (http://msopentech.com/)
 */
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/vmoodle/tools/customscriptgenerator_form.php');
require_once($CFG->dirroot.'/blocks/vmoodle/tools/lib.php');

$url = new moodle_url('/blocks/vmoodle/tools/generatecustomscripts.php');
$context = context_system::instance();
$PAGE->set_context($context);

require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_heading(get_string('scriptgenerator', 'block_vmoodle'));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$mform = new CustomScriptGenerator_Form();

echo $OUTPUT->header();

$data = new StdClass();
$data->templatetext = '';
if ($data = $mform->get_data()) {
    $vhosts = $DB->get_records('block_vmoodle', array('enabled' => 1));

    $script = '';

    if (!empty($vhosts)) {
        foreach ($vhosts as $vh) {
            $scriptlet = str_replace('%WWWROOT%', $vh->vhostname, $data->templatetext);
            $scriptlet = str_replace('%DBHOST%', $vh->vdbhost, $scriptlet);
            $scriptlet = str_replace('%DBNAME%', $vh->vdbname, $scriptlet);
            $scriptlet = str_replace('%DBUSER%', $vh->vdblogin, $scriptlet);
            $scriptlet = str_replace('%DBPASS%', $vh->vdbpass, $scriptlet);
            $scriptlet = str_replace('%DATAROOT%', $vh->vdatapath, $scriptlet);

            switch ($data->commentformat) {
                case 'shell' :
                    $script .= '# Process script for '.$vh->name."\n#------------\n";
                    break;
                case 'sql' :
                    $script .= '// Process script for '.$vh->name."\n//\n";
                    break;
                default :
                    $script .= '<!-- Process script for '.$vh->name." -->\n";
                    break;
            }
            $script .= $scriptlet."\n\n";
        }
    }

    echo $OUTPUT->heading(get_string('generatedscript', 'block_vmoodle'));
    echo '<pre>';
    echo $script;
    echo '</pre>';
}

$mform->set_data($data);
$mform->display();

echo $OUTPUT->footer();