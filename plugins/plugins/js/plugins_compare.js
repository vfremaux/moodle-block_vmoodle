/* Coordinates of capability to synchronize */

var vmpluginlib_row = -1;
var vmpluginlib_col = -1;

/*
 * Save capability to synchronize.
 * @param	col		integer		The column of capability
 * @param	row		integer		The row of capability.
 * @param	name	string		The name of capability.
 */
function setPlugin(col, row, name, host) {
	// Get HTML elements
	var plugin = document.getElementById('plugin');
	var src_pltfrm = document.getElementById('source_platform');
	var plug_cell = document.getElementById('plug_0_'+row);
	var cell = document.getElementById('plug_'+col+'_'+row);
	var i = 1;
	var pltfrm_checkbox;
	
	if (plugin.value == '') {
		// Recording cell
		vmpluginlib_col = col;
		vmpluginlib_row = row;
		// Adding decoration
		plug_cell && addClass(plug_cell, 'choosenheading');
		cell && addClass(cell, 'choosencell');
		// Saving plugin choice
		plugin.value = name;
		// Saving source platform
		src_pltfrm.value = host;
		// Enabling sync platforms
		i = 1;
		while(pltfrm_checkbox = document.getElementById('platform_'+i)) {
			if (i != col)
				pltfrm_checkbox.disabled = '';
			i++;
		}
	} else if (vmpluginlib_col == col && vmpluginlib_row == row) {

		// Initializing cell coordinates
		vmpluginlib_col = -1;
		vmpluginlib_row = -1;

		// Removing decoration
		plug_cell && removeClass(plug_cell, 'choosenheading');
		cell && removeClass(cell, 'choosencell');

		// Removing capability
		plugin.value = '';

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
function validate_syncplugins() {

	// Initializing variables
	var i = 1;
	var sync = false;

	// Getting HTML elements
	var plugin = document.getElementById('plugin');
	var src_pltfrm = document.getElementById('source_platform');
	var validation_message = document.getElementById('plugincompare_validation_message');
	var sync_pltfrm;

	// Checking if plugin
	if (plugin.value == '') {
		validation_message.innerHTML = vmoodle_pluginlib_notinstalled;
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
		validation_message.innerHTML = vmoodle_pluginlib_nosyncpltfrm;
		return false;
	} else
		validation_message.innerHTML = '';
	return confirm(vmoodle_rolelib_confirmpluginvisibilitysync);
}

/*
 * Add highlight on header and first column of current cell.
 * @param	col		integer		The column of current cell.
 * @param	row		integer		The row of current cell.
 */
function cellOver(col, row) {
	// Getting HTML elements
	var plug_cell = document.getElementById('plug_0_'+row);
	var pltfrm_cell = document.getElementById('plug_'+col);
	var cell = document.getElementById('plug_'+col+'_'+row);
//	alert(cell.id);
	// Adding CSS classes
	plug_cell && addClass(plug_cell, 'headinghighlight');
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
	alert(col+ ' '+row);
	var plug_cell = document.getElementById('plug_0_'+row);
	var pltfrm_cell = document.getElementById('plug_'+col);
	var cell = document.getElementById('plug_'+col+'_'+row);
	// Removing CSS classes
	plug_cell && removeClass(plug_cell, 'headinghighlight');
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