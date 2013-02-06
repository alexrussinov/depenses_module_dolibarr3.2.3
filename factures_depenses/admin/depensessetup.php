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
 *   	\file       admin/depensessetup.php
 *		\ingroup    depenses module
 *		\brief      Page to setup module Depenses
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */
require("../../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/depenses.class.php");
require_once(DOL_DOCUMENT_ROOT."/categories/class/categorie.class.php");
/*
 * Actions
 */

// Delete type from the database
if ($_GET["action"]=='delete'|| $_POST["action"]=='delete')
{
	$id=GETPOST("id");
	$obj= new Depenses($db);
	if ($id>0)
	{
		$r=$obj->delete_type($id);
	}
}
// Insert type in to the database
if ($_GET["action"]=='add'|| $_POST["action"]=='add')
{
	$type=GETPOST("type");
	  
	    if ($type)
	    {
	    	// Create a categorie for a third party that corresponds to a type added with predefined properties  
	    	$cat= new Categorie ($db);
	    	$cat->label = $type;
	    	$cat->type = 1;
	    	$c=$cat->create();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."depenses_type(";
		$sql.= " type ";
        $sql.= ") VALUES (";
        $sql.= " '".$type."'";
		$sql.= ")";
		
        $resql=$db->query($sql);
        $_POST["type"]='';
	     if (!$resql)
         {
        	//Error handler
         }
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

print_fiche_titre($langs->trans("ChargesSetup"));




$resql=$obj->gettype();

if ($resql)
{

$numrows=$db->num_rows($resql);	
$i=0;
 print '<table>';
 print '<tr class="liste_titre">';
 print '<td class="liste_titre" align="center" width="40">'.$langs->trans("Id").'</td>';
 print '<td class="liste_titre" align="center" width="160">'.$langs->trans("Type").'</td>';
 print '</tr>';
 
 // List of the Type
while ($i<$numrows)
{
	$typ=$db->fetch_object($resql);
	$id=$typ->rowid;
	$type=$typ->type;
	
	print '<tr>';
	print '<td align="center">'.$id.'</td>';
	print '<td align="center">'.$type.'</td>';
	
	// Send delete action
	//print '<td align="left"><a href="depensessetup.php?id='.$id.'&action=delete"><img src="'.DOL_DOCUMENT_ROOT.'/factures_depenses/img/minus_icon.png" alt="Supprimer"></a></td>';
	print '<td align="left"><a href="depensessetup.php?id='.$id.'&action=delete">'.img_picto('Supprimer', 'minus_icon@factures_depenses').'</a></td>';
	print '</tr>';
	
	$i++;
}

}
print '</table>';
print '<br><br>';

// Send add action
//print '<a  href="depensessetup.php?action=add"> <img src="plus_icon.png" alt="Ajouter" border="0"> </a>';
//if($_GET["action"]=='add')
//{

  print '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  print '<input type="hidden" name="action" value="add">';
  print '<table>';
 // print '<tr>';
  print '<div class="titre">Ajouter un type:</div>';
 // print '</tr>';
  print '<tr>';
  print '<td><input class="flat" type="text" name="type"></td>&nbsp;&nbsp';
 // print '<td><input class="button" type="image" src="plus_icon_pt.png" alt="Add" border=0></td>';
  print '<td><input class="button" type="image" '.$obj->img_al('xxx','plus_icon_pt@factures_depenses').'></td>';
  print '</tr>';
  print '</table>';
  print '</form>'; 
	
  








$db->close();

llxFooter('$Date: 2011/07/31 22:23:25 $ - $Revision: 1.150 $');
?>