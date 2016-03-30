<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
<meta charset='utf-8'>
</head>
<body>


<?php

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
require 'common_subs.php';

/*
$con : connection do database
$pe : periodid
$fo : datasetid
$no : orgunit id
$ous: orgunit all last children id
$le : hierarchy level
$twig : compulsory variable for generating template
$de if == A then $debug = true
$debug if true displays var info
*/


if (! check_get('pe') ) { exit; }
$pe = $_GET["pe"];
if (! check_get('fo') ) { exit; }
$fo = $_GET["fo"];
if (! check_get('no') ) { exit; }
$no = $_GET["no"];
if (! check_get('ous') ) { exit; }
$ous = $_GET["ous"];
if (! check_get('le') ) { exit; }
$le = $_GET["le"];
if (! check_get('de') ) { exit; }
$de = $_GET["de"];


$prototipo_masinfo_ou = array(
'71' =>	'????????????',
'70' =>	'AFRMOZ009002',
'69' =>	'AFRMOZ009003',
'72' =>	'AFRMOZ010002',
'73' =>	'AFRMOZ010004',
'74' =>	'AFRMOZ010007',
'102' => 'AFRMOZ009002001',
'75' => 'AFRMOZ009002002',
'76' => 'AFRMOZ009002003',
'77' => 'AFRMOZ009002004',
'78' => 'AFRMOZ009002005',
'79' => 'AFRMOZ009002006',
'80' => 'AFRMOZ009003001',
'81' => 'AFRMOZ009003002',
'82' => 'AFRMOZ009003003',
'83' => 'AFRMOZ009003004',
'84' => 'AFRMOZ009003005',
'85' => 'AFRMOZ009003006',
'86' => 'AFRMOZ010002001',
'87' => 'AFRMOZ010002002',
'88' => 'AFRMOZ010004001',
'89' => 'AFRMOZ010004002',
'90' => 'AFRMOZ010004003',
'91' => 'AFRMOZ010004004',
'92' => 'AFRMOZ010004005',
'94' => 'AFRMOZ010007001',
'95' => 'AFRMOZ010007002',
'96' => 'AFRMOZ010007003',
'97' => 'AFRMOZ010007004',
'319' => 'AFRMOZ009012',
'320' => 'AFRMOZ009012001',
'321' => 'AFRMOZ009012002',
'322' => 'AFRMOZ009012003',
);


require 'kint/Kint.class.php';
require_once 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('tpl');
$twig = new Twig_Environment($loader);

