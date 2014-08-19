VMoodle block
#############

Implements a packaged virtualization control feature for large "Moodle Arrays" 

Important requirements for VMoodling :

Version 2014071301 summary
=============================

Essentially redraws the internal class organization to cope qith 
core Moodle class loading strategy.

version 2013020801 summary
=============================

- Fixes security issue when used with local/technicalsignals plugin
- Adds new remote plugin and equipement control
- MultiSQL commands fixed
- Several fixes on meta administration
- added config file generator (to help cli migrations)
- impove Command error and success report
- integrates mahara patches for MNET stability.

Summary of prerequisites
################################################################

0. Alter the <moodle>/lib/classes/component.php in lone 48 to let subplugins be used in blocks. (add 'block' to the list).

1. Installing vmoodle block in codebase
2. Installing the master moodle as usual browsing to Administration -> notifications
3. Installing the config.php hook to vconfig.php
4. Configuring the VMoodle common parameters

Post install procedure
----------------------------------------------------------------
5. Having names resolved for all the virtual moodles, through an explicit DNS binding OR a wildcard dns binding
(local resolutions on the webserver could make it possible also).
6. Setting up the master Moodle with relevant startup settigns (including MNET activation).
7. Snapshoting the master Moodle as template for virtual moodling (may be long)
8. Deploying vmoodle instances (may be long).

0 Old patches information (vs. 1.9)

0.1 Add possibility to blocks to handle subplugins and install them:
####################################################################

The vmoodle block construction is using subplugins, that are plugins 
handled inside the vmoodle block scope.

for Moodle 2.2
--------------

Conversely to version 1.9, now subplugins are handled as "standard architecture feature" for
any plugin. 

Since 2.4
---------

An architecture flexibility regression caused the subplugin system for blocks to fail. We 
now need an extra patch to get it finding plugin subcomponent path properly again. 

This patch will alter 2 plugin related functions in /lib/moodlelib.php

around L§8030

/**
 * Lists all plugin types
 * @param bool $fullpaths false means relative paths from dirroot
 * @return array Array of strings - name=>location
 */
