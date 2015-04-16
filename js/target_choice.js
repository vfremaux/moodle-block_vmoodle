/*
 * Active filters
 */
var pfilters = new Array();
var pfilters_counter = 0;

/**
 * Define a filter platform
 */
function Pfilter(type, value) {
    // Checking parameters.
    if (type != 'contains' && type != 'notcontains' && type != 'regexp' || value == '')
        return;
    
    // Defining members.
    this.type = type;
    this.value = value;
    switch (this.type) {
    case 'contains':
        this.filter = new RegExp(this.value, 'i');
        this.negate = false;
        break;
    case 'notcontains':
        this.filter = new RegExp(this.value, 'i');
        this.negate = true;
        break;
    case 'regexp':
        // Getting regexp
        if (value[0] != '/') {
            alert(vmoodle_badregexp);
            return;
        }
        var value_cpy = value;
        value = value.substring(1, value.length);
        var index = value.lastIndexOf('/');
        if (index == -1) {
            alert(vmoodle_badregexp);
            return;
        }
        value = value.substring(0, index);
        // Getting modifiers
        var modifiers = value_cpy.substring(index+2, value_cpy.length);
        if ((index = modifiers.indexOf('n')) != -1) {
            this.negate = true;
            modifiers = modifiers.substring(0, index)+modifiers.substring(index+1, modifiers.length);
        } else {
            this.negate = false;
        }
        // Creating filter
        try {
            this.filter = new RegExp(value, modifiers);
        }
        catch (e) {
            alert(vmoodle_badregexp)
            return;
        }
        break;
    }
    // Validating construction attributing an id.
    this.id = pfilters_counter++;
    
    // Defining methods
    /*
     * Draw and insert the HTML of Pfilter.
     */
    this.draw = function() {
        // Getting HTMLelement
        var el_form = document.getElementById('pfilterform');
        // Creating HTMLelemnt
        var el_pfilter = document.createElement('div');
        
        el_pfilter.id = 'pfilter'+this.id;
        el_pfilter.innerHTML = '<table class="pfilter">'+
                                    '<tr>'+
                                        '<td class="pfilter_type">'+eval('vmoodle_'+this.type)+'</td>'+
                                        '<td class="pfilter_value">'+this.value+'</td>'+
                                        '<td class="pfilter_action">'+
                                            '<input type="button" value="'+vmoodle_delete+'" onclick="remove_pfilter('+this.id+'); return false;"/>'+
                                        '</td>'+
                                    '</tr>'+
                                '</table>';
        el_form.appendChild(el_pfilter);
    };
    
    /*
     * Clear the HTML on Pfilter.
     */
    this.clear = function() {
        // Getting HTMLelements
        var el_form = document.getElementById('pfilterform');
        var el_pfilter = document.getElementById('pfilter'+this.id);
        // Removing child
        el_form.removeChild(el_pfilter);
    };
    
    /*
     * Apply pfilter on the available and selected platforms.
     */
    this.apply = function() {
        // Getting HTMLelement
        var el_achoices = document.getElementById('id_aplatforms');
        var el_schoices = document.getElementById('id_splatforms');
        // Applying filter
        apply_filter(this, el_achoices);
        apply_filter(this, el_schoices);
    };
    
    /*
     * Test the filter on a string.
     * @param    value        string            String to test.
     */
    this.test = function(value) {
        var test = this.filter.test(value);
        if (this.negate)
            return !test;
        else
            return test;
    };
}

/*
 * Add a platform filter.
 */
function add_filter() {
    // Retrieve values from the form
    var type = document.getElementById('id_filterparam_filtertype').value;
    var value = document.getElementById('id_filterparam_filtervalue').value;
    // Create a new filter
    var pfilter = new Pfilter(type, value);
    if (pfilter.id == null)
        return;
    // Adding to the form
    pfilter.draw();
    // Filter choices
    pfilter.apply();
    // Add to active filter
    pfilters[pfilter.id] = pfilter;
}

/*
 * Remove a platform filter.
 * @param integer $id Id of filter to remove.
 */
function remove_pfilter(id) {
    // Checking parameter.
    if (pfilters[id] == null)
        return;

    // Removing filter.
    pfilters[id].clear();
    delete pfilters[id];

    // Repopulating choices.
    populate_achoices();

    // Applying active filters.
    for (index in pfilters){
        pfilters[index].apply();
    }
}

/*
 * Apply a filter on select values.
 * @param string $filter Filter to apply.
 * @param HTMLElement $select Values to filter.
 */
