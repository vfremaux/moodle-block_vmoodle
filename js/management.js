function vmoodle_manager_confirm(selectobj, confirmtext) {
    if (selectobj.options[selectobj.selectedIndex].value == 'deleteinstances') {
        if (confirm(confirmtext)) {
            document.forms.vmoodlesform.submit();
            return true;
        }
    } else {
        document.forms.vmoodlesform.submit();
        return true;
    }

    return false;
}