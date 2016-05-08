<?php
require 'kint/Kint.class.php';
$debug = false;

//header('Content-type: text/html; charset=UTF-8') ;
if($_GET['period'] === '') { echo '<p style="color:red;font-weight:bold;">period is an empty string</p>'; exit; }
if($_GET['period'] === false) { echo '<p style="color:red;font-weight:bold;">period is false</p>'; exit; }
if($_GET['period'] === null) { echo '<p style="color:red;font-weight:bold;">period is null</p>'; exit; }
if(!isset($_GET['period'])) { echo '<p style="color:red;font-weight:bold;">period is not set</p>'; exit; }
if(empty($_GET['period'])) { echo '<p style="color:red;font-weight:bold;">period is empty</p>'; exit; }
$period = $_GET['period'];
$year = substr($period,0,4);
$month = substr($period,4,2);
$timestamp = date('Y-m-d');
$date = strtotime($timestamp);
$today_year = date('Y', $date);
$today_month = date('m', $date);

if($year < 2010 or $year > $today_year) { echo '<p style="color:red;font-weight:bold;">year ='.$year.' not correct</p>'; exit; }
if($month < 1 or $month > 12) { echo '<p style="color:red;font-weight:bold;">month ='.$month.' not correct</p>'; exit; }

$dom = new DOMDocument(); 
$dom->formatOutput = true;
//$dom->Load('ADOREUN_completo.xml');
$dom->Load('Estrutura_completa_APM-00_from_live_2.22.xml');

$group_elements = $dom->getElementsByTagName('dataElements')->item(0);

if (! $group_elements->hasChildNodes() ) { echo '$group_elements has no child nodes'; exit; }

$elements = $group_elements->childNodes;

$group_ous = $dom->getElementsByTagName('organisationUnits')->item(1);
$ous = $group_ous->childNodes;

$dataElements = [];
$organisationUnitsPA = [];

$name = 'name';
$id = 'id';
$level = 'level';

foreach($elements as $node){
	$datele_name = $node->getAttribute($name );
	$datele_id = $node->getAttribute($id);
	$dataElements[] = array($name=>$datele_name,$id=>$datele_id);
}

foreach($ous as $node){
	$ou_name = $node->getAttribute($name);
	$ou_id = $node->getAttribute($id);
	$ou_level = $node->getAttribute($level);
	if ($ou_level==4) {
		$organisationUnitsPA[] = array($name=>$ou_name,$id=>$ou_id)  ;
	}
}

$csv_title = array('dataelement','period','orgunit','categoryoptioncombo','attroptioncombo','value','storedby','lastupdated');
$csv_import_array = [];
foreach($organisationUnitsPA as $pa){
	foreach($dataElements as $ele){
		$dataelement = $ele[$id];
		$orgunit = $pa[$id];
		$categoryoptioncombo = '';
		$attroptioncombo = '';
		$value = rand(1, 10);
		$storedby = 'admin';
		$csv_import_array[] = array($dataelement, $period, $orgunit, $categoryoptioncombo, $attroptioncombo, $value, $storedby, $timestamp);
	}
}

unset($dataElements);
unset($organisationUnitsPA);

array_unshift($csv_import_array,$csv_title);

//ob_start();
//print_array_as_csv($csv_import_array);
//$csv_import_string = ob_get_clean();

if ($debug) {
//	echo str_replace("\n",'<br />',$csv_import_string);
//	!Kint::dump( $ous );
//	echo '<hr />';
//	!Kint::dump( $dataElements );
//	echo '<hr />';
//	!Kint::dump( $organisationUnitsPA );
//	echo '<hr />';
	!Kint::dump( $csv_import_array );
//	echo '<hr />';
//	!Kint::dump( $dataElements );
//	echo '<hr />';
//	!Kint::dump( $organisationUnitsPA );
} else {
	array_to_csv($csv_import_array, 'APM-00_fake_data.csv');
//	header("Content-type:text/csv");
//	header("Content-Disposition: attachment; filename=fake_data.csv");
//	echo $csv_import_string;

}

//----------------------------------------------------------------------------------------------------------
function array_to_csv($input_array, $output_file_name, $delimiter = ',') {
    /** open raw memory as file, no need for temp files, be careful not to run out of memory thought */
    $f = fopen('php://memory', 'w');
    /** loop through array  */
    foreach ($input_array as $line) {
        /** default php csv handler **/
        fputcsv($f, $line, $delimiter);
    }
    /** rewrind the "file" with the csv lines **/
    fseek($f, 0);
    /** modify header to be downloadable csv file **/
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
    /** Send file to browser for download */
    fpassthru($f);
}
//----------------------------------------------------------------------------------------------------------
function print_array_as_csv($input_array, $delimiter = ',') {
	foreach ($input_array as $line) {
		$sline = '"' . implode('","', $line) . '"';
		print str_replace('""','',$sline) . "\n";
   }
}
//----------------------------------------------------------------------------------------------------------
/**
The following section describes the CSV format used in DHIS2. The first row is assumed to be a header row and will be ignored during import.

Column					Required	Description
Data element 			Yes 		Refers to ID by default, can also be name and code based on selected id scheme
Period					Yes 		In ISO format
Org unit				Yes 		Refers to ID by default, can also be name and code based on selected id scheme
Category option combo	No			Refers to ID
Attribute option combo 	No			Refers to ID (from version 2.16)
Value					No			Data value
Stored by				No			Refers to username of user who entered the value
Last updated			No			Date in ISO format
Comment					No			Free text comment
Follow up				No			true or false

An example of a CSV file which can be imported into DHIS 2 is seen below.

"dataelement","period","orgunit","categoryoptioncombo","attroptioncombo","value","storedby","timestamp"
"DUSpd8Jq3M7","201202","gP6hn503KUX","Prlt0C1RF0s",,"7","bombali","2010-04-17"
"DUSpd8Jq3M7","201202","gP6hn503KUX","V6L425pT3A0",,"10","bombali","2010-04-17"
"DUSpd8Jq3M7","201202","OjTS752GbZE","V6L425pT3A0",,"9","bombali","2010-04-06"
**/
?>
