<?php
/* Copyright (C) 2012 Alex Russinov  <alexrussinov@gmail.com>
 *
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *   	\file       factures_depenses/paiement/fiche.php
 *		\ingroup    depenses module
 *		\brief      Page to view card of payement
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */


require("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/paiementdepenses.class.php");

$langs->load("bills");
$langs->load("suppliers");
$langs->load("companies");


llxHeader();
$h=0;

$head[$h][0] = DOL_URL_ROOT.'/factures_depenses/paiement/fiche.php?id='.$_GET['id'];
$head[$h][1] = $langs->trans('Card');
$h++;

$head[$h][0] = DOL_URL_ROOT.'/factures_depenses/paiement/info.php?id='.$_GET['id'];
$head[$h][1] = $langs->trans('Info');
$hselected = $h;
$h++;

dol_fiche_head($head, $hselected, $langs->trans("SupplierPayment"), 0, 'payment');

$paiement = new PaiementDepenses($db);
$paiement->fetch($_GET["id"], $user);
$paiement->info($_GET["id"]);

print '<table width="100%"><tr><td>';
dol_print_object_info($paiement);
print '</td></tr></table>';

print '</div>';

$db->close();

llxFooter('$Date: 2011/07/31 23:57:03 $ - $Revision: 1.11 $');
?>