function get_plugin_types($fullpaths=true) {
    global $CFG;

    static $info     = null;
    static $fullinfo = null;

    if (!$info) {
        $info = array('qtype'         => 'question/type',
                      'mod'           => 'mod',
                      'auth'          => 'auth',
                      'enrol'         => 'enrol',
                      'message'       => 'message/output',
                      'block'         => 'blocks',
                      'filter'        => 'filter',
                      'editor'        => 'lib/editor',
                      'format'        => 'course/format',
                      'profilefield'  => 'user/profile/field',
                      'report'        => 'report',
                      'coursereport'  => 'course/report', // must be after system reports
                      'gradeexport'   => 'grade/export',
                      'gradeimport'   => 'grade/import',
                      'gradereport'   => 'grade/report',
                      'gradingform'   => 'grade/grading/form',
                      'mnetservice'   => 'mnet/service',
                      'webservice'    => 'webservice',
                      'repository'    => 'repository',
                      'portfolio'     => 'portfolio',
                      'qbehaviour'    => 'question/behaviour',
                      'qformat'       => 'question/format',
                      'plagiarism'    => 'plagiarism',
                      'tool'          => $CFG->admin.'/tool',
                      'cachestore'    => 'cache/stores',
                      'cachelock'     => 'cache/locks',
                      'theme'         => 'theme',  // this is a bit hacky, themes may be in $CFG->themedir too
        );

// PATCH : Allow subplugins in blocks
        $subpluginowners = array_merge(array_values(get_plugin_list('mod')),
                array_values(get_plugin_list('editor')), array_values(get_plugin_list('block')));
// /PATCH 
        foreach ($subpluginowners as $ownerdir) {

around L§8106

/**
 * Simplified version of get_list_of_plugins()
 * @param string $plugintype type of plugin
 * @return array name=>fulllocation pairs of plugins of given type
 */
function get_plugin_list($plugintype) {
    global $CFG;

    $ignored = array('CVS', '_vti_cnf', 'simpletest', 'db', 'yui', 'tests');
    if ($plugintype == 'auth') {
        // Historically we have had an auth plugin called 'db', so allow a special case.
        $key = array_search('db', $ignored);
        if ($key !== false) {
            unset($ignored[$key]);
        }
    }

    if ($plugintype === '') {
        $plugintype = 'mod';
    }

    $fulldirs = array();

    if ($plugintype === 'mod') {
        // mod is an exception because we have to call this function from get_plugin_types()
        $fulldirs[] = $CFG->dirroot.'/mod';
// PATCH : Allow subplugins in blocks
    } else if ($plugintype === 'block') {
        // block is an exception because we have to call this function from get_plugin_types()
        $fulldirs[] = $CFG->dirroot . '/blocks';
// /PATCH
    } else if ($plugintype === 'editor') {


Since moodle 2.6
----------------

Subplugin handling has been fluidified, with a very light change in a core library. 
You'll just have to add the 'block' item in the subplugin capable plugins : lib/classes/component.php at line 48.

    protected static $supportsubplugins = array('mod', 'editor', 'tool', 'local', 'block');


0.2 Add possibility for blocks to handle xmlrpc the same way Moodle modules do:
###############################################################################

Moodle 2.2 supports now generic Mnet service declaration in all plugins.

0.3 Patch for automated key rotations and consolidation in VMoodled networks:
#############################################################################

This patch allows a "well known trusted peer" to force asynchronous renewal of his
own key. It is still needed in Moodle 2.x versions

Location : mnet/lib.php
Changes : start of mnet_get_public_key() (including signature)

Patch content : 

// PATCH : VMoodle Mnet automated key renewal -- adding force mode
function mnet_get_public_key($uri, $application=null, $force=0) {
    global $CFG, $DB;

    $mnet = get_mnet_environment();
    // The key may be cached in the mnet_set_public_key function...
    // check this first
    // cache location of key must be bypassed when we need an automated renew.
    if (!$force){
	    $key = mnet_set_public_key($uri);
	    if ($key != false) {
	        return $key;
	    }
    }
// /PATCH

    if (empty($application)) {
        $application = $DB->get_record('mnet_application', array('name'=>'moodle'));
    }

//! PATCH : Mnet automated key renewal
    $rq = xmlrpc_encode_request('system/keyswap', array($CFG->wwwroot, $mnet->public_key, $application->name, $force), array("encoding" => "utf-8"));
// /PATCH


1. Master configuration changes : Installing the config.php hook to vconfig.php
###############################################################################

Main config.php file must be changed in order to plug virtualization hooking.

config must have an include call to vconfig.php virtualization configuration router.
You can obtain this file from the vconfig-dist.php template, making your own 
vconfig.php file in blocks/vmoodle and then install the hook point in the standard 
config.php of Moodle.

/// VMOODLE Hack
include $CFG->dirroot.'/blocks/vmoodle/vconfig.php';
/// /VMOODLE Hack

must be located BEFORE the call to lib/setup.php include and AFTER the static configuration. 

Setting up Apache configuration for virtual Moodling
####################################################

Moodle virtualization assumes correct routing of each instance to the same RootDirectory inthe server.

A way to do this is using VirtualDirectory. Here comes a sample of Apache configuration that allows
this kind of generic binding.

<VirtualHost 127.0.0.1>
    ServerAdmin webmaster@eisti.fr
    ServerName %mastermoodlehost%
    ServerAlias *.%mastermoodledomain%
	VirtualDocumentRoot "D:/wwwroot/%moodledir%"
    ErrorLog logs/vmoodle_common-error_log
    CustomLog logs/vmoodle_common-access_log common
</VirtualHost>

For example : 

the master moodle site could be : 

%mastermoodlehost% = moodle.mydomain.edu
%moodledomain% = mydomain.edu

Using a wildcard DNS
####################

Defining wildcard DNS record say, to resolve *.mydomain.edu
avoids having to bind each virtual moodle within the DNS server
before using it. 

Any undefined moodle will respond with an error message.

Cron handling by virtualization
###############################

VMoodle provides from now an automated handling of cron execution 
for virtualized hosts. It will be not any more needed to manually 
edit the cron tab for each new VMoodle instance. A single cron call 
to vcron.php in the VMoodle block implementation will schedule and 
rotate all VMoodle crons. The VMoodle cron should be run at sufficiant 
frequence so each VMoodle has the expected turnover.  

The ROUND_ROBIN mode runs a VMoodle per turn scannnig the VMoodle 
definitions in order.

The LOWEST_POSSIBLE_GAP mode guarantees a nominal period for each 
VMoodle by dispatching defined VMoodle on available cron ticks 
(still in table order).  

The VMoodle interface adds three additional attributes in the display 
for VMoodles : 

•amount of passed crons
•date of last cron
•time elapsed (in secs) since the last cron was run

On large implementations running heavy moodle cron jobs or a lot 
of VMoodles, it can be a good idea to make cron run on a spare little 
server that can run the codebase and has access to the database pool.

