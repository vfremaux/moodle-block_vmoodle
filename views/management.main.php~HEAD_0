<?php
/**
 * Redirection to a certain page of Vmoodle management.
 *
 * @package block-vmoodle
 * @category blocks
 * @author Moheissen Fabien (fabien.moheissen@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Declaring the page parameter.
$page = optional_param('page', 'list', PARAM_TEXT);

// Templates test, for ADD page.
$templates = vmoodle_get_available_templates();
if($page == 'add' && empty($templates)) {
	$page = 'list';
}

// Selecting the page.
switch($page) {
	case 'list': {
		$result = include 'management.list.php';
	}
	break;
	case 'add' : {
		$result = include 'management.add.php';
	}
	break;
	case 'edit' : {
		$result = include 'management.edit.php';
	}
	break;
	default: {
		$result = -1;
	}
}

// If an error happens.
if ($result == -1) {
	print_footer();
	exit(0);
}