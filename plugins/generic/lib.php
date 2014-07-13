<?php
/*
 * Created on 22 sept. 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';

function vmoodle_get_remote_config($mnethost, $configkey, $domain = '') {
    global $CFG, $USER, $DB, $OUTPUT;
    
    if (empty($mnethost)) {
        return '';
    }

    if (!isset($USER)) {
        $user = $DB->get_record('user', array('username' => 'guest'));
    } else {
        $user = $USER;
    }

    $userhost = $DB->get_record('mnet_host', array('id' => $user->mnethostid));
    $user->remoteuserhostroot = $userhost->wwwroot;
    $user->remotehostroot = $CFG->wwwroot;

    // get the sessions for each vmoodle that have same ID Number
    $rpcclient = new mnet_xmlrpc_client();
    $rpcclient->set_method('blocks/vmoodle/plugins/generic/rpclib.php/dataexchange_rpc_fetch_config');

    $rpcclient->add_param($user, 'struct');
    $rpcclient->add_param($configkey, 'string');
    $rpcclient->add_param($domain, 'string');

    $mnet_host = new mnet_peer();

    $mnet_host->set_wwwroot($mnethost->wwwroot);
    
    if ($rpcclient->send($mnet_host)) {
        $response = json_decode($rpcclient->response);
        if ($response->status == 200) {
            return $response->value;
        } else {
            if (debugging()) {
                echo $OUTPUT->notification('Remote RPC error '.implode('<br/>', $response->errors));
            }
        }
    } else {
        if (debugging()) {
            echo $OUTPUT->notification('Remote RPC failure '.implode('<br/', $rpcclient->error));
        }
    }        
}

/**
 * Install generic plugin library.
 * @return                    boolean                TRUE if the installation is successfull, FALSE otherwise.
 */
function genericlib_install() {
    // No install operation
    global $DB;
    $result = true;
    // installing Data Exchange

    if ($previous = get_config('dataexchangesafekeys')) {
        $genericconfigs[] = $previous;
    }
    $genericconfigs[] = 'globaladminmessage';
    $genericconfigs[] = 'globaladminmessagecolor';
    set_config('dataexchangesafekeys', implode(',', $genericconfigs));

    return $result;
}

/**
 * Uninstall generic plugin library.
 * @return                    boolean                TRUE if the uninstallation is successfull, FALSE otherwise.
 */
function genericlib_uninstall() {
    global $DB,$OUTPUT;

    set_config('dataexchangesafekeys', '');

    return true;
}