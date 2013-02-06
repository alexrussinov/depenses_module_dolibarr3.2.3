<?php


require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/depenses.class.php");
require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');

$filt = $_GET["type_filter"];
//$filt = "Carburant";

global $conf,$user,$langs, $db;

$langs->load("depenses");

// On recherche les societes pour le type choisi 
$sql = "SELECT s.rowid, s.nom, s.client, s.fournisseur, s.code_client, s.code_fournisseur";
$sql.= " FROM ".MAIN_DB_PREFIX ."societe as s";
$sql.= ",".MAIN_DB_PREFIX ."categorie_fournisseur as cf ";
$sql .= "LEFT JOIN ".MAIN_DB_PREFIX."categorie as c ON c.rowid=cf.fk_categorie";
$sql.= " WHERE s.fournisseur = 1";
$sql.= " AND c.rowid = '".$filt."'";
$sql.= " AND s.rowid =cf.fk_societe";
$sql.= " ORDER BY nom ASC";

dol_syslog("Form::select_societes sql=".$sql);
$resql=$db->query($sql);
if ($resql)
{
	
	print '<select id="soc" style="width:130px" class="flat" name="societe_id" form="cr_depense" onchange="showInput()">';
	print '<option value="-1">&nbsp;</option>';
	$num = $db->num_rows($resql);
	$i = 0;
	if ($num)
	{
		while ($i < $num)
		{
			$obj = $db->fetch_object($resql);
			$label=$obj->nom;

			if ($selected > 0 && $selected == $obj->rowid)
			{
				print'<option value="'.$obj->rowid.'" selected="selected">'.$label.'</option>';
			}
			else
			{
				print '<option value="'.$obj->rowid.'">'.$label.'</option>';
			}
			$i++;
		}
	}
	print '<option id="CrNewCom" value="1">'.$langs->trans("CreateNewCompany").'</option>';
	print '</select>';
	
	print '&nbsp;&nbsp;&nbsp;<input id="soc_text" type="hidden" name="societe_name" form="cr_depense">';
}
else
{
	dol_print_error($db);
}