function apply_filter(filter, select) {
    var option_text;
    for (var i=0; i<select.length; i++) {
        option_text = select.item(i).text;

        // Checking the none value.
        if (option_text == vmoodle_none)
            break;

        // Verifying filter.
        if (!filter.test(select.item(i).text)) {
            select.remove(i);
            i--;
        }
    }

    // Checking if remains value.
    if (select.length == 0) {
        // Adding none value.
        var option_none = document.createElement('option');
        option_none.value = 0;
        option_none.text = vmoodle_none;
        select.appendChild(option_none);
    }
}

/*
 * Populate the available platforms.
 */
function populate_achoices() {
    // Getting HTMLelement.
    var aselect = document.getElementById('id_aplatforms');
    var sselect = document.getElementById('id_splatforms');

    // Getting values.
    var values = eval('('+document.getElementsByName('achoices')[0].value+')');

    // Declaring variables.
    var option;
    var selected;

    // Deleting all values.
    while (aselect.length != 0)
        aselect.remove(0);

    // Adding values.
    for(index in values) {

        // Checking if option isn't selected.
        selected = false;
        for (var i = 0 ; i < sselect.length; i++) {
            if (sselect.item(i).text == values[index] && sselect.item(i).value == index) {
                selected = true;
                break;
            }
        }
        if (selected) {
            continue;
        }

        // Adding value.
        option = document.createElement('option');
        option.value = index;
        option.text = values[index];
        aselect.appendChild(option);
    }
}

/*
 * Add selected available platform(s) to selected platforms.
 */
function select_platforms() {
    // Getting HTMLelement.
    var aselect = document.getElementById('id_aplatforms');
    var sselect = document.getElementById('id_splatforms');

    // Moving option to the selected select.
    move_selected_options(aselect, sselect);
}

/*
 * Remove selected selected platform(s) to available platforms.
 */
function unselect_platforms() {
    // Getting HTMLelement.
    var aselect = document.getElementById('id_aplatforms');
    var sselect = document.getElementById('id_splatforms');

    // Moving option to the selected select.
    move_selected_options(sselect, aselect);
}

/*
 * Add all available platforms to selected platforms.
 */
function select_all_platforms() {
    // Getting HTMLelement.
    var select = document.getElementById('id_aplatforms');

    // Checking if none value.
    if (select.length == 0 && select.item(0).value == 0 && select.item(0).value == vmoodle_none)
        return;

    // Selecting all options.
    for (var i = 0; i < select.length; i++)
        select.item(i).selected = true;

    // Moving options.
    select_platforms();
}

/*
 * Remove all selected platforms to available platforms.
 */
function unselect_all_platforms() {
    // Getting HTMLelement.
    var select = document.getElementById('id_splatforms');

    // Checking if none value.
    if (select.length == 0 && select.item(0).value == 0 && select.item(0).value == vmoodle_none)
        return;

    // Selecting all options.
    for (var i = 0 ; i < select.length ; i++)
        select.item(i).selected = true;

    // Moving options.
    unselect_platforms();
}

/*
 * Move selected options of from select to to select.
 */
function move_selected_options(from, to) {
    // Getting HTMLelement.
    var aselect = document.getElementById('id_aplatforms');
    var sselect = document.getElementById('id_splatforms');
    var option;

    // Moving option to the selected select.
    for (var i = 0 ; i < from.length ; i++) {
        if (from.item(i).selected) {
            // Checking if none value.
            if (from.item(i).value == 0 && from.item(i).text == vmoodle_none)
                continue;

            // Checking if to is none value.
            if (to.length == 1 && to.item(0).value == 0 && to.item(0).text == vmoodle_none)
                to.remove(0);
            // Adding option to selected.
            to.appendChild(from.item(i));

            // Updating counter.
            i--;
        }
    }

    // Sort options.
    sort_select(to);

    // Checking if remains value.
    if (from.length == 0) {

        // Adding none value.
        option = document.createElement('option');
        option.value = 0;
        option.text = vmoodle_none;
        from.appendChild(option);
    }
}

/*
 * Sort option of a select.
 */
function sort_select(select) {
    // Cloning options to sort
    var options = new Array();

    // Getting data.
    for (var i = 0; i < select.length ; i++) {
        options[i] = new Array();
        options[i][0] = select.item(i).text;
        options[i][1] = select.item(i).value;
        options[i][2] = select.item(i).selected;
    }

    // Sorting data.
    options = options.sort();

    // Setting sorted data.
    for (var i = 0 ; i < select.length ; i++) {
        select.item(i).text = options[i][0];
        select.item(i).value = options[i][1];
        select.item(i).selected = options[i][2];
    }
}

/*
 * Submit the target form, ensuring all selected items ae really selected.
 */
function submit_target_form() {
    // Getting HTMLElement.
    var select = document.getElementById('id_splatforms');

    // Selecting all elements.
    for (var i = 0; i < select.length ; i++)
        select.item(i).selected = true;

    // Validating form.
    return true;
}