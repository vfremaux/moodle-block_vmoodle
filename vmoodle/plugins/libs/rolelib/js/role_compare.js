/* Coordinates of capability to synchronize */

var vmrolelib_row = -1;
var vmrolelib_col = -1;

/*
 * Save capability to synchronize.
 * @param	col		integer		The column of capability
 * @param	row		integer		The row of capability.
 * @param	name	string		The name of capability.
 */
function setCapability(col, row, name, host) {
	// Get HTML elements
	var capability = document.getElementById('capability');
	var src_pltfrm = document.getElementById('source_platform');
	var cap_cell = document.getElementById('cap_0_'+row);
	var cell = document.getElementById('cap_'+col+'_'+row);
	var i = 1;
	var pltfrm_checkbox;
	
	if (capability.value == '') {
		// Recording cell
		vmrolelib_col = col;
		vmrolelib_row = row;
		// Adding decoration
		cap_cell && addClass(cap_cell, 'choosenheading');
		cell && addClass(cell, 'choosencell');
		// Saving capability
		capability.value = name;
		// Saving source platform
		src_pltfrm.value = host;
		// Enabling sync platforms
		i = 1;
		while(pltfrm_checkbox = document.getElementById('platform_'+i)) {
			if (i != col)
				pltfrm_checkbox.disabled = '';
			i++;
		}
	} else if (vmrolelib_col == col && vmrolelib_row == row) {
		// Initializing cell coordinates
		vmrolelib_col = -1;
		vmrolelib_row = -1;
		// Removing decoration
		cap_cell && removeClass(cap_cell, 'choosenheading');
		cell && removeClass(cell, 'choosencell');
		// Removing capability
		capability.value = '';
		// Removing source platform
		src_pltfrm.value = '';
		// Disabling platforms
		while(pltfrm_checkbox = document.getElementById('platform_'+i)) {
			pltfrm_checkbox.checked = '';
			pltfrm_checkbox.disabled = 'disabled';
			i++;
		}
	}
}

/*
 * Validate form.
 * @return			boolean		TRUE if form is valid, FALSE otherwise.
 */
function validate_syncrole() {
	// Initializing variables
	var i = 1;
	var sync = false;
	// Getting HTML elements
	var capability = document.getElementById('capability');
	var src_pltfrm = document.getElementById('source_platform');
	var validation_message = document.getElementById('rolecompare_validation_message');
	var sync_pltfrm;
	// Checking if capability
	if (capability.value == '') {
		validation_message.innerHTML = vmoodle_rolelib_nocapability;
		return false;
	}
	// Checking source platform
	if (src_pltfrm.value == '') {
		validation_message.innerHTML = vmoodle_rolelib_nosrcpltfrm;
		return false;
	}
	// Checking sync platforms
	while(sync_pltfrm = document.getElementById('platform_'+i)) {
		if (sync_pltfrm.checked) {
			sync = true;
			break;
		}
		i++;
	}
	if (!sync) {
		validation_message.innerHTML = vmoodle_rolelib_nosyncpltfrm;
		return false;
	} else
		validation_message.innerHTML = '';
	return confirm(vmoodle_rolelib_confirmrolecapabilitysync);
}

/*
 * Add highlight on header and first column of current cell.
 * @param	col		integer		The column of current cell.
 * @param	row		integer		The row of current cell.
 */
function cellOver(col, row) {
	// Getting HTML elements
	var cap_cell = document.getElementById('cap_0_'+row);
	var pltfrm_cell = document.getElementById('cap_'+col);
	var cell = document.getElementById('cap_'+col+'_'+row);
//	alert(cell.id);
	// Adding CSS classes
	cap_cell && addClass(cap_cell, 'headinghighlight');
	pltfrm_cell && addClass(pltfrm_cell, 'headinghighlight');
	cell && addClass(cell, 'cellhighlight');
}

/*
 * Remove highlight on header and first column of current cell.
 * @param	col		integer		The column of current cell.
 * @param	row		integer		The row of current cell.
 */
function cellOut(col, row) {
	// Getting HTML elements
	var cap_cell = document.getElementById('cap_0_'+row);
	var pltfrm_cell = document.getElementById('cap_'+col);
	var cell = document.getElementById('cap_'+col+'_'+row);
	// Removing CSS classes
	cap_cell && removeClass(cap_cell, 'headinghighlight');
	pltfrm_cell && removeClass(pltfrm_cell, 'headinghighlight');
	cell && removeClass(cell, 'cellhighlight');
}

/*
 * Add CSS class on DOM element.
 * @param	el		DOM element		A DOM element.
 * @param	clss	string			A CSS class name.
 */
function addClass(el, clss) {
	el.className+= el.className ? ' '+clss : clss;
}

/*
 * Remove CSS class from DOM element.
 * @param	el		DOM element		A DOM element.
 * @param	clss	string			A CSS class name.
 */
function removeClass(el, clss) {
	clss = el.className.match(' '+clss) ? ' '+clss : clss;
	el.className = el.className.replace(clss, '');
}