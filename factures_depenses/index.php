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
 *   	\file       factures_depenses/index.php
 *		\ingroup    depenses module
 *		\brief      Page to view list of invoices
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */


require("../main.inc.php");

require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/depenses.class.php");
require_once(DOL_DOCUMENT_ROOT."/fourn/class/fournisseur.class.php");

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("depenses");

// Get parameters
$id=isset($_GET["id"])?$_GET["id"]:$_POST["id"];

// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}
$search_ref=GETPOST('search_ref');
$search_ttc=GETPOST('total_ttc');
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="d.dated";
$limit = $conf->liste_limit;

/*******************************************************************
* ACTIONS
*
********************************************************************/

if ($_GET["action"] == 'add' || $_POST["action"] == 'add')
{
	if (! $_POST["cancel"])
	{
	global $langs;
	$obj =new Depenses($db);
	
	$obj->type=$_POST["type"];
	$obj->societe=$_POST["societe"];
	$obj->ref=$_POST["ref"];
	$obj->total_ht=$_POST["total_ht"];
	$obj->total_ttc=$_POST["total_ttc"];
	$obj->tvarate=$_POST["tvarate"];
	$obj->payment=$_POST["payment"];
	$obj->datec=strftime("%Y-%m-%d %H:%M:%S");
	$obj->dated=$_POST["reyear"]."-".$_POST["remonth"]."-".$_POST["reday"];
	$obj->note=$_POST["note"];
	$id=$obj->create($user);
	
	if ($id > 0)
	{
		Header ( "Location: fiche.php?id=".$id);
				exit;
		// Creation OK
	}
	else
	{
		// Creation KO
		$mesg=$obj->error;
	}
	
	}
	else 
	{
		Header ( "Location: index.php");
		exit;
	}
	
	
	
}





/***************************************************
* PAGE
****************************************************/

$sql = "SELECT";
		$sql.= " d.rowid,";
		$sql.= " d.type,";
		$sql.= " d.societe,";
		$sql.= " d.ref,";
		$sql.= " d.total_ht,";
		$sql.= " d.total_ttc,";
		$sql.= " d.date_lim_reglement,";
		$sql.= " d.paye,";
		$sql.= " d.fk_statut,";
		$sql.= " d.fk_soc,";
		$sql.= " d.dated ";
		//...
        $sql.= " FROM ".MAIN_DB_PREFIX."depenses as d";
        if (trim($search_ref) != '')
        {
        $sql.= ' WHERE d.ref LIKE \'%'.$db->escape(trim($search_ref)) . '%\'';
        }
        if (trim($search_ref) != '')
        {
        $sql.= ' AND d.total_ttc LIKE \'%'.$db->escape(trim($search_ttc)) . '%\'';	
        }
        $sql.= $db->order($sortfield,$sortorder);
        $sql.= $db->plimit($limit + 1 ,$offset);

$depensesjs=array("/factures_depenses/js/lib.depenses.js");

llxHeader('','Depenses','','','','',$depensesjs,'',0,0);

$now=gmmktime();
$form=new Form($db);

$obj = new Depenses($db);




