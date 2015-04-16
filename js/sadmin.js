/*
    elementToggleHide (element, elementFinder)

    If elementFinder is not provided, toggles the "hidden" class for the specified element.
    If elementFinder is provided, then the "hidden" class will be toggled for the object
    returned by the function call elementFinder(element).

    If persistent == true, also sets a cookie for this.
*/
function elementToggleHide(el, persistent, elementFinder, strShow, strHide) {

    if (!elementFinder) {
        var obj = el;  //el:container
        el = document.getElementById('togglehide_'+obj.id);
    } else {
        var obj = elementFinder(el);  //el:button.
    }

    obj = $(el).parent().parent().parent().find('.content');
    obj.className=$(obj).attr('class');

    if (obj.className.indexOf('hidden') == -1) {
        obj.className += ' hidden';
        if (el.src) {
            el.src = el.src.replace('switch_minus', 'switch_plus');
            el.alt = strShow;
            el.title = strShow;
        }
        $(obj).attr('class', obj.className);
        var shown = 0;
    } else {
        obj.className = obj.className.replace(new RegExp(' ?hidden'), '');
        if (el.src) {
            el.src = el.src.replace('switch_plus', 'switch_minus');
            el.alt = strHide;
            el.title = strHide;
        }
        $(obj).attr('class', obj.className);
        var shown = 1;
    }
 
    if(persistent == true) {
        new cookie('hide:' + obj.id, 1, (shown ? -1 : 356), '/').set();
    }
}

function filtercapabilitytable(filterinput) {
    $('.capabilityrow').css('display', 'table-row');
    if (filterinput.value != '') {
        $('.capabilityrow').css('display', 'none');
        $('.capabilityrow[id*=\''+filterinput.value+'\']').css('display', 'table-row');
    }
}