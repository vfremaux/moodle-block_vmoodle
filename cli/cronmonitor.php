<?php

define('CLI_SCRIPT', true);
global $CLI_VMOODLE_PRECHECK;

$CLI_VMOODLE_PRECHECK = true; // force first config to be minimal
require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
require_once($CFG->dirroot.'/lib/clilib.php'); // CLI only functions

// Ensure errors are well explained.
$CFG->debug = 31676;

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false,
                                                     'file'=>false,
                                                     'host'=>false,
                                                     'mode'=>false),
                                               array('h' => 'help',
                                                     'm' => 'mode',
                                                     'H' => 'host',
                                                     'f' => 'file')
                                               );

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Monitors the platform cron and checks ts sanity. Mails an alert if blocked or erroneous.

Options:
-h, --help            Print out this help
-m, --mode            Mode (web or cli)
-H, --host            The host wwwroot
-f, --file            If file is given, will check the cron result in the given file. If not, the monitor will fire
a cron execution to get the cron result.
-u, --user

Example in crontab :
0 */4 * * * /usr/bin/php blocks/vmoodle/cli/cronmonitor.php
";

    echo $help;
    die;
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
mtrace('Config check : playing for '.$CFG->wwwroot);

if (empty($options['mode'])) {
    $options['mode'] = 'cli';
}

if (!empty($options['file'])) {
    if (!file_exists($options['file'])) {
        die('Error reading output file '.$options['file']);
    }

    $output = implode('', file($options['file']));
} else {
    if ($options['mode'] == 'cli') {

        if (empty($options['user'])) {
            $cmd = 'php '.$CFG->dirroot.'/blocks/vmoodle/cli/cron.php --host='.$options['host'];
        } else {
            $cmd = 'sudo -u '.$options['user'].' php '.$CFG->dirroot.'/blocks/vmoodle/cli/cron.php --host='.$options['host'];
        }

        $execres = exec($cmd, $rawoutput);
        $output = implode("\n", $rawoutput);
    } else {
        $params = array();

        $cronremotepassword = $DB->get_field('config', 'value', array('name' => 'cronremotepassword'));

        if (!empty($cronremotepassword)) {
            $params = array('password' => $cronremotepassword);
        }

        $url = new moodle_url('/admin/cron.php', $params);
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if (!empty($CFG->proxyhost)) {
            if (empty($CFG->proxyport)) {
                $proxy_host = $CFG->proxyhost;
            } else {
                $proxy_host = $CFG->proxyhost.':'.$CFG->proxyport;
            }
            curl_setopt($ch, CURLOPT_PROXY, $proxy_host);

            if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                $proxy_auth = $CFG->proxyuser.':'.$CFG->proxypassword;
                curl_setopt($ch, CURL_AUTHHTTP, CURLAUTH_BASIC);
                curl_setopt($ch, CURL_PROXYAUTH, $proxy_auth);
            }

            if (!empty($CFG->proxytype)) {
                if ($CFG->proxytype == 'SOCKS5') {
                    $proxy_type = CURLPROXY_SOCKS5;
                } else {
                    $proxy_type = CURLPROXY_HTTP;
                }
                curl_setopt($ch, CURL_PROXYTYPE, $proxy_type);
            }
        }

        $output = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpcode != 200) {
            $faulttype = 'HTTP RETURN ERROR';
            $notification = '['.$CFG->wwwroot.'] CURL HTTP error on '.$url;
        }
        else if (!empty($error)) {
            $faulttype = 'HTTP FETCH ERROR';
            $notification = '['.$CFG->wwwroot.'] CURL error on '.$url;
        }

        // close cURL resource, and free up system resources
        curl_close($ch);
    }
}

// We have a proper output. Analyse

$notification = '';
if (empty($output)) {
    $faulttype = 'EMPTY';
    $notification = '['.$CFG->wwwroot.'] Empty cron output. This is NOT an expected situation and may denote cron execution silent failure';
} else {

    if (preg_match('/Cron script completed correctly/', $output)) {
        die('Cron OK');
    }

    else if (preg_match('/Moodle upgrade pending, cron execution suspended./', $output)) {
        $faulttype = 'UPGRADE';
        $notification = '['.$CFG->wwwroot.'] Unresolved upgrade pending.';
    }

    else if (preg_match('/Fatal error/', $output)) {
        $faulttype = 'PHP ERROR';
        $notification = '['.$CFG->wwwroot.'] Fatal error in cron.';
    }

    else if (!preg_match('/Error code: cronerrorpassword/', $output)) {
        $faulttype = 'PASSWORD ERROR';
        $notification = '['.$CFG->wwwroot.'] cron locked bvy password.';
    }

    else {
        $faulttype = 'OTHER ERROR';
        $notification = '['.$CFG->wwwroot.'] cron has some unclassified error.';
    }
}


// We have some notifications

if (!empty($notification)) {

    mtrace('Mode: '.$options['mode']);
    mtrace($faulttype);
    mtrace($notification);

    $admins = $DB->get_records_list('user', 'id', explode(',',$CFG->siteadmins));

    foreach($admins as $a) {
        email_to_user($a, $a, '['.$SITE->shortname.':'.$faulttype.'] Cron Monitoring system', $notification);
    }
}