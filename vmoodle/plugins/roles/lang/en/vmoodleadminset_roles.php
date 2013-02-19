<?php
/**
 * English traduction of role commands category.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */

$string['backtocomparison'] = 'Back to role comparison';
$string['capabilityparamsyncdesc'] =  'The capacity to synchronize';
$string['cmdcomparedesc'] = 'Compare role capabilities.';
$string['cmdcomparename'] = 'Role comparison';
$string['cmdsynccapabilitydesc'] = 'Synchronize a role capacity.';
$string['cmdsynccapabilityname'] = 'Synchronization of a role capacity';
$string['cmdsyncdesc'] = 'Synchronize a role capabilities.';
$string['cmdsyncname'] = 'Synchronization of a role';
$string['comparerole'] = 'Comparing role "{$a}"';
$string['confirmrolecapabilitysync'] = 'You are going to change a role capability on sereval platforms. Would you like to continue ?';
$string['editrole'] = 'Edit role';
$string['mnetadmin_name'] = 'MNET Meta Administration';
$string['mnetadmin_description'] = 'Provides functions to perform network scoped administration operations, such as role or configuration settigns synchronisation.';
$string['nocapability'] = 'No selected capability.';
$string['nosrcpltfrm'] = 'No source platform.';
$string['nosyncpltfrm'] = 'Any platforms to synchronize.';
$string['platformparamsyncdesc'] = 'Source platform of the role to copy';
$string['pluginname'] = 'Role related commands';
$string['problematiccomponent'] = 'Unknown capabilities';
$string['roleparamcomparedesc'] = 'The role to compare';
$string['roleparamsyncdesc'] = 'The role to synchronize';
$string['roles'] = 'Roles';
$string['synchronize'] = 'Synchronize';
$string['syncwithitself'] = 'Synchronizing "{$a->role}" role from "{$a->platform}" platform with itself.';

$string['rolecompare_help'] = '
<h2>Role comparison</h2>
<table style="width: 80%;">
  <caption>Legend of comparison table:</caption>
  <thead>
    <tr>
      <th style="width: 16px;">Icon</th>
      <th>Legend</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Permissions:</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/permissionallow.png" alt="Permettre"/></td>
      <td>Means that capability is allowed.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/permissionprevent.png" alt="Empécher"/></td>
      <td>Means that capability is prevent.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/permissionforbid.png" alt="Interdire"/></td>
      <td>Means that capability is forbidden.</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Contexts:</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextB.png" alt="Contexte B"/></td>
      <td>Means that capability is "block" context.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextC.png" alt="Contexte C"/></td>
      <td>Means that capability is "course" context.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextCC.png" alt="Contexte CC"/></td>
      <td>Means that capability is "course category" context.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextG.png" alt="Contexte G"/></td>
      <td>Means that capability is "group" context.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextM.png" alt="Contexte M"/></td>
      <td>Means that capability is "module" context.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextS.png" alt="Contexte S"/></td>
      <td>Means that capability is "system" context.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextU.png" alt="Contexte U"/></td>
      <td>Means that capability is "user" context.</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Lack of capabilities:</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/norolecapability.png" alt="Pas de capacité de rôle"/></td>
      <td>Means that capability is not set for this role.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/libs/rolelib/pix/nocapability.png" alt="Pas de capacité"/></td>
      <td>Means that capability is not defined on this platform.</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Difference markers:</td>
    </tr>
    <tr>
      <td style="background-color: #F2FF98;"><img src="/blocks/vmoodle/plugins/roles/pix/blank.png" alt=" "/></td>
      <td>Means that capability has different context than others platforms.</td>
    </tr>
    <tr>
      <td style="background-color: #FF607D;"><img src="/blocks/vmoodle/plugins/roles/pix/blank.png" alt=" "/></td>
      <td>Means that capability has different permission than others platforms.</td>
    </tr>
    <tr>
  </tbody>
</table>
';