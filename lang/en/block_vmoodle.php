<?php

// Capabilities

$string['vmoodle:myaddinstance'] = 'Can add instance to my pages'; // @CORE
$string['vmoodle:addinstance'] = 'Can add instance'; // @CORE

// Block strings

$string['addall'] = 'Add all';
$string['addformdbgroup'] = 'Database ';
$string['addformdescription'] = 'Description ';
$string['addformfeaturesgroup'] = 'Features ';
$string['addforminputtexterror'] = 'You must enter a value here ';
$string['addformname'] = 'Name ';
$string['addformnfgroup'] = 'Network and files ';
$string['addformshortname'] = 'Short name ';
$string['addtoselection'] = 'Add to selection';
$string['addvmoodle'] = 'Define a new virtual moodle';
$string['administrate'] = 'Administrate';
$string['administration'] = 'Administration';
$string['advancedmode'] = 'Advanced mode';
$string['assistedcommand'] = 'Assisted commands';
$string['automateschema'] = 'Automate the scheme generation ';
$string['automateschema_desc'] = 'If enabled, this setting will apply to any new Vmoodle definition.';
$string['siteschema'] = 'VMoodle Host Definition';
$string['dbschema'] = 'VMoodle DB Definition';
$string['fileschema'] = 'VMoodle File Storage Definition';
$string['generateconfigs'] = 'Generate physical configurations';
$string['mnetschema'] = 'VMoodle Networking Definition';
$string['available'] = 'Available';
$string['badblockinsert'] = 'Unable to insert new block in \'block_vmoodle\' table.';
$string['badblockupdate'] = 'Unable to update block in \'block_vmoodle\' table.';
$string['badbootstraphost'] = 'Error when doing bootstrap for the MNET host (keys, or others).';
$string['badbootstrapnewhost'] = 'Error when doing bootstrap for the new MNET host (keys, vhosting, or others).';
$string['badbothblockhost'] = 'Host with \'wwwroot\' equals to block\'s \'vhostname\' cannot be find.';
$string['badclientuser'] = 'Wrong client user';
$string['badnodefile'] = 'Bad node file {$a}';
$string['badconnection'] = 'Error when connecting to database.';
$string['baddatabasenamealreadyused'] = 'This database name is already used.';
$string['baddatabasenamecoherence'] = 'Please check the database name consistency.';
$string['baddatabaseprefixvalue'] = 'This table prefix is not allowed.';
$string['baddumpcommandpath'] = 'Please check targeting of needed dump programs in the \'vconfig.php\' file.';
$string['badhostalreadydeleted'] = 'Unable to delete an already deleted host.';
$string['badhostnamealreadyused'] = 'This host\' site is already used.';
$string['badmoodledatapath'] = 'The specified path is not correct. Check its form.';
$string['badmoodledatapathalreadyused'] = 'The specified path is already used by another virtual host.';
$string['badmoodledatapathbackslash'] = 'Only one backslash \'\\\' is needed to separate strings.';
$string['badmoodleid'] = 'This host doesn\'t exist anymore.';
$string['badnohyphensindbname'] = 'No hyphens (minus) in database name !';
$string['badregexp'] = 'Expected format: /regexp/modifiers';
$string['badservicesnumber'] = 'There is no services anymore on this host.';
$string['badshortname'] = 'Shortname must have no spaces.';
$string['badtemplatation'] = 'Loading database from chosen template has failed (reading, writing or removing files).';
$string['badthishostdata'] = 'Unable to retrieve this host\'s data, from \'mnet_host\' table.';
$string['badvhostname'] = 'Bad host form.';
$string['blockname'] = 'Virtual Moodles';
$string['blocknameplural'] = 'Moodles Farms';
$string['cancelcommand'] = 'Cancel command';
$string['categoryignored'] = 'The category {$a} has been ignored:';
$string['categorywrongname'] = 'The category\'s name is wrong.';
$string['categorywrongpluginname'] = 'The plugin\'s name linked to the category {$a} is wrong.';
$string['certificate'] = 'Certificate';
$string['climakestep'] = 'Make step {$a}';
$string['clisnapstep'] = 'Snap step {$a}';
$string['cliusingtemplate'] = 'Make Node using template {$a}';
$string['climissingtemplateskip'] = 'Template files not found for {$a}';
$string['climissingnodes'] = 'Node list file is missing in command line arguments';
$string['cliemptynodelist'] = 'Node list is empty';
$string['clistart'] = 'Starting cli =';
$string['climakenode'] = 'Making node {$a}';
$string['clidestroynode'] = 'Destroying node {$a}';
$string['clisnapnode'] = 'Snapshotting node {$a}';
$string['cliprocesserror'] = 'An unrecoverable error occured. Aborting.';
$string['closewindow'] = 'Close the window';
$string['clinodeexistsskip'] = 'Node {$a} exists already. Ignored.';
$string['clinodenotexistsskip'] = 'Node {$a} does not exist. Ignored.';
$string['commanddescription'] = 'Description';
$string['commandemptydescription'] = 'The command\'s description is empty.';
$string['commandemptyname'] = 'The command\'s name is empty.';
$string['commandformnotlinked'] = 'No command linked to the form.';
$string['commandnotaparameter'] = 'One of parameters of the command {$a} is not a Vmoodle_Parameter object.';
$string['commandnotexists'] = 'The command doesn\'t exist'; 
$string['commands'] = 'Commands';
$string['commandsadministration'] = 'Commands administration:';
$string['commandwrongparametertype'] = 'Parameters of the command {$a} aren\'t supported types.';
$string['confirmdelete'] = 'Do you really want to delete (disable) this host ?';
$string['confirmfulldelete'] = 'Do you really want to destroy (definitive) this host ?';
$string['confirmedit'] = 'All changes are under the responsibility of the administrator, particularly in case of changing database name, tables prefix and path for "moodledata". Do you want to continue ?';
$string['connectionok'] = 'Connection is OK';
$string['contains'] = 'contains';
$string['confignotfound'] = 'Config file is not there';
$string['couldnotconnecttodb'] = 'Could not connect to DB';
$string['couldnotcreateclient'] = 'Could not create MNET client on "{$a}".';
$string['couldnotcreatedataroot'] = 'Could not create data root';
$string['couldnotcreatedb'] = 'Could not create the virtual DB';
$string['couldnotdropdb'] = 'Could not drop the virtual DB';
$string['couldnotfixdatabase'] = 'ERROR: Could not fix database.';
$string['couldnotkeyboot'] = 'ERROR: Could not initiate KeyBoot';
$string['cron'] = 'Cron';
$string['crontab'] = 'CRON tasks table ';
$string['databasecreated'] = 'Database created ';
$string['databaseloaded'] = 'Database initialized ';
$string['databasesetup'] = 'Database configurated ';
$string['dataexchange_name'] = 'Generic data exchange'; // from vmoodleadminset_generic @CORE
$string['dataexchange_description'] = 'Opens functions to exchange static text values on a system to system basis';  // from vmoodleadminset_generic @CORE
$string['datapath'] = 'Data path';
$string['datapathavailable'] = 'Data path is available';
$string['datapathnotavailable'] = 'Data path is NOT available';
$string['datapathbase'] = 'Path base for "moodledata" ';
$string['datapathcreated'] = 'File repository created ';
$string['datatbasedroped'] = 'Virtual database dropped';
$string['datatpathunbound'] = 'Data path unbound';
$string['db'] = 'Databases';
$string['dbbasename'] = 'Base name';
$stirng['dbcommandnotconfigured'] = 'Database command line not configured';
$string['dbcommanddoesnotmatchanexecutablefile'] = 'Database command is not pointing to an executable resource from the server point of view : {$a}';
$string['dbhost'] = 'DB Host';
$string['dblogin'] = 'DB login';
$string['dbname'] = 'DB name';
$string['dbpass'] = 'DB pass';
$string['dbpersist'] = 'DB persist';
$string['dbprefix'] = 'DB tables prefix';
$string['dbtype'] = 'DB type';
$string['delete'] = 'Delete';
$string['deletehost'] = 'Delete (disable) the configuration';
$string['details'] = 'Details:';
$string['donotopenservices'] = 'Do not open MNET services';
$string['edithost'] = 'Edit the configuration';
$string['editvmoodle'] = 'Edit a virtual host definition';
$string['elements'] = 'element(s)';
$string['errorbindingmnet'] = 'Error binding MNET services';
$string['errorreactivetemplate'] = 'Check moodle data path et database name from the host';
$string['errorsetupdb'] = 'Error when setting up the DB';
$string['errorbaddirectorylocation'] = 'moodledata path should not contain blank spaces. Relocate dump template location by changing your moodledata location in the filesystem';
$string['failedplatforms'] = 'Failed platforms:';
$string['filter'] = 'Filter';
$string['fixcommand'] = 'Fix command';
$string['forcedns'] = 'Force deployment even if DNS resolution is not available.';
$string['fulldeletehost'] = 'Destroy all host data';
$string['generateconfigs'] = 'Generate configuration files';
$string['generatedconfigs'] = 'Config generated: {$a}';
$string['hostexists'] = 'Host exists';
$string['hostnameexists'] = 'Hostname exists';
$string['hostsource'] = 'Hosts source';
$string['insuffisantcapabilities'] = 'Insufficient capabilities';
$string['key_autorenew_parms'] = 'Aumated MNET key renewal';
$string['crons'] = 'Crons';
$string['lastcron'] = 'Last cron';
$string['lastcrongap'] = '&Delta;';
$string['manualcommand'] = 'Manual command';
$string['mastermnetnotice'] = 'The moodle network (mnet) is not enabled on the master host. The virtual moodle will continue install without the mnet activation. You may bind manually the network feature by yourself after deployment';
$string['massdeployment'] = 'Massive deployment';
$string['csvencoding'] = 'CSV Encoding';
$string['csvencoding_desc'] = 'Choose encoding for the nodelist.csv file';
$string['mnet'] = 'MNET';
$string['mnetadmin_name'] = 'Meta-administration service';
$string['mnetadmin_description'] = 'Remote administration from a master Moodle';
$string['mnetkeyautorenew'] = 'Activation';
$string['mnetkeyautorenew_desc'] = 'Enables the global key refreshing task';
$string['mnetkeyautorenewgap'] = 'Detect lookback delay';
$string['mnetkeyautorenewgap_desc'] = 'The delay a key obsolescence will be anticipated';
$string['mnetkeyautorenewtime'] = 'Scheduled time for key renewal';
$string['mnetactivationrequired'] = 'MNET activation required';
$string['mnetbound'] = 'Bound to MNET network';
$string['mnetdisabled'] = 'MNET disabled';
$string['mnetenabled'] = 'MNET activated';
$string['mnetfree'] = 'Free MNET network';
$string['mnetnew']	= 'New MNET subnetwork';
$string['mnetopenservices'] = 'MNET Services';
$string['multimnet'] = 'Subnetwork MNET ';
$string['multimnet_desc'] = 'Defines MNET behaviour of the new platform';
$string['musthaveshortname'] = 'Shortname is mandatory';
$string['mysqlcmd'] = 'mysql command location';
$string['mysqldumpcmd'] = 'Mysqldump command location';
$string['newvmoodle'] = 'New virtual host';
$string['nextstep'] = 'Next';
$string['nomnet'] = 'No Moodle Net';
$string['none'] = 'None';
$string['noplatformchosen'] = 'No platform chosen.';
$string['notallowed'] = 'Not allowed';
$string['notcontains'] = 'no contains';
$string['notemplates'] = 'No existing template (snapshot) for defining a new virtual moodle';
$string['novmoodle'] = 'The host and/or its corresponding block doesn\' exist anymore.';	
$string['novmoodles'] = 'No virtual moodle defined.';
$string['off'] = 'Disabled';
$string['on'] = 'Enabled';
$string['openallservices'] = 'Open all MNET services';
$string['operation'] = 'Operation';
$string['organization'] = 'Organization';
$string['organizationmail'] = 'foo@organization';
$string['organizationunit'] = 'Unit';
$string['parameterallowedvaluesnotgiven'] = 'The allowed values of enum {$a} aren\'t given.';
$string['parameteremptydescription'] = 'The description of parameter {$a} is empty.';
$string['parameteremptyname'] = 'The parameter\'s name is empty.';
$string['parameterforbiddentype'] = 'The type of parameter {$a} is forbidden.';
$string['parameterinternalconstantnotgiven'] = 'The constant "{$a->constant_name}" of parameter "{$a->parameter_name}" is unknown.';
$string['parameterinternalfunctionfailed'] = 'The "{$a->function_name}" function has raised an exception {$a->message} during his call.';
$string['parameterinternalfunctionnotexists'] = 'The function "{$a->function_name}" of internal parameter "{$a->parameter_name}" doesn\'t exist.';
$string['parameterinternalparameternotgiven'] = 'The parameter "{$a->parameter_need}" of parameter "{$a->parameter_name}" isn\'t given.';
$string['parametervaluenotdefined'] = 'The value of parameter "{$a}" is not defined.';
$string['parameterwrongdefaultvalue'] = 'The default value of parameter "{$a}" is wrong.';
$string['pluginname'] = 'VMoodle'; // @CORE
$string['pgsqlcmd'] = 'Postgres command location';
$string['pgsqldumpcmd'] = 'Postgres sqldump command location';
$string['plateformreactivate'] = 'Platform reactivated';
$string['platformname'] = 'Platform name';
$string['plugin'] = 'Plugin';
$string['plugindisabled'] = 'Plugin is disabled.';
$string['pluginenabled'] = 'Plugin is enabled.';
$string['pluginnotdisabled'] = 'Plugin were not disabled.';
$string['pluginnotenabled'] = 'Plugin were not enabled.';
$string['pluginnotuninstalled'] = 'Plugin {$a} were not uninstalled.';
$string['pluginsadministration'] = 'Plugins administration:';
$string['pluginuninstalled'] = 'Plugin {$a} was correctly uninstalled.';
$string['publish'] = 'Publish';
$string['reactivetemplate'] = 'Reactivate the plateforme';
$string['regexp'] = 'regexp';
$string['removeall'] = 'Remove all';
$string['removefromselection'] = 'Remove from selection';
$string['renewallbindings'] = 'Renew all bindings';
$string['retrieveplatforms'] = 'Retrieve platforms';
$string['rpcstatus100'] = 'Test command.';
$string['rpcstatus200'] = 'Successfull command.';
$string['rpcstatus500'] = 'RPC failure.';
$string['rpcstatus501'] = 'No local account for calling user.';
$string['rpcstatus502'] = 'Configuration failure.';
$string['rpcstatus510'] = 'Insuffisant rights.';
$string['rpcstatus511'] = 'MNET failure.';
$string['rpcstatus520'] = 'Unable to get SQL record.';
$string['rpcstatus521'] = 'Unable to run SQL command.';
$string['runcmdagain'] = 'Run command again';
$string['runnewcommand'] = 'Run new command';
$string['runothercommand'] = 'Run an other command on those platforms';
$string['runotherplatforms'] = 'Run command again on other platforms';
$string['runvcron'] = 'Run VCRon manually';
$string['selected'] = 'Selected';
$string['services'] = 'Services';
$string['servicesformselection'] = 'Default services pattern';
$string['mainservicesformselection'] = 'Services pattern to master Moodle<br/>This pattern is applied to the new node. the mirrored pattern is applied to the master node (this moodle)';
$string['peerservicesformselection'] = 'Services pattern to subnet peers<br/>This pattern is applied to the new node. the mirrored pattern is applied to all peers in the subnetwork';
$string['servicesstrategy'] = 'Services strategy';
$string['servicesstrategy_desc'] = 'Service strategy that will be applied to the new host at bootup';
$string['servicesstrategydefault']	= '"Minimal services" strategy (SSO for remote administration only)';
$string['servicesstrategysubnetwork'] = 'Subnetwork Template services strategy';
$string['shortnameexists'] = 'Shortname exists already';
$string['skip'] = 'Skip';
$string['snapshothost'] = 'Snapshot the configuration';
$string['snapshotmaster'] = 'Snapshot master Moodle';
$string['sqlcommand'] = 'SQL command';
$string['sqlfile'] = 'SQL file';
$string['startingstate'] = 'Starting state';
$string['status'] = 'Status';
$string['systempath_desc'] = 'Absolute system path to executable';
$string['subscribe'] = 'Subscribe';
$string['successaddnewhost'] = 'Adding the new host completed.';
$string['successaddnewhostwithoutmnet'] = '';
$string['successdeletehost'] = 'Deleting (disabling) the host completed.';
$string['successedithost'] = 'Editing the host completed.';
$string['successfinishedcapture'] = 'Capture completed.';
$string['successfullplatforms'] = 'Successfull platforms:';
$string['successstrategyservices'] = 'Default services strategy deployment done.';
$string['tabpoolmanage'] = 'Pool management';
$string['tabpoolsadmin'] = 'Pool administration';
$string['tabpoolservices'] = 'Services strategy';
$string['template'] = 'Template';
$string['testconnection'] = 'Database connection test';
$string['testdatapath'] = 'Test MoodleData path';
$string['tools'] = 'Tools';
$string['unablepopulatecommand'] = 'Unable to populate command.';
$string['uninstall'] = 'Uninstall';
$string['unknownhost'] = 'The host you are trying to setup has no DNS resolution. You can force the way executing again the procedure but the MNET initialisation will not be processed.';
$string['unknownhostforced'] = 'the new host you are trying to setup has no DNS resolution. You have required forcing this limitation. MNET bindings will not be performed and you will have to set them up manually.';
$string['unknownuserhost'] = 'User host platform unknown';
$string['uploadscript'] = 'Upload a script';
$string['vdatapath'] = 'Path for "moodledata" ';
$string['vdatapath_desc'] = 'An "absolute path" pattern where <%%INSTANCE%%> placeholder can be replaced by host shortname ';
$string['vdbbasename'] = 'Database name ';
$string['vdbhost'] = 'Database host ';
$string['vdbhost_desc'] = '';
$string['vdblogin'] = 'Database administrator login ';
$string['vdblogin_desc'] = 'Administrator login. Check this account has credentials to create database and tables.';
$string['vdbname'] = 'Database name ';
$string['vdbname_desc'] = 'Database name to be created. VMoode creates database and it MUST NOT exist.';
$string['vdbpass'] = 'Database password ';
$string['vdbpass_desc'] = 'Database administator password ';
$string['vdbpersist'] = 'Connection persistance ';
$string['vdbpersist_desc'] = 'Enables or disables persistant connections.';
$string['vdbprefix'] = 'Tables prefix ';
$string['vdbprefix_desc'] = 'Should not change unless very specific local situation.';
$string['vdbtype'] = 'Database type ';
$string['vdbtype_desc'] = 'Actually supported mysqli and postgres. Old mysql (Moodle 1.9) not supported any more.';
$string['vhostname'] = 'Site\'s host';
$string['virtualplatforms'] = 'Virtual hosts';
$string['vmoodleadministration'] = 'Moodle virtual instances administration';
$string['vmoodleappname'] = 'Moodle Virtualization';
$string['vmoodledoadd1'] = 'STEP 1 on 4 : The new virtual platform database is being loaded. Next step will treat it.';
$string['vmoodledoadd2'] = 'STEP 2 on 4 : The database has been converted. Next step will load data files.';
$string['vmoodledoadd3'] = 'STEP 3 on 4 : Data Files are available. Next step will register the virtual platform.';
$string['vmoodledoadd4'] = 'STEP 4 on 4 : Platform registration complete. Platform URL has been activated. Last step will enable Mnet protocols.';
$string['vmoodlehost'] = 'Virtual Moodle host';
$string['vmoodleip'] = 'IP Address';
$string['vmoodleip_desc'] = 'A true XXX.XXX.XXX.XXX IP.';
$string['vmoodlehost'] = 'Virtual Moodle Host name scheme';
$string['vmoodlehost_desc'] = 'Any http(s):// scheme where <%%INSTANCE%%> will be replaced by host shortname.';
$string['vmoodlemanager'] = 'Virtual Moodle instances manager';
$string['vmoodlesnapshot1'] = 'STEP 1 on 3 : Snapshot preparation done. Next step will copy the database. This operation can be long depending on the database size.';
$string['vmoodlesnapshot2'] = 'STEP 2 on 3 : Database saved. Next step will save Moodle Data. This operation can be very long depending on the number of files added to the platform';
$string['vmoodlesnapshot3'] = 'STEP 3 on 3 : Files saved. The platform snapshot is complete.';
$string['vtemplate'] = 'Vmoodle template';
$string['withmessage'] = 'with message "{$a}"';
$string['withoutmessage'] = 'without message';
$string['wrongplugin'] = 'Wrong plugin.';
$string['wwwrootexceedscsrlimits'] = 'The choosen wwwroot exceeds 64 chars length. This is not compatible with MNET openssl CSR requirements.';
$string['withselection'] = 'With the selection: ';
$string['disableinstances'] = 'disable instances ';
$string['enableinstances'] = 'enable instances ';
$string['deleteinstances'] = 'destroy instances ';
$string['deleteconfirm'] = 'Delete is NOT reversible. Data will be definitively destroyed. Continue?';


