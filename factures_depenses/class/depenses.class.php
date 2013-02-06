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
 *   	\file       factures_depenses/class/depenses.class.php
 *		\ingroup    depenses module
 *		\brief      class module depenses
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */


require_once(DOL_DOCUMENT_ROOT ."/core/class/commonobject.class.php");

/**
 *      \class      Depenses
 */
class Depenses  extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	
	var $element='depenses';
	var $table_element='depenses';
	var $table_element_line = 'facturedet';
	var $fk_element = 'fk_facture';
	
    var $id;
    var $type;
    var $categorie_name;
    var $societe;
    var $ref;
    var $total_ht;
    var $total_ttc;
    var $tvarate;
    var $total_tva;     //Total tva
    var $payment;
    var $datec;
    var $dated;
    var $note;
    var $idtype;
    var $tva_amounts = array();
    var $rate_id;   // primary key value llx_depenses_tvarate
    var $fk_rowid; // foreign key value  llx_depenses_tvarate
    var $fk_soc; // foreign key value llx_societe
    var $categorie_id;
    var $socid;   
    var $client;
    var $statut;
    var $author;
    var $date_echeance;
    var $paye;
    var $amount;
    var $fk_statut;
	//...


    /**
     *      Constructor
     *      @param      DB      Database handler
     */
    function __construct($DB)
    {
        $this->db = $DB;
        return 1;
    }


    /**
     *      Create object into database
     *      @param      user        	User that create
     *      @param      notrigger	    0=launch triggers after, 1=disable triggers
     *      @return     int         	<0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        if (isset($this->societe)) $this->societe=trim($this->societe);
        if (isset($this->ref)) $this->ref=trim($this->ref);
        if (isset($this->total_ht))
        { 
        $this->total_ht=trim($this->total_ht);
        $this->total_ht=str_ireplace(",", ".", $this->total_ht);
        }
        if (isset($this->total_ttc))
        { 
        $this->total_ttc=trim($this->total_ttc);
        $this->total_ttc=str_ireplace(",", ".", $this->total_ttc);
        }
        if(isset($this->fk_soc))
        {
        	$this->fk_soc=trim($this->fk_soc);
        }
          
          $f=2.1;
          $g=19.6;
          $h=$f+$g;

        if (isset($this->tva_amounts))
        {
        	 
          foreach ($this->tva_amounts as &$tv)
          {
          $tv=trim($tv);
          //$av_tva+=$tv;
          $tv=str_ireplace(",", ".", $tv);
          $av_tva+=$tv;
          }
        }
        
        if (isset($this->note)) $this->note=trim($this->note);
        
        //$this->tva=$this->total_ttc-$this->total_ht;
        
        $this->total_ht=$this->total_ttc-$av_tva;
		//...

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."depenses(";
		$sql.= " type,";
		$sql.= " societe,";
		$sql.= " ref,";
		$sql.= " total_ht,";
		$sql.= " total_ttc,";
		$sql.= " fk_soc,";
		$sql.= " total_tva,";      // Total_tva
		$sql.= " payment,";
	    $sql.= " datec,";
	    $sql.= " dated,";
	    $sql.= " date_lim_reglement,";
	    $sql.= " fk_user_author,";
	    $sql.= " note";
		//...
        $sql.= ") VALUES (";
        $sql.= " '".$this->type."',";
        $sql.= " '".$this->societe."',";
        $sql.= " '".$this->ref."',";
        $sql.= " '".$this->total_ht."',";
        $sql.= " '".$this->total_ttc."',";
        $sql.= " '".$this->fk_soc."',";
        $sql.= " '".$av_tva."',";
        $sql.= " '".$this->payment."',";
        $sql.= " '".$this->datec."',";
        $sql.= " '".$this->dated."',";
        //$sql.= " '".$this->date_echeance."',";
        $sql.= $this->date_echeance!=''?"'".$this->date_echeance."',":"null,";
        $sql.= " '".$user->id."',";
        $sql.= " '".$this->note."'";
		//...
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        
	   	$resql=$this->db->query($sql);
	   	
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."depenses");
            
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."depenses_tvarate(";
		    $sql.= " tva0,";
		    $sql.= " tva2_1,";
		    $sql.= " tva5_5,";
		    $sql.= " tva7_0,";
		    $sql.= " tva19_6,";
		    $sql.= " fk_rowid";
		    $sql.= ") VALUES (";
	        $sql.= " '".$this->tva_amounts["tva0_0"]."',";
	        $sql.= " '".$this->tva_amounts["tva2_1"]."',";
	        $sql.= " '".$this->tva_amounts["tva5_5"]."',";
	        $sql.= " '".$this->tva_amounts["tva7_0"]."',";
	        $sql.= " '".$this->tva_amounts["tva19_6"]."',";
	        $sql.= " '".$this->id."'";
			$sql.= ")";
			
			$resql=$this->db->query($sql);

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *    Load object in memory from database
     *    @param      id          id object
     *    @return     int         <0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " d.rowid,";
		$sql.= " d.type,";
		$sql.= " d.societe,";
		$sql.= " d.ref,";
		$sql.= " d.total_ht,";
		$sql.= " d.total_ttc,";
		$sql.= " d.tvarate,";
		$sql.= " d.total_tva,";
		$sql.= " d.payment,";
		$sql.= " d.datec,";
		$sql.= " d.dated,";
		$sql.= " d.date_lim_reglement,";
		$sql.= " d.note,";
		$sql.= " d.fk_statut,";
		$sql.= " d.fk_user_author as user,";
		$sql.= " d.paye,";
		$sql.= " d.amount,";
		$sql.= " d.fk_soc,";
		//$sql.= " t.rowid,";
		$sql.= " t.tva0,";
		$sql.= " t.tva2_1,";
		$sql.= " t.tva5_5,";
		$sql.= " t.tva7_0,";
		$sql.= " t.tva19_6";

		//...
        $sql.= " FROM ".MAIN_DB_PREFIX."depenses as d LEFT JOIN ".MAIN_DB_PREFIX."depenses_tvarate as t ON d.rowid=t.fk_rowid";
        $sql.= " WHERE d.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                $this->type = $obj->type;
                $this->societe = $obj->societe;
                $this->ref = $obj->ref;
                $this->total_ht = $obj->total_ht;
                $this->total_ttc = $obj->total_ttc;
                $this->total_tva = $obj->total_tva;
               // $this->tvarate = $obj->tvarate;
                $this->payment = $obj->payment;
                $this->datec = $obj->datec;
                $this->dated = $obj->dated;
                $this->date_echeance = $obj->date_lim_reglement;
                $this->note = $obj->note;
                $this->tva_amounts =array("tva0_0"=>$obj->tva0,
                                          "tva2_1"=>$obj->tva2_1,
                						  "tva5_5"=>$obj->tva5_5,
                                          "tva7_0"=>$obj->tva7_0,
                                          "tva19_6"=>$obj->tva19_6
                                         );
                $this->fk_rowid = $obj->fk_rowid;
                $this->fk_soc = $obj->fk_soc;
                $this->socid = $obj->fk_soc;
                $this->fk_statut = $obj->fk_statut;
                $this->author = $obj->user;
                $this->paye= $obj->paye;
                $this->amount = $obj->amount;
				//...
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *      Update object into database
     *      @param      user        	User that modify
     *      @param      notrigger	    0=launch triggers after, 1=disable triggers
     *      @return     int         	<0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        if (isset($this->societe)) $this->societe=trim($this->societe);
        if (isset($this->ref)) $this->ref=trim($this->ref);
        if (isset($this->total_ht))
        { 
        $this->total_ht=trim($this->total_ht);
        $this->total_ht=str_ireplace(",", ".", $this->total_ht);
        }
        if (isset($this->total_ttc))
        { 
        $this->total_ttc=trim($this->total_ttc);
        $this->total_ttc=str_ireplace(",", ".", $this->total_ttc);
        }
        
        if (isset($this->note)) $this->note=trim($this->note);
        
        //$this->tva=$this->total_ttc-$this->total_ht;
		//...

		// Check parameters
		// Put here code to add control on parameters values
		
        if (isset($this->tva_amounts))
        {
        	 
          foreach ($this->tva_amounts as &$tv)
          {
          $tv=trim($tv);
          $tv=str_ireplace(",", ".", $tv);
          $av_tva+=$tv;
          }
        }
      
        $this->total_ht=$this->total_ttc-$av_tva;
        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."depenses SET";
        $sql.= " rowid=".$this->id.",";
        //$sql.= " type=".(isset($this->type)?"'".$this->db->escape($this->type)."'":"null").",";
        //$sql.= " societe=".(isset($this->societe)?"'".$this->db->escape($this->societe)."'":"null").",";
        $sql.= " ref= '".$this->ref."',";
        //$sql.= " total_ht=".$this->total_ht.",";
        $sql.= " total_ttc=".$this->total_ttc.",";
        $sql.= " payment= '".$this->payment."',";
        $sql.= " total_tva=".$av_tva.",";
        //$sql.= " tvarate=".$this->tvarate.",";
        $sql.= " dated= '".$this->dated."',";
        $sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null")."";
		//...
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			$sql = "UPDATE ".MAIN_DB_PREFIX."depenses_tvarate SET";
			//$sql.= " rowid=".$this->rate_id.",";
			if ($this->tva_amounts["tva0_0"]===0)$sql.= " tva0=".$this->tva_amounts["tva0_0"].",";
			$sql.= " tva2_1=".$this->tva_amounts["tva2_1"].",";
			$sql.= " tva5_5=".$this->tva_amounts["tva5_5"].",";
			$sql.= " tva7_0=".$this->tva_amounts["tva7_0"].",";
			$sql.= " tva19_6=".$this->tva_amounts["tva19_6"]."";
			//$sql.= " fk_rowid=".$this->fk_rowid."";
			$sql.= " WHERE fk_rowid=".$this->id."";
			
			//$this->db->begin();

		    dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
            $resql = $this->db->query($sql);
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return $this->id;
		}
    }


 	/**
	 *   Delete object in database
     *	 @param     user        	User that delete
     *   @param     notrigger	    0=launch triggers after, 1=disable triggers
	 *   @return	int				<0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."depenses";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action call a trigger.

		        //// Call triggers
		        //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *		Load an object from its id and create a new one in database
	 *		@param      fromid     		Id of object to clone
	 * 	 	@return		int				New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Skeleton_class($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{



		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *		Initialisz object with example values
	 *		Id must be 0 if object instance is a specimen.
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		$this->prop1='prop1';
		$this->prop2='prop2';
	}
	
	function listDepenses()
	{
		$sql = "SELECT";
		$sql.= " d.rowid,";
		$sql.= " d.type,";
		$sql.= " d.societe,";
		$sql.= " d.ref,";
		$sql.= " d.total_ht,";
		$sql.= " d.total_ttc,";
		$sql.= " d.dated ";
		//...
        $sql.= " FROM ".MAIN_DB_PREFIX."depenses as d";
        
        dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        
        $resql=$this->db->query($sql);
        if ($resql)
        {
        	return $resql;
        }
	}
	
	function update_note($note, $user)
	{
		$this->note=$note;
	 // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."depenses SET";
        $sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null")."";
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action call a trigger.

	            //// Call triggers
	            //include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return $this->id;
		}
	}
	
	/*
	 * Return list of the categorie
	 */
	function gettype()
	{
	    $sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.type, ";
		$sql.= " c.rowid as cat_id";
		$sql.= " FROM ".MAIN_DB_PREFIX."depenses_type as t,";
		$sql.= MAIN_DB_PREFIX."categorie as c ";
		$sql.= "WHERE c.label=t.type";
		
	    dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        
        $resql=$this->db->query($sql);
        if ($resql)
        {  
        	$this->db->commit();
            return $resql;
        }
        
        else
        {
        	$error++; 
        	$this->errors[]="Error ".$this->db->lasterror();
         if ($error)
		 {
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		 }
        }
	}
	
	/*
	 * Return name of the categorie
	 * $cat_id - id of the categorie
	 */
	
	function showType($cat_id)
	{
		$sql = "SELECT";
		$sql.= " c.rowid,";
		$sql.= " c.label ";
		$sql.= "FROM ".MAIN_DB_PREFIX."categorie as c ";
		$sql.= "WHERE c.rowid=".$cat_id;

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		
		$resql=$this->db->query($sql);
		
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			$this->categorie_name = $obj->label;
			$this->db->commit();
			return $this->categorie_name;
		}
		
		else
		{
			$error++;
			$this->errors[]="Error ".$this->db->lasterror();
			if ($error)
			{
				foreach($this->errors as $errmsg)
				{
					dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
					$this->error.=($this->error?', '.$errmsg:$errmsg);
				}
				$this->db->rollback();
				return -1*$error;
			}
		}
	}
	
	function delete_type($id)
	{
	    global $conf, $langs;
		$error=0;
		

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."depenses_type";
		$sql.= " WHERE rowid=".$id;

		$this->db->begin();

		dol_syslog(get_class($this)."::delete sql=".$sql);
		$resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }


        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}
	
