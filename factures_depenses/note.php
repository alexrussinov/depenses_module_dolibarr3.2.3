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
 *   	\file       factures_depenses/note.php
 *		\ingroup    depenses module
 *		\brief      Page to view / modify note
 *		\version    $Id: note.php,v 1.00 2012/07/26 22:21:57
 *		\author		Alex Russinov
 */
require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/depenses.class.php");

// Load traductions files requiredby by page
$langs->load("companies");


// Get parameters
$id=isset($_GET["id"])?$_GET["id"]:$_POST["id"];

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

$obj = new Depenses($db);

if ($_POST["action"] == 'update')
{
	$db->begin();

	$obj->fetch($_GET["id"]);

	$res=$obj->update_note($_POST["note_public"],$user);
	if ($res < 0)
	{
		$mesg='<div class="error">'.$fac->error.'</div>';
		$db->rollback();
	}
	else
	{
		$db->commit();
	}
}

llxHeader();

$html = new Form($db);
	global $langs;
	$langs->Load('depenses');

$id = GETPOST("id");

if ($id > 0)
{
	$obj = new Depenses($db);
	$obj->fetch($id);

	$soc = new Societe($db, $trip->socid);
    $soc->fetch($trip->socid);

	$h=0;

	        $head[$h][0] = DOL_URL_ROOT."/factures_depenses/fiche.php?id=$obj->id";
			$head[$h][1] = $langs->trans("Card");
			$head[$h][2] = 'card';
			$h++;

			$head[$h][0] = DOL_URL_ROOT."/factures_depenses/note.php?id=$obj->id";
			$head[$h][1] = $langs->trans("Note");
			$head[$h][2] = 'note';
			$h++;
			dol_fiche_head($head, 'note', $langs->trans("DepensesCard"), 0, 'depenses');

            print '<table class="border" width="100%">';

		    if ($_GET["action"] == 'edit')
		    {
		        print '<form method="post" action="note.php?id='.$obj->id.'">';
		        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		        print '<input type="hidden" name="action" value="update">';
		        print '<textarea name="note_public" cols="80" rows="8">'.$obj->note."</textarea><br>";
		        print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
		        print '</form>';
		    }
		    else
		    {
		    	print '<tr><td valign="top">'.$langs->trans("Note").'</td>';
			    print '<td valign="top" colspan="3">';
			    print $obj->note;
			    print "</td></tr>";
		    }

			 print "</table>";
			    /*
    * Actions
    */
    print '</div>';
    print '<div class="tabsAction">';

    if ($user->rights->depenses->w->creer && $_GET["action"] <> 'edit')
    {
        print "<a class=\"butAction\" href=\"note.php?id=$obj->id&amp;action=edit\">".$langs->trans('Modify')."</a>";
    }

    print "</div>";
            
	
}	
		
$db->close();

llxFooter('$Date: 2011/08/03 00:46:35 $ - $Revision: 1.7 $');
?>	