/*** Help Strings ***/
$string['name'] = 'Name'; //@CORE
$string['name_help'] = '  //@CORE
<p><b>Public Name of the Instance</b></p>

<p>This name is the apparent instance name as known on the master
moodle side, and will serve as preset for the Moodle instance name. This
name can be updated after instance creation.</p>
';

$string['shortname'] = 'Short Name'; // @CORE
$string['shortname_help'] = ' // @CORE
<p><b>Shortname for the Instance</b></p>

<p>The shortname given here will be preset as shortname of the
created instance. This token is also used in the definition of the other
physical pathes and database name. It CANNOT be updated after the
instance is created.</p>
<p>The short name MUST be a token WITHOUT SPACES.</p>
';

$string['description'] = 'Description'; // @CORE
$string['description_help'] = ' // @CORE
<p>This text will be setup as base moodle description in the local
instance datamodel. It may be updated after instance creation.</p>
';
 

$string['vhostname'] = 'Hostname'; // @CORE
$string['vhostname_help'] = ' // @CORE
<p><b>Instance Effective Hostname</b></p>

<p>This field defines the apparent Web name of the new instance. Generally, you should use a %%INSTANCE%% metatag that will be replaced with the instance shortname. This will allow each new instance to be independantly identified on the Web. The atual subdomain will be automatically constructed using the lowercased shortname.</p>
<p>If the instance distinction is made on a DNS subdomain bases (e.g.: %%INSTANCE%%.mydomain.org), the instances will be automatically available on the Web using the virtualhost DocumentRoot virtualization of Apache, sharing the same DocumentRoot location.</p>
<p>A typical configuration for apache is :</p>
<pre>
&lt;VirtualHost 127.0.0.1&gt;
    ServerAdmin admin@foo.org
    ServerName default.mydomain.org
    ServerAlias *.mydomain.org
	VirtualDocumentRoot "path_to_default/vmoodle_defaut"
    ErrorLog logs/vmoodle-error_log
    CustomLog logs/vmoodle-access_log common