function img_al($alt, $picto, $options='', $pictoisfullpath=0)
{
    global $conf;

    $path =  'theme/'.$conf->theme;
    $url = DOL_URL_ROOT;

    if (preg_match('/^([^@]+)@([^@]+)$/i',$picto,$regs))
    {
        $picto = $regs[1];
        $path = $regs[2];
        if (! preg_match('/(\.png|\.gif)$/i',$picto)) $picto.='.png';
        // If img file not into standard path, we use alternate path
        if (defined('DOL_URL_ROOT_ALT') && DOL_URL_ROOT_ALT && ! file_exists(DOL_DOCUMENT_ROOT.'/'.$path.'/img/'.$picto)) $url = DOL_URL_ROOT_ALT;
    }
    else
    {
        if (! preg_match('/(\.png|\.gif)$/i',$picto)) $picto.='.png';
    }
    if ($pictoisfullpath) return 'src="'.$picto.'"';
    return 'src="'.$url.'/'.$path.'/img/'.$picto.'"';
}

/**
 *    	Output html form to select a third party
 *		@param      selected        Preselected type
 *		@param      htmlname        Name of field in form
 *    	@param      filter          Optionnal filters criteras
 *		@param		showempty		Add an empty field
 * 		@param		showtype		Show third party type in combolist (customer, prospect or supplier)
 * 		@param		forcecombo		Force to use combo box
 */
