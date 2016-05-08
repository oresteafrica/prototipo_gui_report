<?php

// test
// localhost/prototipo/csv_download.php?f=http://localhost/prototipo/uploads/2213522638.xml&p=201601&d=ckpiSgvwKxg
// f = file, p = period (yyyymm), d = dataset

if (! ( check_get('f') and check_get('p') and check_get('d') ) ) { exit; }

date_default_timezone_set('Africa/Maputo');

$period = $_GET['p'];
$dataSetId = $_GET['d'];
$xml_file = $_GET['f'];
$timestamp = date('YmdHis');
$name = 'name';
$id = 'id';
$ele = [];
$ous = [];
$csv = [];
$dataSets = null;
$dataSet = null;
$dataSetname = '';
$dataElements = null;
$organizationUnits = null;
$dom = new DOMDocument();
$dom->load($xml_file);
$dataSet = $dom->getElementsByTagName('dataSet');

if (!is_null($dataSet)) {
	foreach ($dataSet as $d) {
		if ($d->getAttribute('id')!=$dataSetId) { continue; }
		$dataSetname = $d->getAttribute('name');
		$nodes = $d->childNodes;
		foreach ($nodes as $node) {
			if ($node->nodeName!='organisationUnits') { continue; }
			foreach ($node->childNodes as $node2) {
				$ous[] = $node2->getAttribute('id');
			}
		}
		foreach ($nodes as $node) {
			if ($node->nodeName!='dataElements') { continue; }
			foreach ($node->childNodes as $node2) {
				$ele[] = $node2->getAttribute('id');
			}
		}
	}
} else { exit; }

$csv_title = array('dataelement','period','orgunit','categoryoptioncombo','attroptioncombo','value');

foreach($ous as $ou){
	foreach($ele as $e){
		$dataelement = $e;
		$orgunit = $ou;
		$categoryoptioncombo = '';
		$attroptioncombo = '';
		$value = rand(1, 10);
		$csv[] = array($dataelement, $period, $orgunit, $categoryoptioncombo, $attroptioncombo, $value);
	}
}

array_unshift($csv,$csv_title);
array_to_csv($csv, 'dhis2_fake_data_'.$dataSetname.'_'.$timestamp.'.csv');

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
function check_get ($var) {
	if($_GET[$var] === '') { return false; }
	if($_GET[$var] === false) { return false; }
	if($_GET[$var] === null) { return false; }
	if(!isset($_GET[$var])) { return false; }
	if(empty($_GET[$var])) { return false; }
	return true;
}
//----------------------------------------------------------------------------------------------------------
function is_url_exist($url) {
    $ch = curl_init($url);    
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($code == 200){
       $status = true;
    } else {
      $status = false;
    }
    curl_close($ch);
	return $status;
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
