<?php
/**
 * French traduction of role commands category.
 * 
 * @package block-vmoodle
 * @category blocks
 * @author Bruce Bujon (bruce.bujon@gmail.com)
 */

$string['backtocomparison'] = 'Retour à la comparaison du rôle';
$string['capabilityparamsyncdesc'] =  'La capacité à synchroniser';
$string['cmdcomparedesc'] = 'Compare les capacités d\'un rôle.';
$string['cmdcomparename'] = 'Comparaison d\'un rôle';
$string['cmdsynccapabilitydesc'] = 'Synchronise une capacité d\'un rôle.';
$string['cmdsynccapabilityname'] = 'Synchronisation d\'une capacité d\'un rôle';
$string['cmdsyncdesc'] = 'Synchronise les capacités d\'un rôle.';
$string['cmdsyncname'] = 'Synchronisation d\'un rôle';
$string['comparerole'] = 'Comparaison du role "{$a}"';
$string['confirmrolecapabilitysync'] = 'Vous êtes sur le point de modifier une capacité de rôle sur plusieurs plate-formes. Voulez-vous continuer ?';
$string['editrole'] = 'Editer le rôle';
$string['nocapability'] = 'Pas de capacité sélectionnée.';
$string['nosrcpltfrm'] = 'Pas de plate-forme source.';
$string['nosyncpltfrm'] = 'Pas de plate-formes à synchroniser.';
$string['platformparamsyncdesc'] = 'Plate-forme source du rôle à copier';
$string['problematiccomponent'] = 'Capacités inconnues';
$string['roleparamcomparedesc'] = 'Le rôle à comparer';
$string['roleparamsyncdesc'] = 'Le rôle à synchroniser';
$string['roles'] = 'Rôles';
$string['synchronize'] = 'Synchroniser';
$string['syncwithitself'] = 'Synchronisation du rôle "{$a->role}" de la plate-forme "{$a->platform}" avec elle-même.';

$string['rolecompare_help'] = '
<h2>Comparaison d\'un rôle</h2>
<table style="width: 80%;">
  <caption>Légende du tableau de comparaison :</caption>
  <thead>
    <tr>
      <th style="width: 16px;">Icône</th>
      <th>Légende</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Les permissions :</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/permissionallow.png" alt="Permettre"/></td>
      <td>Signifie que la capacité est permise.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/permissionprevent.png" alt="Empécher"/></td>
      <td>Signifie que la capacité est empéchée.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/permissionforbid.png" alt="Interdire"/></td>
      <td>Signifie que la capacité est interdite.</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Les contextes :</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextB.png" alt="Contexte B"/></td>
      <td>Signifie que la capacité est de contexte "block".</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/rolels/pix/contextC.png" alt="Contexte C"/></td>
      <td>Signifie que la capacité est de contexte "course".</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextCC.png" alt="Contexte CC"/></td>
      <td>Signifie que la capacité est de contexte "course category".</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextG.png" alt="Contexte G"/></td>
      <td>Signifie que la capacité est de contexte "group".</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextM.png" alt="Contexte M"/></td>
      <td>Signifie que la capacité est de contexte "module".</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextS.png" alt="Contexte S"/></td>
      <td>Signifie que la capacité est de contexte "system".</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/contextU.png" alt="Contexte U"/></td>
      <td>Signifie que la capacité est de contexte "user".</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Les absences de capacités :</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/norolecapability.png" alt="Pas de capacité de rôle"/></td>
      <td>Signifie que la capacité n\'est pas définie pour ce rôle.</td>
    </tr>
    <tr>
      <td><img src="/blocks/vmoodle/plugins/roles/pix/nocapability.png" alt="Pas de capacité"/></td>
      <td>Signifie que la capacité n\'est pas définie sur cette plate-forme.</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size 1.2 em; font-style: italic;">Les marqueurs de différences :</td>
    </tr>
    <tr>
      <td style="background-color: #F2FF98;"><img src="/blocks/vmoodle/plugins/roles/pix/blank.png" alt=" "/></td>
      <td>Signifie que la capacité possède un contexte différent de celui des autres plate-formes.</td>
    </tr>
    <tr>
      <td style="background-color: #FF607D;"><img src="/blocks/vmoodle/plugins/roles/pix/blank.png" alt=" "/></td>
      <td>Signifie que la capacité possède une permission différente de celles des autres plate-formes.</td>
    </tr>
    <tr>
  </tbody>
</table>
';