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
 *   	\file       factures_depenses/class/paiementdepenses.class.php
 *		\ingroup    depenses module
 *		\brief      class to manage payements of module depenses
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */

require_once(DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php');
require_once(DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php');


class PaiementDepenses extends Paiement
{
	var $db;
	var $error;
	var $element='payment_supplier';
	var $table_element='paiementdepenses';

	var $id;
	var $ref;
	var $facid;
	var $datepaye;
	var $total;
	var $amount;            // Total amount of payment
	var $amounts=array();   // Array of amounts
	var $author;
	var $paiementid;	// Type de paiement. Stocke dans fk_paiement
	// de llx_paiement qui est lie aux types de
	//paiement de llx_c_paiement
	var $num_paiement;	// Numero du CHQ, VIR, etc...
	var $bank_account;	// Id compte bancaire du paiement
	var $bank_line;		// Id de la ligne d'ecriture bancaire
	var $note;
	var $statut;        //Status of payment. 0 = unvalidated; 1 = validated
	// fk_paiement dans llx_paiement est l'id du type de paiement (7 pour CHQ, ...)
	// fk_paiement dans llx_paiement_facture est le rowid du paiement
   // var $date_creation;
   // var $date_modification;
	/**
	 *    \brief  Constructeur de la classe
	 *    \param  DB          handler acces base de donnees
	 */

	function PaiementDepenses($DB)
	{
		$this->db = $DB ;
	}

	/**
	 *    \brief      Load payment object
	 *    \param      id      id paiement to get
	 *    \return     int     <0 si ko, >0 si ok
	 */
	function fetch($id)
	{
		$sql = 'SELECT p.rowid, p.datep as dp, p.amount, p.statut, p.fk_bank,';
		$sql.= ' c.libelle as paiement_type,';
		$sql.= ' p.num_paiement, p.note, b.fk_account';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'c_paiement as c, '.MAIN_DB_PREFIX.'paiementdepenses as p';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'bank as b ON p.fk_bank = b.rowid ';
		$sql.= ' WHERE p.fk_paiement = c.id';
		$sql.= ' AND p.rowid = '.$id;
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			if ($num > 0)
			{
				$obj = $this->db->fetch_object($resql);
				$this->id             = $obj->rowid;
				$this->ref            = $obj->rowid;
				$this->date           = $this->db->jdate($obj->dp);
				$this->numero         = $obj->num_paiement;
				$this->bank_account   = $obj->fk_account;
				$this->bank_line      = $obj->fk_bank;
				$this->montant        = $obj->amount;
				$this->note           = $obj->note;
				$this->type_libelle   = $obj->paiement_type;
				$this->statut         = $obj->statut;
				$error = 1;
			}
			else
			{
				$error = -2;
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
			$error = -1;
		}
		return $error;
	}

	/**
	 *    Create payment in database
	 *    @param      user        			Object of creating user
	 *    @param       closepaidinvoices   	1=Also close payed invoices to paid, 0=Do nothing more
	 *    @return     int         			id of created payment, < 0 if error
	 */
	function create($user,$closepaidinvoices=0)
	{
		global $langs,$conf;

		$error = 0;

		// Clean parameters
		$this->total = 0;
		foreach ($this->amounts as $key => $value)
		{
			$value = price2num($value);
			$val = round($value, 2);
			$this->amounts[$key] = $val;
			$this->total += $val;
		}
		$this->total = price2num($this->total);


		$this->db->begin();

		if ($this->total <> 0) // On accepte les montants negatifs
		{
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'paiementdepenses (';
			$sql.= 'datec, datep, amount, fk_paiement, num_paiement, note, fk_user_author, fk_bank)';
			$sql.= ' VALUES ('.$this->db->idate(mktime()).',';
			$sql.= " ".$this->db->idate($this->datepaye).", '".$this->total."', ".$this->paiementid.", '".$this->num_paiement."', '".$this->db->escape($this->note)."', ".$user->id.", 0)";

			dol_syslog(get_class($this)."::create sql=".$sql);
			$resql = $this->db->query($sql);
			if ($resql)
			{
				$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'paiementdepenses');

				// Insere tableau des montants / factures
				foreach ($this->amounts as $key => $amount)
				{
					$facid = $key;
					if (is_numeric($amount) && $amount <> 0)
					{
						$amount = price2num($amount);
						$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'paiementdepenses_facturedep (fk_facturefourn, fk_paiementfourn, amount)';
						$sql .= ' VALUES ('.$facid.','. $this->id.',\''.$amount.'\')';
						$resql=$this->db->query($sql);
						if ($resql)
						{
							// If we want to closed payed invoices
							if ($closepaidinvoices)
							{
								$invoice=new Depenses($this->db);
								$invoice->fetch($facid);
								$paiement = $invoice->getSommePaiement();
								//$creditnotes=$invoice->getSumCreditNotesUsed();
								$creditnotes=0;
								//$deposits=$invoice->getSumDepositsUsed();
								$deposits=0;
								$alreadypayed=price2num($paiement + $creditnotes + $deposits,'MT');
								$remaintopay=price2num($invoice->total_ttc - $paiement - $creditnotes - $deposits,'MT');
								if ($remaintopay == 0)
								{
									$result=$invoice->set_paid($user,'','');
								}
								else dol_syslog("Remain to pay for invoice ".$facid." not null. We do nothing.");
							}
						}
						else
						{
							dol_syslog('Paiement::Create Erreur INSERT dans paiement_facture '.$facid);
							$error++;
						}

					}
					else
					{
						dol_syslog(get_class($this).'::Create Montant non numerique',LOG_ERR);
					}
				}

				if (! $error)
				{
					// Appel des triggers
					include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
					$interface=new Interfaces($this->db);
					$result=$interface->run_triggers('PAYMENT_DEPENSES_CREATE',$this,$user,$langs,$conf);
					if ($result < 0) { $error++; $this->errors=$interface->errors; }
					// Fin appel triggers
				}
			}
			else
			{
				$this->error=$this->db->lasterror();
				dol_syslog(get_class($this).'::Create Error '.$this->error, LOG_ERR);
				$error++;
			}
		}
		else
		{
			$this->error="ErrorTotalIsNull";
			dol_syslog(get_class($this).'::Create Error '.$this->error, LOG_ERR);
			$error++;
		}

		if ($this->total <> 0 && $error == 0) // On accepte les montants negatifs
		{
			$this->db->commit();
			dol_syslog(get_class($this).'::Create Ok Total = '.$this->total);
			return $this->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	
	/**
	 *      A record into bank for payment with links between this bank record and invoices of payment.
	 *      All payment properties must have been set first like after a call to create().
	 *      @param      user                Object of user making payment
	 *      @param      mode                'payment', 'payment_supplier'
	 *      @param      label               Label to use in bank record
	 *      @param      accountid           Id of bank account to do link with
	 *      @param      emetteur_nom        Name of transmitter
	 *      @param      emetteur_banque     Name of bank
	 *      @return     int                 <0 if KO, bank_line_id if OK
	 */
	function addPaymentToBank($user,$mode,$label,$accountid,$emetteur_nom,$emetteur_banque,$notrigger=0)
	{
		global $conf,$langs,$user;
	
		$error=0;
		$bank_line_id=0;
		$this->fk_account=$accountid;
	
		if ($conf->banque->enabled)
		{
			require_once(DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php');
	
			dol_syslog("$user->id,$mode,$label,$this->fk_account,$emetteur_nom,$emetteur_banque");
	
			$acc = new Account($this->db);
			$acc->fetch($this->fk_account);
	
			$totalamount=$this->amount;
			if (empty($totalamount)) $totalamount=$this->total; // For backward compatibility
			if ($mode == 'payment') $totalamount=$totalamount;
			if ($mode == 'payment_supplier') $totalamount=-$totalamount;
	
			// Insert payment into llx_bank
			$bank_line_id = $acc->addline($this->datepaye,
					$this->paiementid,  // Payment mode id or code ("CHQ or VIR for example")
					$label,
					$totalamount,
					$this->num_paiement,
					'',
					$user,
					$emetteur_nom,
					$emetteur_banque);
	
			// Mise a jour fk_bank dans llx_paiement
			// On connait ainsi le paiement qui a genere l'ecriture bancaire
			if ($bank_line_id > 0)
			{
				$result=$this->update_fk_bank($bank_line_id);
				if ($result <= 0)
				{
					$error++;
					dol_print_error($this->db);
				}
	
				// Add link 'payment', 'payment_supplier' in bank_url between payment and bank transaction
				if ( ! $error)
				{
					$url=DOL_URL_ROOT.'/factures_depenses/paiement/fiche.php?id=';

					if ($url)
					{
						$result=$acc->add_url_line($bank_line_id, $this->id, $url, '(paiement)', $mode);
						if ($result <= 0)
						{
							$error++;
							dol_print_error($this->db);
						}
					}
				}
	
				// Add link 'company' in bank_url between invoice and bank transaction (for each invoice concerned by payment)
				if (! $error)
				{
					$linkaddedforthirdparty=array();
					foreach ($this->amounts as $key => $value)  // We should have always same third party but we loop in case of.
					{
						
						
							$fac = new Depenses($this->db);
							$fac->fetch($key);
							$fac->fetch_thirdparty();
							if (! in_array($fac->thirdparty->id,$linkaddedforthirdparty)) // Not yet done for this thirdparty
							{
								$result=$acc->add_url_line($bank_line_id, $fac->thirdparty->id,
										DOL_URL_ROOT.'/fourn/fiche.php?socid=', $fac->thirdparty->nom, 'company');
								if ($result <= 0) dol_print_error($this->db);
								$linkaddedforthirdparty[$fac->thirdparty->id]=$fac->thirdparty->id;  // Mark as done for this thirdparty
							}
						
					}
				}
	
				if (! $error && ! $notrigger)
				{
					// Appel des triggers
					include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
					$interface=new Interfaces($this->db);
					$result=$interface->run_triggers('PAYMENT_ADD_TO_BANK',$this,$user,$langs,$conf);
					if ($result < 0) { $error++; $this->errors=$interface->errors; }
					// Fin appel triggers
				}
			}
			else
			{
				$this->error=$acc->error;
				$error++;
			}
		}
	
		if (! $error)
		{
			return $bank_line_id;
		}
		else
		{
			return -1;
		}
	}
	
	
	/**
	 *      Mise a jour du lien entre le paiement et la ligne generee dans llx_bank
	 *      @param      id_bank     Id compte bancaire
	 */
	function update_fk_bank($id_bank)
	{
		$sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element.' set fk_bank = '.$id_bank;
		$sql.= ' WHERE rowid = '.$this->id;
	
		dol_syslog(get_class($this).'::update_fk_bank sql='.$sql);
		$result = $this->db->query($sql);
		if ($result)
		{
			return 1;
		}
		else
		{
			$this->error=$this->db->lasterror();
			dol_syslog(get_class($this).'::update_fk_bank '.$this->error);
			return -1;
		}
	}
	
	/**
	 *      \brief      Supprime un paiement ainsi que les lignes qu'il a genere dans comptes
	 *                  Si le paiement porte sur un ecriture compte qui est rapprochee, on refuse
	 *                  Si le paiement porte sur au moins une facture a "payee", on refuse
	 *      \return     int     <0 si ko, >0 si ok
	 */
	function delete()
	{
		$bank_line_id = $this->bank_line;
	
		$this->db->begin();
	
		// Verifier si paiement porte pas sur une facture a l'etat payee
		// Si c'est le cas, on refuse la suppression
		$billsarray=$this->getBillsArray('paye=1');
		if (is_array($billsarray))
		{
			if (sizeof($billsarray))
			{
				$this->error='Impossible de supprimer un paiement portant sur au moins une facture a l\'etat paye';
				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			$this->db->rollback();
			return -2;
		}
	
		// Verifier si paiement ne porte pas sur ecriture bancaire rapprochee
		// Si c'est le cas, on refuse le delete
		if ($bank_line_id)
		{
			$accline = new AccountLine($this->db,$bank_line_id);
			$accline->fetch($bank_line_id);
			if ($accline->rappro)
			{
				$this->error='Impossible de supprimer un paiement qui a genere une ecriture qui a ete rapprochee';
				$this->db->rollback();
				return -3;
			}
		}
	
		// Efface la ligne de paiement (dans paiement_facture et paiement)
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'paiementdepenses_facturedep';
		$sql.= ' WHERE fk_paiementfourn = '.$this->id;
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'paiementdepenses';
			$sql.= ' WHERE rowid = '.$this->id;
			$result = $this->db->query($sql);
			if (! $result)
			{
				$this->error=$this->db->error();
				$this->db->rollback();
				return -3;
			}
	
			// Supprimer l'ecriture bancaire si paiement lie a ecriture
			if ($bank_line_id)
			{
				$accline = new AccountLine($this->db);
				$accline->fetch($bank_line_id);
				$result=$accline->delete();
				if ($result < 0)
				{
					$this->error=$accline->error;
					$this->db->rollback();
					return -4;
				}
			}
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->error;
			$this->db->rollback();
			return -5;
		}
	}
	
	/**
	 *    Validate payment
	 *    @return     int     <0 if KO, >0 if OK
	 */
	function valide()
	{
		$sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element.' SET statut = 1 WHERE rowid = '.$this->id;
	
		dol_syslog(get_class($this).'::valide sql='.$sql);
		$result = $this->db->query($sql);
		if ($result)
		{
			return 1;
		}
		else
		{
			$this->error=$this->db->lasterror();
			dol_syslog(get_class($this).'::valide '.$this->error);
			return -1;
		}
	}
	
	function info($id)
	{
		$sql = 'SELECT c.rowid, datec, fk_user_author, tms';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'paiementdepenses as c';
		$sql.= ' WHERE c.rowid = '.$id;
	
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			if ($num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				if ($obj->fk_user_creat)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_creat);
					$this->user_creation = $cuser;
				}
				if ($obj->fk_user_modif)
				{
					$muser = new User($this->db);
					$muser->fetch($obj->fk_user_modif);
					$this->user_modification = $muser;
				}
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->tms);
			}
			$this->db->free($resql);
		}
		else
		{
			dol_print_error($this->db);
		}
	}
}