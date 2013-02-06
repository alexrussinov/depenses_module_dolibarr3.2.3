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
 *   	\file       factures_depenses/lib/tax.lib.php
 *		\ingroup    depenses module
 *		\brief      library of functions to manage tva depenses
 *		\version    $Id: index.php,v 1.1 2012/08/09 22:21:57
 *		\author		Alex Russinov
 */



/**
 * Function get parametrs: $db - database handler object
 *                         $y - year
 *                         $m - month
 * Return array of total_tva by rate for curent year and month
 */
function get_tva_depenses($db, $y, $m)
{
	global $conf;
	
	$dep=array();
	
	
	$sql = " SELECT d.rowid, d.ref, d.dated, t.tva0, t.tva2_1, t.tva5_5, t.tva7_0, t.tva19_6";
	$sql.= " FROM ".MAIN_DB_PREFIX."depenses as d, ".MAIN_DB_PREFIX."depenses_tvarate as t";
	$sql.= " WHERE d.rowid=t.fk_rowid";
	if ($y && $m)
            {
                $sql.= " AND d.dated >= '".$db->idate(dol_get_first_day($y,$m,false))."'";
                $sql.= " AND d.dated <= '".$db->idate(dol_get_last_day($y,$m,false))."'";
            }
            else if ($y)
            {
                $sql.= " AND d.dated >= '".$db->idate(dol_get_first_day($y,1,false))."'";
                $sql.= " AND d.dated <= '".$db->idate(dol_get_last_day($y,12,false))."'";
            }
            
            $resql = $db->query($sql);
            
            if ($resql)
            {
            	while ($res=$db->fetch_array($resql))
            	{
            	//var_dump($res);
            	$dep['tot_2_1']  +=$res['tva2_1'];
                $dep['tot_5_5']  +=$res['tva5_5'];
            	$dep['tot_7_0']  +=$res['tva7_0'];
            	$dep['tot_19_6'] +=$res['tva19_6'];
            	}
            	//var_dump($dep);
            	return $dep;
            }
            
            else
            {
            	dol_print_error($db);
            	return -1;
            }
	      
}