&lt;/VirtualHost&gt;
</pre>
<p>Other binding solutions might be possible, but we do not have evaluation for them.</p>
';

$string['vdbhost_help'] = '<p><b>Instance Local Database</b></p> // @CORE

<p>Must contain a valid hostname:port pattern where the database
holding the instance\'s own datamodel will run. This host must have a
suitable database, and must have network connectivity from the master
moodle website server.</p>';

$string['vdbtype_help'] = ' //@CORE
<p><b>Instance Database Type</b></p>

<p>The vmoodle block has implementation for MySQL and PostgreSQL
virtual moodle deployment. There could be heterogeneous instances from a
unique master moodle. Other databases supported by the standard
distribution of Moodle are not supported for virtualization.</p>
';

$string['vdbname_help'] = ' // @CORE
<p><b>Instance Database Name</b></p>

<p>Allows database name definition for the created instance. This
name CANNOT be updated. It is actually preset to a default based on the
instance shortname.</p>
';

$string['vdbpersist_help'] = ' // @CORE
<p><b>Database Connection Persistance</b></p>

<p>This dropdown allows selecting if the instance may use persistent
connection to its own database. This parameter can be updated.</p>
';

$string['vdatapath_help'] = ' // @CORE
<p><b>Physical File System for the Instance</b></p>

