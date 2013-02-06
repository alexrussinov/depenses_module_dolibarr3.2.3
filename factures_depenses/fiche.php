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
 *   	\file       factures_depenses/fiche.php
 *		\ingroup    depenses module
 *		\brief      Page to create / see an invoice of charges
 *		\version    $Id: fiche.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */



require("../main.inc.php");

// Change this following line to use the correct relative path from htdocs (do not remove DOL_DOCUMENT_ROOT)
require_once(DOL_DOCUMENT_ROOT."/factures_depenses/class/depenses.class.php");
require_once(DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php');
require_once(DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php');

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("depenses");


// Get parameters
$id=isset($_GET["id"])?$_GET["id"]:$_POST["id"];

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}



/*******************************************************************
* ACTIONS
********************************************************************/

// Add new invoice in to the database
if ($_GET["action"] == 'add' || $_POST["action"] == 'add')
{
	if (! $_POST["cancel"])
	{
	global $langs, $user;
	
	$obj =new Depenses($db);
	$soc = new Societe($db);
	$facfou = new FactureFournisseur($db);
	
	// Determine name of a Company
	if(GETPOST("societe_id"))
	{
	$socid = GETPOST("societe_id");
	$soc->fetch($socid);
	$soc_name=$soc->name;
	}
	else 
		$soc_name=$_POST["societe_name"];
	
	$obj->categorie_id=$_POST["categorie_id"];
	
	$obj->type=$obj->showType($obj->categorie_id); // Name of a categorie
	$obj->societe=$soc_name; // Name of a company
	$obj->ref=$_POST["ref"];
	$obj->total_ht=$_POST["total_ht"];
	$obj->total_ttc=$_POST["total_ttc"];
	$obj->tvarate=$_POST["tvarate"];
	$obj->payment=$_POST["payment"];
	$obj->datec=strftime("%Y-%m-%d %H:%M:%S");
	$obj->dated=$_POST["reyear"]."-".$_POST["remonth"]."-".$_POST["reday"];
	$obj->date_echeance=$_POST["echyear"]."-".$_POST["echmonth"]."-".$_POST["echday"];
	$obj->note=$_POST["note"];
	$obj->tva_amounts= array("tva0_0"=>$_POST["tvarate0"],
	                         "tva2_1"=>$_POST["tva2_1"]?$_POST["tva2_1"]:0, 
	                         "tva5_5"=>$_POST["tva5_5"]?$_POST["tva5_5"]:0, 
	                         "tva7_0"=>$_POST["tva7_0"]?$_POST["tva7_0"]:0, 
	                         "tva19_6"=>$_POST["tva19_6"]?$_POST["tva19_6"]:0
			                );
	$obj->fk_soc=$socid;
	

	// Creation of the new Third party
	if($_POST["societe_name"] && $_POST["societe_name"]!='')
	{
	$soc->client=3;
	$soc->fournisseur=1;
	$soc->name=$soc->nom=$obj->societe;
	$id_cat=$obj->getCategorieid($obj->type);
	$soc->fournisseur_categorie = $id_cat;
	$r=$soc->create($user);
	  if ($r>0)
	  {
		$obj->fk_soc=$soc->id;
	  }
	}
	
	// Creation automatic la facture fournisseur
	
	/*$facfou->ref           = $obj->ref;
	$facfou->socid         = $obj->fk_soc;
	$facfou->date          = dol_mktime(12, 0 , 0,
                                         $_POST['remonth'],
                                         $_POST['reday'],
                                         $_POST['reyear']);
    $facfou->type =1;*/
    //$facfou->addline($obj->type,);
	//$facfou->date_echeance = $datedue;
	//$facfouid = $facfou->create($user);

	
	//
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


// Update invoice in the database 
if ($_GET["action"] == 'update' || $_POST["action"] == 'update')
{
	if (! $_POST["cancel"])
	{
	$id=GETPOST("id");
	$socid = GETPOST("socid");
	
	global $langs;
	$obj =new Depenses($db);
	$obj->fetch($id);
	
	$soc = new Societe($db);
	$soc->fetch($obj->fk_soc);
	
	$obj->$id=$id;
	//$obj->type=$_POST["type"];
	//$obj->societe=$_POST["societe"];
	$obj->ref=$_POST["ref"];
	//$obj->total_ht=$_POST["total_ht"];
	$obj->total_ttc=$_POST["total_ttc"];
	$obj->tvarate=$_POST["tvarate"];
	$obj->payment=$_POST["payment"];
	$obj->datec=strftime("%Y-%m-%d %H:%M:%S");
	$obj->dated=$_POST["reyear"]."-".$_POST["remonth"]."-".$_POST["reday"];
	$obj->note=$_POST["note"];
	$obj->tva_amounts= array("tva0_0"=>$_POST["tvarate0"]?$_POST["tvarate0"]:'null',
	                         "tva2_1"=>($_POST["tva2_1"]>0)?$_POST["tva2_1"]:0, 
	                         "tva5_5"=>($_POST["tva5_5"]>0)?$_POST["tva5_5"]:0, 
	                         "tva7_0"=>($_POST["tva7_0"]>0)?$_POST["tva7_0"]:0, 
	                         "tva19_6"=>($_POST["tva19_6"]>0)?$_POST["tva19_6"]:0
	                         );
	// Update of the new Third party
	$soc->client=3;
	$soc->fournisseur=1;
	$soc->name=$soc->nom=$obj->societe;
	$id_cat=$obj->getCategorieid($obj->type);
	$soc->fournisseur_categorie = $id_cat;
	$r=$soc->update($obj->fk_soc);
	if ($r>0)
	{
		$obj->fk_soc=$soc->id;
	}
	
	$id=$obj->update($user);
	
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

// Delete invoice from the database
if ($_GET["action"] == 'delete' || $_POST["action"] == 'delete')
{
	if (! $_POST["cancel"])
	{
	global $langs;
	$obj =new Depenses($db);
	$obj->id=GETPOST("id");
	$r=$obj->delete($user);
	
	if ($r == 1)
	{
		Header ( "Location: index.php");
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

$depensesjs=array("/factures_depenses/js/lib.depenses.js");

llxHeader('','Depenses','','','','',$depensesjs,'',0,0);


$form=new Form($db);

// Invoice creation

if ($_GET["action"] == 'create' || $_POST["action"] == 'create')
{
	global $langs;
	$langs->Load('depenses');
	$langs->Load('test@factures_depenses');
	$datec = dol_mktime(12, 0, 0,
	$_POST["remonth"],
	$_POST["reday"],
	$_POST["reyear"]);
	
	$obj = new Depenses($db);

	
	print_fiche_titre($langs->trans("NewCharges"));
		
	print '<table class="border" width="100%">';
	print '<form id="cr_depense" name=create_depense method="post" action='.$_SERVER['PHP_SELF'].'?action=add>';
	print '<input type="hidden" name="action" value="add">';
	//Type
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Type").'</td>';
	print '<td>';
	print '<select style="width:130px" class="flat" name="categorie_id" id="type" onchange="getcompany()">';
	/*
	print '<option value="'.$langs->trans("Fuel").'">'.$langs->trans("Fuel").'</option>';
	print '<option value="'.$langs->trans("Road").'">'.$langs->trans("Road").'</option>';
	print '<option value="'.$langs->trans("Resto").'">'.$langs->trans("Resto").'</option>';
	print '<option value="'.$langs->trans("Trans").'">'.$langs->trans("Trans").'</option>';
	print '<option value="'.$langs->trans("Mail").'">'.$langs->trans("Mail").'</option>';
	print '<option value="'.$langs->trans("Others").'">'.$langs->trans("Others").'</option>';
	*/
	print '<option value=""></option>';
	$res=$obj->gettype();
	if ($res)
	{
		$numrows=$db->num_rows($res);	
        $i=0;
        while ($i<$numrows)
        {
        	$typ=$db->fetch_object($res);
        	$obj->categorie_id=$obj->getCategorieid($typ->type);
        	print '<option value="'.$obj->categorie_id.'">'.$typ->type.'</option>';
        	$i++;
        }
	}
	print '</select>';
	

	//print '&nbsp;&nbsp;&nbsp;&nbsp';
	//print '<a href="" onclick="adtype()">Create new type</a>';
	print '</td>';
	print '</tr>';
	// Societe
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Company").'</td>';
	print '<td id="company">';
	//print $obj->select_company((empty($_GET['socid'])?'':$_GET['socid']),'socid','s.fournisseur = 1',1);
	print '<select style="width:130px" class="flat" disabled><option></option><option>Creer nouvelle societe</option></select>';
	//print '<input id="societe" type="hidden" name="societe">';
	print '</td>';
	print '</tr>';
	// Reference
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Reference").'</td>';
	print '<td>';
	print '<input type="text" name="ref">';
	print '</td>';
	print '</tr>';
	// Total HT
	/*
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("TotalHT").'</td>';
	print '<td>';
	print '<input type="text" name="total_ht" onkeyup="return numbersinput(this);">';
	print '</td>';
	print '</tr>';
	*/
	// Total TTC
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("TotalTTC").'</td>';
	print '<td>';
	print '<input type="text"  name="total_ttc" onkeyup="return numbersinput(this);">';
	print '</td>';
	print '<tr>';
	// TVA
	print '<tr valign="top">';
	print '<td class="fieldrequired">'.$langs->trans("TVA").'</td>';
    print '<td>';


	print '<input type="checkbox" name="tvarate0" value="0"> 0%<br><br>';
    
	
    
	print '<input id="t2_1" type="checkbox" name="tvarate2_1" value="2.1" onclick="check(this)"> 2,1%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
    print '<input id="tva2_1" type="hidden"  name="tva2_1" onkeyup="return numbersinput(this);"><br><br>';

    

    print '<input id="t5_5" type="checkbox" name="tvarate5_5" value="5.5" onclick="check(this)"> 5,5%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
    print '<input id="tva5_5" type="hidden"  name="tva5_5" onkeyup="return numbersinput(this);"><br><br>';

    
	
    print '<input id="t7_0" type="checkbox" name="tvarate7_0" value="7.0" onclick="check(this)"> 7,0%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
    print '<input id="tva7_0" type="hidden"  name="tva7_0" onkeyup="return numbersinput(this);"><br><br>';

    
	
    print '<input id="t19_6" type="checkbox" name="tvarate19_6" value="19.6" onclick="check(this)"> 19,6%&nbsp;&nbsp;&nbsp';
    print '<input id="tva19_6" type="hidden"  name="tva19_6" onkeyup="return numbersinput(this);"><br>';
   
    
 
	print '</td>';

	print '</tr>';
	// Payment
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Payment").'</td>';
	print '<td>';
	print '<select name="payment">';
	print '<option value="'.$langs->trans("CB").'">'.$langs->trans("CB").'</option>';
	print '<option value="'.$langs->trans("Cheque").'">'.$langs->trans("Cheque").'</option>';
	print '<option value="'.$langs->trans("Virement").'">'.$langs->trans("Virement").'</option>';
	print '<option value="'.$langs->trans("Cash").'">'.$langs->trans("Cash").'</option>';
	print '<option value="'.$langs->trans("Other").'">'.$langs->trans("Other").'</option>';
	print '</select>';
	print '</td>';
	print '</tr>';
	
	// DAte
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Date").'</td>';
	print '<td>';
	print $form->select_date($datec?$datec:-1,'','','','','add',1,1);
	print '</td>';
	print '<tr>';
	
	//Date limite of payment
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("DatePayement").'</td>';
	print '<td>';
	print $form->select_date($datep?$datep:-1,'ech','','','','add',1,1);
	print '</td>';
	print '<tr>';
	// Note
	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans("Note").'</td>';
	print '<td>';
	print '<textarea name="note"></textarea>';
	print '</td>';
	print '</tr>';
	print '</table>';
	print '<br>';
	print '<center><input class="button" type="submit" value="'.$langs->trans("Create").'"> &nbsp; &nbsp; ';
	print '<input class="button" type="submit" name="cancel" value="'.$langs->trans("Cancel").'"></center>';
	print '</forme>';
	
	
}

else 
 

// View of predefined invoice

if($id)
{
	$obj = new Depenses($db);
	
	$result=$obj->fetch($id);
	$langs->Load('depenses');
	$langs->Load('test@factures_depenses');
	
	if ($result>0)
	{
		$head[$h][0] = DOL_URL_ROOT."/factures_depenses/fiche.php?id=$obj->id";
			$head[$h][1] = $langs->trans("Card");
			$head[$h][2] = 'card';
			$h++;

			$head[$h][0] = DOL_URL_ROOT."/factures_depenses/note.php?id=$obj->id";
			$head[$h][1] = $langs->trans("Note");
			$head[$h][2] = 'note';
			$h++;
			dol_fiche_head($head, 'card', $langs->trans("DepensesCard"), 0, 'depenses@factures_depenses');
			
			// Edition of the invoice
			if (GETPOST("action")=='edit')
			{
				global $langs;
				print '<table class="border" width="100%">';
				print '<form name=create_depense method="post" action='.$_SERVER['PHP_SELF'].'>';
				print '<input type="hidden" name="action" value="update">';
				print '<input type="hidden" name="id" value="'.$id.'">';
				
				//Type
				
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("Type").'</td>';
				print '<td>';
				//print '<select name="type">';
				/*
				$type= $langs->transnoentitiesnoconv("Fuel");
				if ($obj->type == "$type")
				print '<option value="'.$langs->trans("Fuel").'" selected>'.$langs->trans("Fuel").'</option>';
				else 
				print '<option value="'.$langs->trans("Fuel").'">'.$langs->trans("Fuel").'</option>';
				
				$type= $langs->transnoentitiesnoconv("Road");
				if ($obj->type== "$type")
				print '<option value="'.$langs->trans("Road").'" selected>'.$langs->trans("Road").'</option>';
				else 
				print '<option value="'.$langs->trans("Road").'">'.$langs->trans("Road").'</option>';
				
				$type= $langs->transnoentitiesnoconv("Resto");
				if ($obj->type== "$type")
				print '<option value="'.$langs->trans("Resto").'" selected>'.$langs->trans("Resto").'</option>';
				else 
				print '<option value="'.$langs->trans("Resto").'">'.$langs->trans("Resto").'</option>';
				
				$type= $langs->transnoentitiesnoconv("Trans");
				if ($obj->type== "$type")
				print '<option value="'.$langs->trans("Trans").'" selected>'.$langs->trans("Trans").'</option>';
				else 
				print '<option value="'.$langs->trans("Trans").'">'.$langs->trans("Trans").'</option>';
				
                $type= $langs->transnoentitiesnoconv("Mail");
				if ($obj->type== "$type")
				print '<option value="'.$langs->trans("Mail").'" selected>'.$langs->trans("Mail").'</option>';
				else 
				print '<option value="'.$langs->trans("Mail").'">'.$langs->trans("Mail").'</option>';
				
				$type= $langs->transnoentitiesnoconv("Others");
				if ($obj->type== "$type")
				print '<option value="'.$langs->trans("Others").'" selected>'.$langs->trans("Others").'</option>';
				else 
				print '<option value="'.$langs->trans("Others").'">'.$langs->trans("Others").'</option>';
				
			    $res=$obj->gettype();
	            if ($res)
	              {
		           $numrows=$db->num_rows($res);	
                   $i=0;
                   while ($i<$numrows)
                   {
        	       $typ=$db->fetch_object($resql);
        	       if ($obj->type==$typ->type)
        	       print '<option value="'.$typ->type.'" selected>'.$typ->type.'</option>';
        	       else 
        	       print '<option value="'.$typ->type.'">'.$typ->type.'</option>';
        	       $i++;
                   }
	              }
				
				print '</select>';
				*/
				print $obj->type;
				print '</td>';
				print '</tr>';

				// Societe
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("Company").'</td>';
				print '<td>';
				//print '<input type="text" name="societe" value="'.$obj->societe.'">';
				print $obj->societe;
				print '</td>';
				print '</tr>';
				// Reference
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("Reference").'</td>';
				print '<td>';
				print '<input type="text" name="ref" value="'.$obj->ref.'">';
				print '</td>';
				print '</tr>';
				// Total HT
				/*
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("TotalHT").'</td>';
				print '<td>';
				print '<input type="text" name="total_ht" value="'.$obj->total_ht.'" onkeyup="return numbersinput(this);">';
				print '</td>';
				print '</tr>';*/
				// Total TTC
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("TotalTTC").'</td>';
				print '<td>';
				print '<input type="text"  name="total_ttc" value="'.$obj->total_ttc.'" onkeyup="return numbersinput(this);">';
				print '</td>';
				print '<tr>';
				// TVA
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("TVA").'</td>';
			    print '<td>';
			    
			    if ($obj->tva_amounts["tva0_0"]===0)
			    {
				print '<input type="checkbox" name="tvarate0" value="0" checked> 0%<br>';
				//print '<input type="text"  name="tva2_1" onkeyup="return numbersinput(this);" value="'.$obj->tva_amounts["tva0_0"].'"><br>';
			    }
				else
				print '<input type="checkbox" name="tvarate0" value="0"> 0%<br>'; 
				
			    if ($obj->tva_amounts["tva2_1"]!=0)
			    {
				print '<input id="t2_1" type="checkbox" name="tvarate2_1" value="2.1" checked onclick="check(this)"> 2,1%';
				print '<input id="tva2_1" type="text"  name="tva2_1" onkeyup="return numbersinput(this);" value="'.$obj->tva_amounts["tva2_1"].'"><br>';
			    }
			    else 
			    {
			    print '<input id="t2_1" type="checkbox" name="tvarate2_1" value="2.1" onclick="check(this)"> 2,1%';
			    print '<input id="tva2_1" type="hidden"  name="tva2_1" onkeyup="return numbersinput(this);"><br>';
			    }
			    
				if ($obj->tva_amounts["tva5_5"]!=0)
			    {
				print '<input id="t5_5" type="checkbox" name="tvarate5_5" value="5.5" checked onclick="check(this)"> 5,5%';
				print '<input id="tva5_5" type="text"  name="tva5_5" onkeyup="return numbersinput(this);" value="'.$obj->tva_amounts["tva5_5"].'"><br>';
			    }
			    else 
			    {
			    print '<input id="t5_5" type="checkbox" name="tvarate5_5" value="5.5" onclick="check(this)"> 5,5%';
			    print '<input id="tva5_5" type="hidden"  name="tva5_5" onkeyup="return numbersinput(this);"><br>';
			    }
			    
			    if ($obj->tva_amounts["tva7_0"]!=0)
			    {
				print '<input id="t7_0" type="checkbox" name="tvarate7_0" value="7.0" checked onclick="check(this)"> 7,0%';
				print '<input id="tva7_0" type="text"  name="tva7_0" onkeyup="return numbersinput(this);" value="'.$obj->tva_amounts["tva7_0"].'"><br>';
			    }
			    else 
			    {
			    print '<input id="t7_0" type="checkbox" name="tvarate7_0" value="7.0" onclick="check(this)"> 7,0%';
			    print '<input id="tva7_0" type="hidden"  name="tva7_0" onkeyup="return numbersinput(this);"><br>';
			    }
			    
			    if ($obj->tva_amounts["tva19_6"]!=0)
			    {
				print '<input id="t19_6" type="checkbox" name="tvarate19_6" value="19.6" checked onclick="check(this)"> 19,6%';
				print '<input id="tva19_6" type="text"  name="tva19_6" onkeyup="return numbersinput(this);" value="'.$obj->tva_amounts["tva19_6"].'"><br>';
			    }
			    else 
			    {
			    print '<input id="t19_6" type="checkbox" name="tvarate19_6" value="19.6" onclick="check(this)"> 19,6%';
			    print '<input id="tva19_6" type="hidden"  name="tva19_6" onkeyup="return numbersinput(this);"><br>';
			    }
				
				print '</td>';
				print '</tr>';
				// Payment
				
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("Payment").'</td>';
				print '<td>';
				print '<select name="payment">';
				$payment= $langs->transnoentitiesnoconv("CB");
				if ($obj->payment== "$payment")
				print '<option value="'.$langs->trans("CB").'" selected>'.$langs->trans("CB").'</option>';
				else
				print '<option value="'.$langs->trans("CB").'">'.$langs->trans("CB").'</option>';
				
				$payment= $langs->transnoentitiesnoconv("Cheque");
				if ($obj->payment== "$payment")
				print '<option value="'.$langs->trans("Cheque").'" selected>'.$langs->trans("Cheque").'</option>';
				else 
				print '<option value="'.$langs->trans("Cheque").'">'.$langs->trans("Cheque").'</option>';
				
				$payment= $langs->transnoentitiesnoconv("Transfer");
				if ($obj->payment== "$payment")
				print '<option value="'.$langs->trans("Transfer").'" selected>'.$langs->trans("Transfert").'</option>';
				else
				print '<option value="'.$langs->trans("Transfer").'">'.$langs->trans("Transfer").'</option>'; 
				
				$payment= $langs->transnoentitiesnoconv("Cash");
				if ($obj->payment== "$payment")
				print '<option value="'.$langs->trans("Cash").'" selected>'.$langs->trans("Cash").'</option>';
				else
				print '<option value="'.$langs->trans("Cash").'">'.$langs->trans("Cash").'</option>';
				
				$payment= $langs->transnoentitiesnoconv("Others");
				if ($obj->payment == "$payment")
				print '<option value="'.$langs->trans("Others").'" selected>'.$langs->trans("Other").'</option>';
				else
				print '<option value="'.$langs->trans("Others").'">'.$langs->trans("Others").'</option>'; 
				print '</td>';
				print '</tr>';

				// DAte
				print '<tr>';
				print '<td class="fieldrequired">'.$langs->trans("Date").'</td>';
				print '<td>';
				$date_fac=dol_stringtotime($obj->dated);
			
				print $form->select_date($date_fac,'','','','','add',1,1);
				print '</td>';
				print '<tr>';
		
				print '</table>';
				print '<br>';
				print '<center><input class="button" type="submit" value="'.$langs->trans("Save").'"> &nbsp; &nbsp; ';
				print '<input class="button" type="submit" name="cancel" value="'.$langs->trans("Cancel").'"></center>';
				print '</forme>';
				
			}
			
	        else 
			{
	        print '<table class="border" width="100%">';
 
        	//Type
	        print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("Type").'</td>';
			print '<td>';
			print $obj->type;			
			print '</td>';
			print '</tr>';
			// Societe
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("Company").'</td>';
			print '<td>';
			print $obj->societe;
			print '</td>';
			print '</tr>';
			// Reference
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("Reference").'</td>';
			print '<td>';
			print $obj->ref;
			print '</td>';
			print '</tr>';
			
			// DAte
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("Date").'</td>';
			print '<td>';
			print $obj->dated;
			print '</td>';
			print '<tr>';
			
			// DAte paiement
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("DatePaiement").'</td>';
			print '<td>';
			print $obj->date_echeance;
			print '</td>';
			print '<tr>';
			
			// Total HT
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("TotalHT").'</td>';
			print '<td>';
			print $obj->total_ht;
			print '</td>';
			print '</tr>';
			// Total TTC
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("TotalTTC").'</td>';
			print '<td>';
			print $obj->total_ttc;
			print '</td>';
			print '<tr>';
			// TVA
			print '<tr>';
			print '<td class="fieldrequired">';
			print $langs->trans("TVA").'&nbsp0&nbsp%&nbsp:<br>';
			print $langs->trans("TVA").'&nbsp2,1&nbsp%&nbsp:<br>';
			print $langs->trans("TVA").'&nbsp5,5&nbsp%&nbsp:<br>';
			print $langs->trans("TVA").'&nbsp7,0&nbsp%&nbsp:<br>';
			print $langs->trans("TVA").'&nbsp19,6&nbsp%&nbsp:';
			print '</td>';
   		    print '<td>';
   		    foreach ($obj->tva_amounts as $tv)
			print $tv.'<br>';
			print '</td>';
			print '</tr>';
			// TVA total
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("TotalTVA").'</td>';
   		    print '<td>';
			print $obj->total_tva;
			print '</td>';
			print '</tr>';
			// Payment
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("Payment").'</td>';
			print '<td>';
			print $obj->payment;
			print '</td>';
			print '</tr>';
			
			// Status
			print '<tr>';
			print '<td class="fieldrequired">'.$langs->trans("Status").'</td>';
			print '<td>';
			print $obj->LibStatut($obj->paye,$obj->fk_statut,5,$objp->am);
			print '</td>';
			print '<tr>';
			
			print '</table>';
			print '</div>';
			}
	}
		else 
		{
			dol_print_error($db);
		}


/*
 * Barre d'actions
 *
 */

print '<div class="tabsAction">';

if ($_GET["action"] != 'create' && $_GET["action"] != 'edit')
{
	if ($user->rights->depenses->w->creer)
	{
		print '<a class="butAction" href="fiche.php?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
	}
	if ($user->rights->depenses->d->supprimer)
	{
		print '<a class="butActionDelete" href="fiche.php?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
	}
}

if ($_GET['action'] != 'edit' && $user->societe_id == 0)
{
	print '<a class="butAction" href="paymentdep.php?facid='.$obj->id.'&amp;action=create">'.$langs->trans('DoPayment').'</a>';
}
print '</div>';
	
}

$db->close();
llxFooter('$Date: 2011/07/31 22:21:57 $ - $Revision: 1.19 $');
?>