$twig = new Twig_Environment($loader, array(
    'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());

$ini_array = parse_ini_file('../cron/prototipo.ini');
$host = $ini_array['host'];
$user = $ini_array['user'];
$pass = $ini_array['pass'];
$dbna = $ini_array['dbna'];

$con = pg_connect ("host=$host dbname=$dbna user=$user password=$pass"); 
if (!$con) { echo "<p>not connected</p>"; exit; } 

$localhosts = array(
    '127.0.0.1',
    'localhost',
	'::1'
);

$display_check_debug = 'none';
if(in_array($_SERVER['REMOTE_ADDR'], $localhosts)){
	$display_check_debug = 'inline';
}

if ( $de == 'A' ) { $debug = true; } else { $debug = false; }

if ( $le < 3 ) { not_yet(); exit; }

// ini form name. Variable $fo = datasetid coming from table datavalue via ajax
$result = pg_query($con, 'SELECT name FROM dataset WHERE datasetid = ' . $fo);
if (!$result) { echo "<p>Error opening dataset</p>\n"; exit; }
$form_name = pg_fetch_assoc($result)['name'];
// end form name

// ini form template
$form_template_file = $form_name . '_' . $le . '_masinfo.html' ;
if (! file_exists('tpl/'.$form_template_file)) { echo "<p>template file does not exist</p>"; exit; }
// end form template

// ini period details. Variable $pe = periodid coming from table datavalue via ajax
$result = pg_query($con, 'SELECT periodtypeid, startdate, enddate FROM period WHERE periodid = ' . $pe);
if (!$result) { echo "<p>Error opening period</p>\n"; exit; }
$r = pg_fetch_assoc($result);
$month_n = (int) substr($r['startdate'], 6, 2);
$startday = substr($r['startdate'], 8, 2);
$startmonth_pt = month_pt_from_month_num(substr($r['startdate'], 6, 2));
$startyear = substr($r['startdate'], 0, 4);
$endday = substr($r['enddate'], 8, 2);
$endmonth_pt = month_pt_from_month_num(substr($r['enddate'], 6, 2));
$endyear = substr($r['enddate'], 0, 4);	
$start_end_text = 'de ' . $startday . '/' . $startmonth_pt. '/' . 
	$startyear . ' até ' . $endday . '/' . $endmonth_pt. '/' . $endyear;
// end period details

// ini chosen ou and parent ou. Variable $no = sourceid (ou id) coming from table datavalue via ajax
$result = pg_query($con, 'SELECT name, parentid FROM organisationunit WHERE organisationunitid = ' . $no);
if (!$result) { echo "<p>Error opening organisationunit</p>\n"; exit; }
while ($row = pg_fetch_row($result)) {
$chosen_ou = $row[0];
$parentid_chosen_ou = $row[1];
}
if (!$parentid_chosen_ou) { $parentid_chosen_ou = 0; }
$result = pg_query($con, 'SELECT name FROM organisationunit WHERE organisationunitid = ' . $parentid_chosen_ou);
if (!$result) { echo '<p>Error opening organisationunit, $parentid_chosen_ou = '.$parentid_chosen_ou.'</p>'; exit; }
$parentname_chosen_ou = pg_fetch_assoc($result)['name'];
// end chosen ou and parent ou.

// ini list of data elements ids according to variable $no coming from table datavalue via ajax
$dataelementids = [];
$result = pg_query($con, 'SELECT dataelementid FROM datasetmembers WHERE datasetid = ' . $fo);
if (!$result) { echo "<p>Error opening datasetmembers</p>\n"; exit; }
while ($row = pg_fetch_row($result)) {
	array_push($dataelementids, $row[0] );
}
// end list of data elements ids

// ini list of data elements names where value types = integer
$fulldataelementnames = [];
foreach ($dataelementids as $di) {
	$query = 'SELECT name FROM dataelement WHERE valuetype = \'int\' AND dataelementid = ' . $di ;
	$result = pg_query($con, $query );
	if (!$result) { echo "<p>Error opening dataelement</p>\n"; exit; }
	$resultname = pg_fetch_assoc($result)['name'];
	if ($resultname) { array_push($fulldataelementnames, $resultname); }
}
sort($fulldataelementnames, SORT_STRING);
// end list of data elements names where value types = integer

// ini list of data elements names where value types != integer
$notintdataelementnames = [];
foreach ($dataelementids as $di) {
	$query = 'SELECT name FROM dataelement WHERE valuetype != \'int\' AND dataelementid = ' . $di ;
	$result = pg_query($con, $query );
	if (!$result) { echo "<p>Error opening dataelement</p>\n"; exit; }
	$resultname = pg_fetch_assoc($result)['name'];
	if ($resultname) { array_push($notintdataelementnames, $resultname); }
}
// end list of data elements names where value types != integer

$entidade = '';
$localidade = '';
$instituicao = '';
$mes = $endmonth_pt;
$ano = $endyear;
$pa = '';
$distrito = '';
$provincia = '';


		// ini list of children names and ids under chosen ou
		$direct_children = [];
		$result = pg_query($con, 'SELECT organisationunitid, name FROM organisationunit WHERE parentid = ' . $no);
		if (!$result) { echo "<p>Error opening organisationunit</p>\n"; exit; }
		while ($row = pg_fetch_row($result)) {
		array_push($direct_children , ['id' => $row[0], 'name' => $row[1]] );
		}
		// end list of children names and ids under chosen ou
	
		// ini value in datavalues for each child each data element within period id
		$result_from_direct_children = [];
		foreach ($direct_children as $dc) {
			foreach ($dataelementids as $di) {
				$where = 'sourceid = ' . $dc['id'] . ' AND periodid = ' . $pe . ' AND dataelementid = ' . $di;
				$query = 'SELECT '.
					'( SELECT name FROM organisationunit WHERE datavalue.sourceid = organisationunit.organisationunitid )'.
					' as ou , '.
					'value, '.
					'( SELECT name FROM dataelement WHERE dataelement.dataelementid = datavalue.dataelementid )'.
					' as dataelementname '.
					'FROM datavalue WHERE ' . $where;
				$result = pg_query($con, $query );
				if (!$result) { echo "<p>Error opening datavalue</p>\n"; exit; }
				$res_all = pg_fetch_assoc($result);
				if ( $res_all ) { array_push($result_from_direct_children, $res_all); }
			}
		}
		// end value in datavalues for each child each data element within period id

		// ini build PAs table from array $result_from_direct_children
		$aggregated_sum = [];
		$row = [];
		foreach ($direct_children as $dc) {
			foreach ($fulldataelementnames as $dn) {
				$aggregated_value = 0;
				foreach ($result_from_direct_children as $rc) {
					if ( $rc['ou'] == $dc['name'] and $rc['dataelementname'] ==  $dn ) { $aggregated_value += $rc['value']; }
				}
				$row[$dn] = $aggregated_value;
			}
			$aggregated_sum[$dc['name']] = $row;
		}
		// end build PAs table from array $result_from_direct_children
		
		$distrito = $chosen_ou;
		$provincia = $parentname_chosen_ou;

		if (! $debug) {
			$template_array = array(
				'rep_oid'			=> $no,
				'rep_mas_ou_code'	=> $prototipo_masinfo_ou[$no],
				'rep_dst'			=> $chosen_ou,
				'rep_mes_n' 		=> $month_n,
				'rep_mes' 			=> $mes,
				'rep_ano' 			=> $ano,
				'rep_prv' 			=> $parentname_chosen_ou,
				'aggregated_sum'	=> $aggregated_sum
			);
			$template = $twig->loadTemplate($form_template_file);
			$masinfo_table_var = $template->render($template_array);
			echo $masinfo_table_var;
			
			file_put_contents('prototipo_masinfo.xls', $masinfo_table_var);


		}
		

//====================================== ini bebug ===========================================================
if ($debug) {

echo '<h2>Server</h2>';
echo '<p>$_SERVER[\'REMOTE_ADDR\'] = ' . $_SERVER['REMOTE_ADDR'] . '</p>';
echo '<hr />';

echo '<h2>From ajax</h2>';
echo '<p>periodid ($pe) = <b>'.$pe.
	'</b></p><p>datasetid ($fo) = <b>'.$fo.
	'</b></p><p>orgunit id ($no) = <b>'.$no.
	'</b></p><p>masinfo orgunit id ($masinfo_ou[$no]) = <b>'.$prototipo_masinfo_ou[$no].
	'</b></p><p>orgunit all last children id ($ous) = <b>'.$ous.
	'</b></p><p>Hierarchy level ($le) = <b>'.$le.'</b></p>';
echo '<hr />';
echo '<h2>From database</h2>';
echo '<p>$parentname_chosen_ou = ' . $parentname_chosen_ou . ' (' . $parentid_chosen_ou . ')</p>';
echo '<p>$month_n = ' . $month_n . '</p>';
echo '<p>$endmonth_pt = ' . $endmonth_pt . '</p>';
echo '<p>$endyear = ' . $endyear . '</p>';
echo '<p>$form_name = ' . $form_name . '</p>';
echo '<p>$chosen_ou = ' . $chosen_ou . ' (' . $no . ')</p>';
echo '<hr />';
echo '<h2>Processed</h2>';
echo '<p>$entidade = <b>' . $entidade . '</b></p>';
echo '<p>$localidade = <b>' . $localidade . '</b></p>';
echo '<p>$instituicao = <b>' . $instituicao . '</b></p>';
echo '<p>$mes = <b>' . $mes . '</b></p>';
echo '<p>$ano = <b>' . $ano . '</b></p>';
echo '<p>$pa = <b>' . $pa . '</b></p>';
echo '<p>$distrito = <b>' . $distrito . '</b></p>';
echo '<p>$provincia = <b>' . $provincia . '</b></p>';
echo '<hr />';

!Kint::dump( $direct_children );

echo '<label>$dataelementids</label><br /><textarea style="width:80%">' . implode('; ', $dataelementids) .  '</textarea>';

echo '<br />';
echo '<br />';
!Kint::dump( $fulldataelementnames );

echo '<br />';
echo '<br />';
!Kint::dump( $notintdataelementnames );

echo '<br />';
!Kint::dump( $result_from_direct_children );

echo '<br />';
!Kint::dump( $aggregated_sum );

if ($le == 3) {
echo '<br />';
echo $direct_children[0]['name'];
!Kint::dump( $aggregated_sum[$direct_children[0]['name']] );
}

echo '<label>last $querypa</label><br /><textarea style="width:80%">' . $querypa .  '</textarea>';
echo '<br />';
!Kint::dump( $result_from_pa );
echo '<br />';
!Kint::dump( $form_value );
echo '<br />';
!Kint::dump( $form_text );

return;

}
//====================================== end bebug ===========================================================


?>

</body>
</html>

