<?php
require("../../main.inc.php");

$type=$_GET["type"];

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."depenses_type(";
		$sql.= " type ";
        $sql.= ") VALUES (";
        $sql.= " '".$type."'";
		$sql.= ")";
		
        $resql=$db->query($sql);

?>