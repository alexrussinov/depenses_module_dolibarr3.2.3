<?php

//Add new type of invoice for the <select> tag in fiche.php

$type=$_GET["type"];

$sql = "INSERT INTO ".MAIN_DB_PREFIX."depenses_type(";
		$sql.= " type ";
        $sql.= ") VALUES (";
        $sql.= " '".$type."'";
		$sql.= ")";
		
$resql=$db->query($sql);

if($resql)
{
	print '<h1>It is work!</h1>';
}

?>