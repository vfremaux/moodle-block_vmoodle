<?php
require_once($CFG->dirroot.'/config.php');
?>

<script language="Javascript">
    initfields();
    modifyfield();

    function initfields() {
        var b = false;
        var dirroot = "<?php echo(urlencode($CFG->dirroot));?>";
        var element  = document.getElementById("id_vdbprefix");
        var element1 = document.getElementById("id_vdbname");
        var element2 = document.getElementById("id_vdatapath");
        
        dirroot = unescape(dirroot);

        while(b == false) {
            b = true;

            for (i = 0 ; i < dirroot.length; i++) {
                if(dirroot[i] == "+") b = false;
            }

            if(b == false) dirroot  = dirroot.replace("+"," ");
            else b = true;
        }

        var tab1 = dirroot.split("www");
        if (tab1.length >= 1) {
                element2.value = tab1[0]+"moodledata_";
        }

        element.value = "mdl_";
        element1.value = "vmoodle_";
    }

    function modifyfield() {
        var element  = document.getElementById("id_vhostname");
        element.onkeyup = changeHostName; 
        element.onclick = changeHostName;
    }

    function changeHostName(e) {
       if ((window.event) ? event.keyCode : e.keyCode) {
            var dirroothostname = document.getElementById("id_vhostname").value;
            var element  = document.getElementById("id_vdbprefix");
            var element1 = document.getElementById("id_vdbname");
            var element2 = document.getElementById("id_vdatapath");
            var tab = null;

            tab = dirroothostname.split("://");

            if (tab != null && tab.length >= 2) {
                var b = false;
                var dirroot = "<?php echo(urlencode($CFG->dirroot));?>";
                var tab1 = tab[1].split(".");

                if (tab1 != null && tab1.length >= 1) {
                    element.value = "mdl_"+tab1[0];
                    element1.value = "vmoodle_"+tab1[0];
                    element1.value.replace('-', '_'); // do NOT admit hyphens in db names as dangerous for unescaped SQL syntax
                    element1.value.replace(' ', '_'); // do NOT admit spaces in db names as dangerous for unescaped SQL syntax
                }

                dirroot = unescape(dirroot);

                while (b == false) {
                    b = true;
                    for (i = 0 ; i < dirroot.length; i++) {
                        if (dirroot[i] == "+") b = false;
                    }
                    if (b == false) {
                        dirroot  = dirroot.replace("+"," ");
                    } else {
                        b = true;
                    }
                }

                var tab2 = dirroot.split("www");
                if (tab != null && tab1 != null && tab2.length >= 2 && tab1.length >= 1) {
                    element2.value = tab2[0]+"moodledata_" + tab1[0];
                }
            }
        }
    }

</script>