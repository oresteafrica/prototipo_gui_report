<?php
require 'kint/Kint.class.php';
$debug = true;
$ini_array = parse_ini_file('../cron/moz.ini');
$sdsn = $ini_array['sdsn'];
$user = $ini_array['user'];
$pass = $ini_array['pass'];
$opts = array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

try {
    $db = new PDO($sdsn, $user, $pass, $opts);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Problemas de conexão à base de dados:<br/>' . $e);
}


/**
if ($debug) {
echo $debug;
echo '<hr />';
!Kint::dump( $array_table );
echo '<hr />';
exit;
}
**/


$dom = new DOMDocument(); 
$dom->formatOutput = true;
$dom->Load('provdist.xml');

$elements = $dom->getElementsByTagName('area');


$i = 0;
foreach($elements as $node){
	$i++;
	$temp0 = $node->childNodes->item(0)->nodeValue;
	$temp1 = $node->childNodes->item(1)->nodeValue;
	echo "$i<br />$temp1<br />$temp0<hr />";

}

/**
$i = 0;
foreach($elements as $node){
	foreach($node->childNodes as $child) {
		$i++;
		$nodeval = $child->nodeValue;
		$nodenam = $child->nodeName;



//		$compare = compare_geoadm_name ($db, 'new_adm_ter', 'nome', 2, $nomedist);

//		if ($compare) { insert_coord ($db, 'new_adm_ter', 'coord', $coord); }


		if ($debug) {
//			$simnao = $compare?'1':'0';
//			echo "$i) $nomedist ($simnao)<hr />";
//			echo '<hr />';
//			echo "$i) $nodenam<hr />";
		if ($nodenam == 'district') { echo "$i) $nodeval<hr />"; }
		}



	}
}

**/



//----------------------------------------------------------------------------------------------------------
function insert_coord ($db, $table, $field, $coord) {


}
//----------------------------------------------------------------------------------------------------------
function compare_geoadm_name ($db, $table, $field, $adm_level, $nametosearch) {
	$sql = "SELECT $field FROM $table WHERE (nivel = $adm_level AND nome = \"$nametosearch\")";
	$tabquery = $db->query($sql);
	$tabquery->setFetchMode(PDO::FETCH_ASSOC);
	$result = $tabquery->fetchColumn();
	if ($result) {return true;} else {return false;}
}
//----------------------------------------------------------------------------------------------------------
?>
