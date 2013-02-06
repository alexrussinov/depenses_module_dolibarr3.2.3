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
 *   	\file       factures_depenses/impayees.php
 *		\ingroup    depenses module
 *		\brief      Page to view list of inpaid invoices
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */
require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.class.php');
require_once(DOL_DOCUMENT_ROOT.'/factures_depenses/class/depenses.class.php');
require_once(DOL_DOCUMENT_ROOT."/compta/paiement/class/paiement.class.php");

if (!$user->rights->facture->lire)
	accessforbidden();

$langs->load("companies");
$langs->load("bills");
$langs->load("depenses");


if ($_GET["socid"]) { $socid=$_GET["socid"]; }

// Security check
if ($user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}


/*
 * View
*/

$now=gmmktime();

llxHeader('',$langs->trans("BillsDepensesUnpaid"));

$facturestatic=new Depenses($db);
$companystatic=new Societe($db);


/***************************************************************************
 *                                                                         *
*                      Mode Liste                                         *
*                                                                         *
***************************************************************************/

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield="f.date_lim_reglement";
if (! $sortorder) $sortorder="ASC";


	$sql = "SELECT s.nom, s.rowid as socid,";
	$sql.= " f.rowid as ref, f.ref as facnumber, f.total_ht, f.total_ttc,";
	$sql.= " f.dated as df, f.date_lim_reglement as datelimite, ";
	$sql.= " f.paye as paye, f.rowid as facid, f.fk_statut";
	$sql.= " ,sum(pf.amount) as am";
	if (! $user->rights->societe->client->voir && ! $socid) $sql .= ", sc.fk_soc, sc.fk_user ";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= ",".MAIN_DB_PREFIX."depenses as f";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."paiementdepenses_facturedep as pf ON f.rowid=pf.fk_facturefourn ";
	$sql.= " WHERE f.fk_soc = s.rowid";
	$sql.= " AND f.paye = 0 AND f.fk_statut = 1";
	if (! $user->rights->societe->client->voir && ! $socid) $sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid) $sql .= " AND s.rowid = ".$socid;

	if ($_GET["filtre"])
	{
		$filtrearr = explode(",", $_GET["filtre"]);
		foreach ($filtrearr as $fil)
		{
			$filt = explode(":", $fil);
			$sql .= " AND " . $filt[0] . " = " . $filt[1];
		}
	}

	if ($_GET["search_ref"])
	{
		$sql .= " AND f.rowid like '%".$_GET["search_ref"]."%'";
	}
	if ($_GET["search_ref_supplier"])
	{
		$sql .= " AND f.ref like '%".$_GET["search_ref_supplier"]."%'";
	}

	if ($_GET["search_societe"])
	{
		$sql .= " AND s.nom like '%".$_GET["search_societe"]."%'";
	}

	if ($_GET["search_montant_ht"])
	{
		$sql .= " AND f.total_ht = '".$_GET["search_montant_ht"]."'";
	}

	if ($_GET["search_montant_ttc"])
	{
		$sql .= " AND f.total_ttc = '".$_GET["search_montant_ttc"]."'";
	}

	if (dol_strlen($_POST["sf_ref"]) > 0)
	{
		$sql .= " AND f.facnumber like '%".$_POST["sf_ref"] . "%'";
	}
	$sql.= " GROUP BY f.ref,s.nom, s.rowid, f.rowid, f.total_ht, f.total_ttc, f.dated, f.date_lim_reglement, f.paye, f.fk_statut";

	$sql.= " ORDER BY ";
	$listfield=explode(',',$sortfield);
	foreach ($listfield as $key => $value) $sql.=$listfield[$key]." ".$sortorder.",";
	$sql.= " f.ref DESC";
	
	$result = $db->query($sql);
	
	if ($result)
	{
		$num = $db->num_rows($result);
	
		if ($socid)
		{
			$soc = new Societe($db);
			$soc->fetch($socid);
		}
	
		$titre=($socid?$langs->trans("BillsDepensesUnpaidForCompany",$soc->nom):$langs->trans("BillsDepensesUnpaid"));
		print_barre_liste($titre,$page,"impayees.php","&amp;socid=$socid",$sortfield,$sortorder,'',0);	// We don't want pagination on this page
		$i = 0;
		print '<table class="liste" width="100%">';
		print '<tr class="liste_titre">';
		print_liste_field_titre($langs->trans("Ref"),$_SERVER["PHP_SELF"],"f.rowid","","&amp;socid=$socid","",$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("RefSupplier"),$_SERVER["PHP_SELF"],"f.facnumber","","&amp;socid=$socid","",$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("Date"),$_SERVER["PHP_SELF"],"f.datef","","&amp;socid=$socid",'align="center"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("DateDue"),$_SERVER["PHP_SELF"],"f.date_lim_reglement","","&amp;socid=$socid",'align="center"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("Company"),$_SERVER["PHP_SELF"],"s.nom","","&amp;socid=$socid","",$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("AmountHT"),$_SERVER["PHP_SELF"],"f.total_ht","","&amp;socid=$socid",'align="right"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("AmountTTC"),$_SERVER["PHP_SELF"],"f.total_ttc","","&amp;socid=$socid",'align="right"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("AlreadyPaid"),$_SERVER["PHP_SELF"],"am","","&amp;socid=$socid",'align="right"',$sortfield,$sortorder);
		print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"fk_statut,paye,am","","&amp;socid=$socid",'align="right"',$sortfield,$sortorder);
		print "</tr>\n";
		
		// Lignes des champs de filtre
		print '<form method="get" action="impayees.php">';
		print '<tr class="liste_titre">';
		print '<td class="liste_titre">';
		print '<input class="flat" size="8" type="text" name="search_ref" value="'.$_GET["search_ref"].'"></td>';
		print '<td class="liste_titre">';
		print '<input class="flat" size="8" type="text" name="search_ref_supplier" value="'.$_GET["search_ref_supplier"].'"></td>';
		print '<td class="liste_titre">&nbsp;</td>';
		print '<td class="liste_titre">&nbsp;</td>';
		print '<td class="liste_titre" align="left">';
		print '<input class="flat" type="text" size="6" name="search_societe" value="'.$_GET["search_societe"].'">';
		print '</td><td class="liste_titre" align="right">';
		print '<input class="flat" type="text" size="8" name="search_montant_ht" value="'.$_GET["search_montant_ht"].'">';
		print '</td><td class="liste_titre" align="right">';
		print '<input class="flat" type="text" size="8" name="search_montant_ttc" value="'.$_GET["search_montant_ttc"].'">';
		print '</td><td class="liste_titre" colspan="2" align="right">';
		print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
		print '</td>';
		print "</tr>\n";
		print '</form>';
		
		
		if ($num > 0)
		{
			$var=True;
			$total_ht=0;
			$total_ttc=0;
			$total_paid=0;
		
			while ($i < $num)
			{
				$objp = $db->fetch_object($result);
		
				$var=!$var;
		
				print "<tr $bc[$var]>";
				$classname = "impayee";
		
				print '<td nowrap>';
						$facturestatic->id=$objp->facid;
						$facturestatic->ref=$objp->ref;
						print $facturestatic->getNomUrl(1);
						print "</td>\n";
		
								print "<td nowrap>".dol_trunc($objp->facnumber,12)."</td>\n";
		
								print "<td nowrap align=\"center\">".dol_print_date($db->jdate($objp->df))."</td>\n";
								print "<td nowrap align=\"center\">".dol_print_date($db->jdate($objp->datelimite));
								if ($db->jdate($objp->datelimite) < ($now - $conf->facture->fournisseur->warning_delay) && ! $objp->paye && $objp->fk_statut == 1) print img_warning($langs->trans("Late"));
								print "</td>\n";
		
								print '<td>';
								$companystatic->id=$objp->socid;
								$companystatic->nom=$objp->nom;
								print $companystatic->getNomUrl(1,'supplier',32);
								print '</td>';
		
								print "<td align=\"right\">".price($objp->total_ht)."</td>";
								print "<td align=\"right\">".price($objp->total_ttc)."</td>";
								print "<td align=\"right\">".price($objp->am)."</td>";
		
								// Affiche statut de la facture
								print '<td align="right" nowrap="nowrap">';
								print $facturestatic->LibStatut($objp->paye,$objp->fk_statut,5,$objp->am);
								print '</td>';
		
								print "</tr>\n";
								$total_ht+=$objp->total_ht;
								$total_ttc+=$objp->total_ttc;
								$total_paid+=$objp->am;
		
								$i++;
			}
		
			print '<tr class="liste_total">';
			print "<td colspan=\"5\" align=\"left\">".$langs->trans("Total").": </td>";
			print "<td align=\"right\"><b>".price($total_ht)."</b></td>";
			print "<td align=\"right\"><b>".price($total_ttc)."</b></td>";
			print "<td align=\"right\"><b>".price($total_paid)."</b></td>";
			print '<td align="center">&nbsp;</td>';
			print "</tr>\n";
		}
		
		print "</table>";
		$db->free();
		}
		else
		{
			dol_print_error($db);
		}
		$db->close();
		
		llxFooter('$Date: 2011/07/31 23:57:01 $ - $Revision: 1.25 $');
		?>