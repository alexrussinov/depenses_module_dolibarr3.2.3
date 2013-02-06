<?php
require("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/depenses.class.php");

/*
 * Actions
 */

if ($_GET["action"]=='delete'|| $_POST["action"]=='delete')
{
	$id=GETPOST("id");
	$obj= new Depenses($db);
	if ($id>0)
	{
		$r=$obj->delete_type($id);
	}
}

if ($_GET["action"]=='add'|| $_POST["action"]=='add')
{
	$type=$_GET["type"];

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."depenses_type(";
		$sql.= " type ";
        $sql.= ") VALUES (";
        $sql.= " '".$type."'";
		$sql.= ")";
		
        $resql=$db->query($sql);
        if (!$resql)
        {
        	//Error handler
        }
}
/*
 * View
 */

$langs->Load("depenses");

$depensesjs=array("/factures_depenses/js/lib.depenses.js");


llxHeader("",$langs->trans("ChargesSetup"),'','','','',$depensesjs,'',0,0);

$html=new Form($db);

$obj = new Depenses($db);

print '<table>';


$resql=$obj->gettype();

if ($resql)
{

$numrows=$db->num_rows($resql);	
$i=0;

 print '<tr class="liste_titre">';
 print '<td class="liste_titre" align="center" width="40">'.$langs->trans("Id").'</td>';
 print '<td class="liste_titre" align="center" width="120">'.$langs->trans("Type").'</td>';
 print '</tr>';
while ($i<$numrows)
{
	$typ=$db->fetch_object($resql);
	$id=$typ->rowid;
	$type=$typ->type;
	
	print '<tr>';
	print '<td>'.$id.'</td>';
	print '<td align="right">'.$type.'</td>';
	print '<td><a href="depensessetup.php?id='.$id.'&action=delete">Supprimer</a></td>';
	print '</tr>';
	
	$i++;
}

}
print '</table>';
print '<br><br>';
print '<a class="button" onclick="adtype()">'.$langs->trans("Add").'</a>';
print '<div id="my"></div>';








$db->close();

llxFooter('$Date: 2011/07/31 22:23:25 $ - $Revision: 1.150 $');
?>