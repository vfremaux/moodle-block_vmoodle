<?php
/**
 * This library provides command for synchronize role capabilites.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

global $STANDARD_PLUGIN_TYPES;

$STANDARD_PLUGIN_TYPES = array(
    'mod' => get_string('activitymodules'),
    'format' => get_string('courseformats'),
    'block' => get_string('blocks'),
    'message' => get_string('messageoutputs', 'message'),
    'auth' => get_string('authentication', 'admin'),
    'enrol' => get_string('enrolments', 'enrol'),
    'editor' => get_string('editors', 'editor'),
    'filter' => get_string('managefilters'),
    'portfolio' => get_string('portfolios', 'portfolio'),
    'scormreport' => get_string('repositories', 'repository'),
    'repository' => get_string('repositories', 'repository'),
    'webservice' => get_string('webservices', 'webservice'),
    'qbehaviour' => get_string('questionbehaviours', 'admin'),
    'qtype' => get_string('questiontypes', 'admin'),
    'plagiarism' => get_string('plagiarism', 'plagiarism'),
    'coursereport' => get_string('coursereports'),
    'report' => get_string('reports'),
    'tool' => get_string('tools', 'admin'),
    'cachestore' => get_string('cachestores', 'cache'),
    'local' => get_string('localplugins'),
    'assignsubmission' => get_string('assignsubmission', 'assign'),
    'assignfeedback' => get_string('assignfeedback', 'assign'),
);