<p>Any user file will be uploaded within a single data volume
usually called "moodledata". This field allows defining the location for
this file volume. Usually, it will be preset using the vmoodle block
global configuration parameter physical prefix, and the shortname of the
moodle virtual instance.</p>
<p>The physical file repository is loaded with a set of preexisting
files obtained from the "Virtual Moodle Template" definition.
<p>Warning : you should ensure that the root path of all virtual
instances are writable by the master server.</p>
<p>The physical repository location CANNOT BE UPDATED after the
instance is created.</p>
';

$string['mnet_help'] = ' // @CORE
<p><b>MNET Activation of the Instance</b></p>

<p>New virtual hosts can be integrated directly in the MNET master
network. For this, the master must be itself in the MNET network. If
this is not the case, the instance installation continue, but its
network settings will not be configurated.</p>
<p>It can be possible to mix instances with and without the network.
By default, all the instances activating the MNET network will be in the
same MNET network.</p>
';   
   
$string['services_help'] = ' // @CORE
<p><b>Services strategy</b></p>

<p>This field allows services strategy definition, when defining a
virtual host.</p>

<p>It is possible to base the strategy on a default strategy, calked
on the master virtual host\'s services and defined by an administrator.
This option is automatically selected when the virtual host enables MNET
without selecting a subnetwork, or when the virtual host disables MNET.</p>