$resql=$db->query($sql);
if ($resql){
	

$num=$db->num_rows($resql);
// Pagination
print_barre_liste($langs->trans("ListDepenses"), $page, $_SERVER["PHP_SELF"],"&socid=$socid",$sortfield,$sortorder,'',$num);
$i=0;
print '<form method="get" action="'.$_SERVER["PHP_SELF"].'">'."\n";
print '<table width=100%>';
print "<tr class=\"liste_titre\">";
    print_liste_field_titre($langs->trans("ID"), $_SERVER["PHP_SELF"],
			"d.rowid", "", "&socid=$socid", '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Type"),$_SERVER["PHP_SELF"],"d.type","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Societe"),$_SERVER["PHP_SELF"],"d.societe","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Reference"),$_SERVER["PHP_SELF"],"d.dated","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Date"),$_SERVER["PHP_SELF"],"d.dated","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("DatePaiement"),$_SERVER["PHP_SELF"],"d.date_lim_reglement","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("TotalHT"),$_SERVER["PHP_SELF"],"d.total_ht","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("TotalTTC"),$_SERVER["PHP_SELF"],"d.total_ttc","","&socid=$socid",'',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"fk_statut,paye","","&socid=$socid",'',$sortfield,$sortorder);
    print "</tr>\n";
    // Filters lines
    print '<tr class="liste_titre">';
    print '<td class="liste_titre">';
    //print '<input class="flat" size="10" type="text" name="search_ref" value="'.$search_ref.'">';
    print '</td>';
    
    print '<td class="liste_titre">';
    //print '<input class="flat" size="10" type="text" name="search_company" value="'.$search_company.'">';
    print '</td>';
    
    print '<td class="liste_titre">';
    print '<input class="flat" size="10" type="text" name="search_ref" value="'.$search_ref.'">';
    print '</td>';
    
    print '<td class="liste_titre" align="left">';
    print '&nbsp;';
    print '</td>';
    
    print '<td class="liste_titre" align="right">';
    print '&nbsp;';
    print '</td>';
    
    print '<td class="liste_titre" align="right">';
    print '&nbsp;';
    print '</td>';
    
    
    print '<td class="liste_titre" align="right">';    
    //print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
    print '<input class="flat" size="10" type="text" name="total_ttc" value="'.$search_ttc.'">';
    print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
    print "</td>";
    
    print '<td class="liste_titre" align="right">';
    print '&nbsp;';
    print '</td>';
    
print '<td class="liste_titre" align="right">';
	print '&nbsp;';
	print '</td>';

    print "</tr>\n";
    
    $supplierstatic=new Fournisseur($db);
    
while ($i < min($num,$limit))
{
	

	//$depenses=$obj->db->fetch_object($res);
	$depenses=$db->fetch_object($resql);
	$id=$depenses->rowid;
	$supplierstatic->id=$depenses->fk_soc;
	$supplierstatic->nom=$depenses->societe;
	$obj->id= $depenses->rowid;
	
	print '<tr>';
print '<td><a href="fiche.php?id=' . $id . '">'
						. img_object($langs->trans("ShowTrip"),
								"depenses@factures_depenses") . ' '
						. strtoupper($id) . '</a></td>';
	
/*print '<td><a href="fiche.php?id='.$id.'">'.img_object($langs->trans("ShowTrip"),"depenses@factures_depenses").' '.strtoupper($depenses->type).'</a></td>';*/
print '<td>' . strtoupper($depenses->type) . '</td>';
	print '<td>';
	print $supplierstatic->getNomUrl(1,'',12);
	//print strtoupper($depenses->societe);
	print '</td>';
	print '<td>'.strtoupper($depenses->ref).'</td>';
	print '<td>'.$depenses->dated.'</td>';
	print '<td>'.$depenses->date_lim_reglement;
	if (($depenses->paye == 0) && ($depenses->fk_statut > 0) && $db->jdate($depenses->date_lim_reglement) < ($now)) print img_picto($langs->trans("Late"),"warning");
	print '</td>';
	print '<td>'.$depenses->total_ht.'</td>';
	print '<td>'.$depenses->total_ttc.'</td>';
	// Affiche statut de la facture
	print '<td align="right" nowrap="nowrap">';
	// TODO  le montant deja paye obj->am n'est pas definie
	print $obj->LibStatut($depenses->paye,$depenses->fk_statut,5,$objp->am);
	print '</td>';
	print '</tr>';
	$total+=$depenses->total_ht;
	$total_ttc+=$depenses->total_ttc;
	$i++;
	if ($i == min($num,$limit))
	{
		// Print total
		print '<tr class="liste_total">';
		print '<td class="liste_total" colspan="5" align="left">'.$langs->trans("Total").'</td>';
		print '<td class="liste_total" align="left"></td>';
		print '<td class="liste_total" align="left">'.price($total).'</td>';
		print '<td class="liste_total" align="left">'.price($total_ttc).'</td>';
		print '<td class="liste_total" align="center">&nbsp;</td>';
		print '</tr>';
	}
}
print "</form>\n";
print '</table>';

}

	else
	{
		dol_print_error($db);
	}
	


$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
?>