function select_company($selected='',$htmlname='socid',$filter='',$showempty=0, $showtype=0, $forcecombo=0)
{
	global $conf,$user,$langs;

	$out='';

	// On recherche les societes
	$sql = "SELECT s.rowid, s.nom, s.client, s.fournisseur, s.code_client, s.code_fournisseur";
	$sql.= " FROM ".MAIN_DB_PREFIX ."societe as s";
	if (!$user->rights->societe->client->voir && !$user->societe_id) $sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.entity = ".$conf->entity;
	if ($filter) $sql.= " AND ".$filter;
	if (!$user->rights->societe->client->voir && !$user->societe_id) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	$sql.= " ORDER BY nom ASC";

	dol_syslog("Form::select_societes sql=".$sql);
	$resql=$this->db->query($sql);
	if ($resql)
	{
		if ($conf->use_javascript_ajax && $conf->global->COMPANY_USE_SEARCH_TO_SELECT && ! $forcecombo)
		{
			//$minLength = (is_numeric($conf->global->COMPANY_USE_SEARCH_TO_SELECT)?$conf->global->COMPANY_USE_SEARCH_TO_SELECT:2);

			$out.= ajax_combobox($htmlname);
		}

		$out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'">';
		if ($showempty) $out.= '<option value="-1">&nbsp;</option>';
		$num = $this->db->num_rows($resql);
		$i = 0;
		if ($num)
		{
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$label=$obj->nom;
				if ($showtype)
				{
					if ($obj->client || $obj->fournisseur) $label.=' (';
					if ($obj->client == 1 || $obj->client == 3) $label.=$langs->trans("Customer");
					if ($obj->client == 2 || $obj->client == 3) $label.=($obj->client==3?', ':'').$langs->trans("Prospect");
					if ($obj->fournisseur) $label.=($obj->client?', ':'').$langs->trans("Supplier");
					if ($obj->client || $obj->fournisseur) $label.=')';
				}
				if ($selected > 0 && $selected == $obj->rowid)
				{
					$out.= '<option value="'.$obj->rowid.'" selected="selected">'.$label.'</option>';
				}
				else
				{
					$out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
				}
				$i++;
			}
		}
		$out.= '</select>';
	}
	else
	{
		dol_print_error($this->db);
	}

	return $out;
 }
 
 /*
  * Return id of the categorie
  * $type - Name of the categorie
  */
 function getCategorieid($type)
 {
 	$sql = "SELECT";
 	$sql.= " c.rowid,";
 	$sql.= " c.label ";
 	$sql.= "FROM ".MAIN_DB_PREFIX."categorie as c ";
 	$sql.= "WHERE c.label='".$type."'";
 	
 	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
 	
 	$resql=$this->db->query($sql);
 	
 	if ($resql)
 	{
 		$obj = $this->db->fetch_object($resql);
 		$this->categorie_id = $obj->rowid;
 		$this->db->commit();
 		return $this->categorie_id;
 	}
 	
 	else
 	{
 		$error++;
 		$this->errors[]="Error ".$this->db->lasterror();
 		if ($error)
 		{
 			foreach($this->errors as $errmsg)
 			{
 				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
 				$this->error.=($this->error?', '.$errmsg:$errmsg);
 			}
 			$this->db->rollback();
 			return -1*$error;
 		}
 	}
 }
 
 /**
  * 	Return amount of payments already done
  *	@return		int		Amount of payment already done, <0 if KO
  */
 function getSommePaiement()
 {
 	$table='paiementdepenses_facturedep';
 	$field='fk_facturefourn';
 
 	$sql = 'SELECT sum(amount) as amount';
 	$sql.= ' FROM '.MAIN_DB_PREFIX.$table;
 	$sql.= ' WHERE '.$field.' = '.$this->id;
 
 	dol_syslog("Facture::getSommePaiement sql=".$sql, LOG_DEBUG);
 	$resql=$this->db->query($sql);
 	if ($resql)
 	{
 		$obj = $this->db->fetch_object($resql);
 		$this->db->free($resql);
 		return $obj->amount;
 	}
 	else
 	{
 		$this->error=$this->db->lasterror();
 		return -1;
 	}
 }
 
 function set_paid($user,$close_code='',$close_note='')
 {
 	global $conf,$langs;
 	$error=0;
 
 	if ($this->paye != 1)
 	{
 		$this->db->begin();
 
 		dol_syslog("Facture::set_paid rowid=".$this->id, LOG_DEBUG);
 		$sql = 'UPDATE '.MAIN_DB_PREFIX.'depenses SET';
 		$sql.= ' fk_statut=2';
 		if (! $close_code) $sql.= ', paye=1';
 		if ($close_code) $sql.= ", close_code='".$this->db->escape($close_code)."'";
 		if ($close_note) $sql.= ", close_note='".$this->db->escape($close_note)."'";
 		$sql.= ' WHERE rowid = '.$this->id;
 
 		$resql = $this->db->query($sql);
 		if ($resql)
 		{
 			$this->use_webcal=($conf->global->PHPWEBCALENDAR_BILLSTATUS=='always'?1:0);
 
 			// Appel des triggers
 			include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
 			$interface=new Interfaces($this->db);
 			$result=$interface->run_triggers('BILL_PAYED',$this,$user,$langs,$conf);
 			if ($result < 0) { $error++; $this->errors=$interface->errors; }
 			// Fin appel triggers
 		}
 		else
 		{
 			$error++;
 			$this->error=$this->db->error();
 			dol_print_error($this->db);
 		}
 
 		if (! $error)
 		{
 			$this->db->commit();
 			return 1;
 		}
 		else
 		{
 			$this->db->rollback();
 			return -1;
 		}
 	}
 	else
 	{
 		return 0;
 	}
 }
 
 /**
  *    	\brief      Renvoi le libelle d'un statut donne
  *    	\param      paye          	Etat paye
  *    	\param      statut        	Id statut
  *    	\param      mode          	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
  *		\param		alreadypaid	    Montant deja paye
  *		\param		type			Type facture
  *    	\return     string        	Libelle du statut
  */
 function LibStatut($paye,$statut,$mode=0,$alreadypaid=-1,$type=0)
 {
 	global $langs;
 	$langs->load('bills');
 
 	//print "$paye,$statut,$mode,$alreadypaid,$type";
 	if ($mode == 0)
 	{
 		$prefix='';
 		if (! $paye)
 		{
 			if ($statut == 0) return $langs->trans('Bill'.$prefix.'StatusDraft');
 			if (($statut == 3 || $statut == 2) && $alreadypaid <= 0) return $langs->trans('Bill'.$prefix.'StatusClosedUnpaid');
 			if (($statut == 3 || $statut == 2) && $alreadypaid > 0) return $langs->trans('Bill'.$prefix.'StatusClosedPaidPartially');
 			if ($alreadypaid <= 0) return $langs->trans('Bill'.$prefix.'StatusNotPaid');
 			return $langs->trans('Bill'.$prefix.'StatusStarted');
 		}
 		else
 		{
 			if ($type == 2) return $langs->trans('Bill'.$prefix.'StatusPaidBackOrConverted');
 			elseif ($type == 3) return $langs->trans('Bill'.$prefix.'StatusConverted');
 			else return $langs->trans('Bill'.$prefix.'StatusPaid');
 		}
 	}
 	if ($mode == 1)
 	{
 		$prefix='Short';
 		if (! $paye)
 		{
 			if ($statut == 0) return $langs->trans('Bill'.$prefix.'StatusDraft');
 			if (($statut == 3 || $statut == 2) && $alreadypaid <= 0) return $langs->trans('Bill'.$prefix.'StatusCanceled');
 			if (($statut == 3 || $statut == 2) && $alreadypaid > 0) return $langs->trans('Bill'.$prefix.'StatusClosedPaidPartially');
 			if ($alreadypaid <= 0) return $langs->trans('Bill'.$prefix.'StatusNotPaid');
 			return $langs->trans('Bill'.$prefix.'StatusStarted');
 		}
 		else
 		{
 			if ($type == 2) return $langs->trans('Bill'.$prefix.'StatusPaidBackOrConverted');
 			elseif ($type == 3) return $langs->trans('Bill'.$prefix.'StatusConverted');
 			else return $langs->trans('Bill'.$prefix.'StatusPaid');
 		}
 	}
 	if ($mode == 2)
 	{
 		$prefix='Short';
 		if (! $paye)
 		{
 			if ($statut == 0) return img_picto($langs->trans('BillStatusDraft'),'statut0').' '.$langs->trans('Bill'.$prefix.'StatusDraft');
 			if (($statut == 3 || $statut == 2) && $alreadypaid <= 0) return img_picto($langs->trans('StatusCanceled'),'statut5').' '.$langs->trans('Bill'.$prefix.'StatusCanceled');
 			if (($statut == 3 || $statut == 2) && $alreadypaid > 0) return img_picto($langs->trans('BillStatusClosedPaidPartially'),'statut7').' '.$langs->trans('Bill'.$prefix.'StatusClosedPaidPartially');
 			if ($alreadypaid <= 0) return img_picto($langs->trans('BillStatusNotPaid'),'statut1').' '.$langs->trans('Bill'.$prefix.'StatusNotPaid');
 			return img_picto($langs->trans('BillStatusStarted'),'statut3').' '.$langs->trans('Bill'.$prefix.'StatusStarted');
 		}
 		else
 		{
 			if ($type == 2) return img_picto($langs->trans('BillStatusPaidBackOrConverted'),'statut6').' '.$langs->trans('Bill'.$prefix.'StatusPaidBackOrConverted');
 			elseif ($type == 3) return img_picto($langs->trans('BillStatusConverted'),'statut6').' '.$langs->trans('Bill'.$prefix.'StatusConverted');
 			else return img_picto($langs->trans('BillStatusPaid'),'statut6').' '.$langs->trans('Bill'.$prefix.'StatusPaid');
 		}
 	}
 	if ($mode == 3)
 	{
 		$prefix='Short';
 		if (! $paye)
 		{
 			if ($statut == 0) return img_picto($langs->trans('BillStatusDraft'),'statut0');
 			if (($statut == 3 || $statut == 2) && $alreadypaid <= 0) return img_picto($langs->trans('BillStatusCanceled'),'statut5');
 			if (($statut == 3 || $statut == 2) && $alreadypaid > 0) return img_picto($langs->trans('BillStatusClosedPaidPartially'),'statut7');
 			if ($alreadypaid <= 0) return img_picto($langs->trans('BillStatusNotPaid'),'statut1');
 			return img_picto($langs->trans('BillStatusStarted'),'statut3');
 		}
 		else
 		{
 			if ($type == 2) return img_picto($langs->trans('BillStatusPaidBackOrConverted'),'statut6');
 			elseif ($type == 3) return img_picto($langs->trans('BillStatusConverted'),'statut6');
 			else return img_picto($langs->trans('BillStatusPaid'),'statut6');
 		}
 	}
 	if ($mode == 4)
 	{
 		if (! $paye)
 		{
 			if ($statut == 0) return img_picto($langs->trans('BillStatusDraft'),'statut0').' '.$langs->trans('BillStatusDraft');
 			if (($statut == 3 || $statut == 2) && $alreadypaid <= 0) return img_picto($langs->trans('BillStatusCanceled'),'statut5').' '.$langs->trans('Bill'.$prefix.'StatusCanceled');
 			if (($statut == 3 || $statut == 2) && $alreadypaid > 0) return img_picto($langs->trans('BillStatusClosedPaidPartially'),'statut7').' '.$langs->trans('Bill'.$prefix.'StatusClosedPaidPartially');
 			if ($alreadypaid <= 0) return img_picto($langs->trans('BillStatusNotPaid'),'statut1').' '.$langs->trans('BillStatusNotPaid');
 			return img_picto($langs->trans('BillStatusStarted'),'statut3').' '.$langs->trans('BillStatusStarted');
 		}
 		else
 		{
 			if ($type == 2) return img_picto($langs->trans('BillStatusPaidBackOrConverted'),'statut6').' '.$langs->trans('BillStatusPaidBackOrConverted');
 			elseif ($type == 3) return img_picto($langs->trans('BillStatusConverted'),'statut6').' '.$langs->trans('BillStatusConverted');
 			else return img_picto($langs->trans('BillStatusPaid'),'statut6').' '.$langs->trans('BillStatusPaid');
 		}
 	}
 	if ($mode == 5)
 	{
 		$prefix='Short';
 		if (! $paye)
 		{
 			if ($statut == 0) return $langs->trans('Bill'.$prefix.'StatusDraft').' '.img_picto($langs->trans('BillStatusDraft'),'statut0');
 			if (($statut == 3 || $statut == 2) && $alreadypaid <= 0) return $langs->trans('Bill'.$prefix.'StatusCanceled').' '.img_picto($langs->trans('BillStatusCanceled'),'statut5');
 			if (($statut == 3 || $statut == 2) && $alreadypaid > 0) return $langs->trans('Bill'.$prefix.'StatusClosedPaidPartially').' '.img_picto($langs->trans('BillStatusClosedPaidPartially'),'statut7');
 			if ($alreadypaid <= 0) return $langs->trans('Bill'.$prefix.'StatusNotPaid').' '.img_picto($langs->trans('BillStatusNotPaid'),'statut1');
 			return $langs->trans('Bill'.$prefix.'StatusStarted').' '.img_picto($langs->trans('BillStatusStarted'),'statut3');
 		}
 		else
 		{
 			if ($type == 2) return $langs->trans('Bill'.$prefix.'StatusPaidBackOrConverted').' '.img_picto($langs->trans('BillStatusPaidBackOrConverted'),'statut6');
 			elseif ($type == 3) return $langs->trans('Bill'.$prefix.'StatusConverted').' '.img_picto($langs->trans('BillStatusConverted'),'statut6');
 			else return $langs->trans('Bill'.$prefix.'StatusPaid').' '.img_picto($langs->trans('BillStatusPaid'),'statut6');
 		}
 	}
 }
 
 /**
  *    	Renvoie nom clicable (avec eventuellement le picto)
  *		@param		withpicto		0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
  *		@param		option			Sur quoi pointe le lien
  * 		@param		max				Max length of shown ref
  * 		@return		string			Chaine avec URL
  */
 function getNomUrl($withpicto=0,$option='',$max=0)
 {
 	global $langs;
 
 	$result='';
 
 	if ($option == 'document')
 	{
 		$lien = '<a href="'.DOL_URL_ROOT.'/factures_depenses/document.php?facid='.$this->id.'">';
 		$lienfin='</a>';
 	}
 	else
 	{
 		$lien = '<a href="'.DOL_URL_ROOT.'/fourn/facture/fiche.php?facid='.$this->id.'">';
 		$lienfin='</a>';
 	}
 	$label=$langs->trans("ShowInvoice").': '.$this->ref;
 	if ($this->ref_supplier) $label.=' / '.$this->ref_supplier;
 
 	if ($withpicto) $result.=($lien.img_object($label,'bill').$lienfin.' ');
 	$result.=$lien.($max?dol_trunc($this->ref,$max):$this->ref).$lienfin;
 	return $result;
 }
}
?>