<p>In a case of establishing a link with a subnetwork, services will
be synchronized with one actual member of the subnetwork. It is
necessary to remember that all members of a subnetwork are synchronized
in terms of services.</p>
';   

$string['vtemplate_help'] = ' // @CORE
<p><b>Vmoodle Template</b></p>

<p>When a VMoodle host is instantiated, the contextual part of the
host can be initialized with a predefined data model and an already
constituted exploiation files set.</p>
<p>This loading put in service a VMoodle host, in a preconfigurated
state.</p>
<p>With this parameter, you can choose one of the available
configuration.</p>
<p>The preset instances should be formed with 2 directories :</p>
<ul>
	<li>The first directory contains two SQL scripts called :
	"moodle_master.&lt;base&gt;.sql" and
	"moodle_setup_template.&lt;base&gt;.sql". &lt;base&gt; can be "MySQL"
	or "PostgreSQL" typed, depending on the choosen template\'s model. The
	first script loads the database with a typed data model, coming from
	the host model of the template. The second script execute
	contextualised SQL request, using some specific parameters coming from
	the instance definition form.</li>
	<li>The second directory contains a preformed "moodledata"
	directory copy. This directory can contain any set of files, coming
	from the host model of the template.</li>
</ul>
<p>In order to correct all absolute paths registered by the host
model, the template ask the presence of an additional "manifest" file,
indicating web and system files roots of the host model. This indication
allows the deployment algorithm to correct any absolute paths of the new
instance.</p>
';   
