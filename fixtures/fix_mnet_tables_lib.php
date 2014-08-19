<?php

function fix_mnet_tables_fixture() {
    global $DB;

    $RPCS = array();
    $SERVICES = array();
    $BADRPCS = array();
    $BADSERVICES = array();

    echo "<pre>\n";

    // Preclean all bindings that are not mapped to real records.
    mtrace('Fixing unattached bindings');
    $DB->execute(" DELETE FROM {mnet_service2rpc} WHERE rpcid NOT IN (SELECT id FROM {mnet_rpc}) ");
    $DB->execute(" DELETE FROM {mnet_service2rpc} WHERE serviceid NOT IN (SELECT id FROM {mnet_service}) ");
    $DB->execute(" DELETE FROM {mnet_host2service} WHERE hostid NOT IN (SELECT id FROM {mnet_host}) ");
    $DB->execute(" DELETE FROM {mnet_host2service} WHERE serviceid NOT IN (SELECT id FROM {mnet_service}) ");

    if ($allrpcs = $DB->get_records('mnet_rpc', array(), 'id')) {

        // First destroy any surnumerous rpc (higher id, same path).
        mtrace('Cleaning RPC records');
        $g = 0;
        $b = 0;
        foreach ($allrpcs as $rpc) {
            if (array_key_exists($rpc->xmlrpcpath, $RPCS)) {
                // Register and remove
                $BADRPCS[$rpc->id] = $RPCS[$rpc->xmlrpcpath]->id;
                $DB->delete_records('mnet_rpc', array('id' => $rpc->id));
                $b++;
            } else {
                // Record xmlRPC and indexes.
                $RPCS[$rpc->xmlrpcpath] = $rpc;
                $RPCIDS[$rpc->id] = $rpc->xmlrpcpath;
                $g++;
            }
        }
        mtrace("$b bad / $g good records found.");

        // Second destroy any surnumerous service (higher id, same name) and keep ids.
        mtrace('Cleaning Service records');
        $g = 0;
        $b = 0;
        $allservices = $DB->get_records('mnet_service', array(), 'id');
        foreach ($allservices as $s) {
            if (array_key_exists($s->name, $SERVICES)) {
                // Register and remove.
                $BADSERVICES[$s->id] = $SERVICES[$s->name]->id;
                $DB->delete_records('mnet_service', array('id' => $s->id));
                $b++;
            } else {
                // Record service and indexes.
                $SERVICES[$s->name] = $s;
                $SERVICEIDS[$s->id] = $s->name;
                $g++;
            }
        }
        mtrace("$b bad / $g good records found.");

        // Now control if some bad services were host bound.
        mtrace('Checking RPC to Service bindings');
        foreach ($BADRPCS as $badid => $goodid) {
            if ($bindings = $DB->get_records('mnet_service2rpc', array('rpcid' => $badid))) {
                foreach ($bindings as $b) {
                    if (array_key_exists($b->serviceid, $BADSERVICES)) {
                        // Bad rpc is registered in bad service. Just check good ones are bind them correctly if missing.
                        $goodservice = $SERVICEIDS[$BADSERVICES[$b->serviceid]];
                        if (!$goodbind = $DB->get_record('mnet_service2rpc', array('rpcid' => $goodid, 'serviceid' => $goodservice))) {
                            $binding = new StdClass();
                            $binding->rpcid = $goodrpc;
                            $binding->serviceid = $goodservice;
                            $DB->insert_record('mnet_service2rpc', $binding);
                        }
                    }
                }
            }
        }

        // Finally clean all bindings that are surnumerous.
        mtrace('Back cleaning');
        $DB->execute(" DELETE FROM {mnet_service2rpc} WHERE rpcid NOT IN (SELECT id FROM {mnet_rpc}) ");
        $DB->execute(" DELETE FROM {mnet_service2rpc} WHERE serviceid NOT IN (SELECT id FROM {mnet_service}) ");

        // Now eliminate all bad host to service mapping.

        mtrace('Checking host bindings');
        $b = 0;
        $g = 0;
        if ($hostbindings = $DB->get_records('mnet_host2service')) {
            mtrace("fixing host bindings");
            foreach($hostbindings as $hb) {
                if (array_key_exists($hb->serviceid, $SERVICEIDS)) {
                    // This is a good case. Good serviceid.
                    $g++;
                    continue;
                }
                if (array_key_exists($hb->serviceid, $BADSERVICES)) {
                    $goodservice = $SERVICEIDS[$BADSERVICES[$hb->serviceid]];
                    if (!$goodbind = $DB->get_record('mnet_host2service', array('hostid' => $hb->hostid, 'serviceid' => $goodservice))) {
                        $binding = new StdClass();
                        $binding->hostid = $hb->hostid;
                        $binding->serviceid = $goodservice;
                        $DB->insert_record('mnet_service2rpc', $binding);
                        $b++;
                    } else {
                        $g++;
                    }
                }
            }
        }
        mtrace("$b bad fixed / $g good host bindings found.");
        mtrace('Finished');

        echo '</pre>';
    }
}