<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<script type="text/javascript" src="js/jquery-2.1.4.js"></script>
<script type="text/javascript" src="js/info_prototipo_db_reports.js"></script>
<link rel="stylesheet" href="css/info_prototipo_db_reports.css">
</head>
<body>

<?php
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
require 'kint/Kint.class.php';

require_once 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('tpl');
$twig = new Twig_Environment($loader);

$twig = new Twig_Environment($loader, array(
    'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());


if (! check_get('option') ) { exit; }
$option = $_GET['option'];

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

switch ($option) {
    case 1:
		echo '<div id="main"><div id="menu">';
		echo '<div class="bu"><button id="bu_report">Redigir relatório</button>'.
		'<button onclick="javascript:window.print();" id="bu_print">Imprimir</button>'.
		'<input type="checkbox" id="debug" style="display:'.$display_check_debug.';" /></div>';

		$result = pg_query($con, "SELECT MAX(level) FROM orgunitlevel");
		$aresult = pg_fetch_array($result);
		$oumaxlevel = $aresult[0];
		$aous_level = create_array_from_table ($con, 'organisationunit', ['organisationunitid', 'name', 'parentid']);

		$combo_periods = create_combo_from_periods($con);
		echo $combo_periods;
		$combo_forms = create_combo_from_datasets($con);
		echo $combo_forms;

		$tree = '<div class="tree">'.
		'<div id="treeinfo">Escolhe a estrutura navigando no diagrama de tipo árvore abaixo</div>'.
		'<div id="treeinfohide"></div>'.generatePageTree($aous_level).'</div>';

		echo $tree;

		echo '</div><div id="report"><h3>Espaço para o relatório</h3></div></div>';

        break;
    case 2:
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
		
		write_report ($con, $pe, $fo, $no, $ous, $le, true, $twig, $de);
        
        break;
    case 3:
 
        break;
    default:
       echo '<p style="color:red;font-weight:bold;">incorrect option</p>'; exit;
}

//----------------------------------------------------------------------------------------------------------
function not_yet () {
	echo '<h3>É possível redigir o relatório apenas a nível distrital</h3>';
	echo '<p>Níveis acima do distrital serão redigidos pelo software Masinfo/Devinfo</p>';
}
//----------------------------------------------------------------------------------------------------------
function write_report ($con, $pe, $fo, $no, $ous, $le, $paper, $twig, $de) {
/*
$con : connection do database
$pe : periodid
$fo : datasetid
$no : orgunit id
$ous: orgunit all last children id
$le : hierarchy level
$paper : 1/0 paper/masinfo 
$twig : compulsory variable for generating template
$de if == A then $debug = true
$debug if true displays var info
*/

if ( $de == 'A' ) { $debug = true; } else { $debug = false; }

// ini form name. Variable $fo = datasetid coming from table datavalue via ajax
$result = pg_query($con, 'SELECT name FROM dataset WHERE datasetid = ' . $fo);
if (!$result) { echo "<p>Error opening dataset</p>\n"; exit; }
$form_name = pg_fetch_assoc($result)['name'];
// end form name

// ini form template
$form_template_file = $form_name . '_' . $le . '.html' ;
if (! file_exists('tpl/'.$form_template_file)) { echo "<p>template file does not exist</p>"; exit; }
// end form template

// ini period details. Variable $pe = periodid coming from table datavalue via ajax
$result = pg_query($con, 'SELECT periodtypeid, startdate, enddate FROM period WHERE periodid = ' . $pe);
if (!$result) { echo "<p>Error opening period</p>\n"; exit; }
$r = pg_fetch_assoc($result);
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

$entidade = '';
$localidade = '';
$instituição = '';
$mes = $endmonth_pt;
$ano = $endyear;
$pa = '';
$distrito = '';
$provincia = '';

switch ($le) {
//---- Nacional -----------------
	case 1:
//---- Provincial ----------------
 	case 2:
	break;
//---- Distrital -----------------
    case 3:

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
				'rep_cho'			=> $chosen_ou,
				'rep_mes' 			=> $mes,
				'rep_ano' 			=> $ano,
				'rep_par' 			=> $parentname_chosen_ou,
				'aggregated_sum'	=> $aggregated_sum
			);
			$template = $twig->loadTemplate($form_template_file);
			echo $template->render($template_array);
		}
		
	break;

//---- Posto Administrativo ------
    case 4:
		$pa = $chosen_ou;
		$distrito = $parentname_chosen_ou;
		$result = pg_query($con, 'SELECT parentid FROM organisationunit WHERE organisationunitid = ' . $parentid_chosen_ou);
		if (!$result) { echo "<p>Error opening organisationunit</p>\n"; exit; }
		$temp = pg_fetch_assoc($result)['parentid'];
		$result = pg_query($con, 'SELECT name FROM organisationunit WHERE organisationunitid = ' . $temp);
		if (!$result) { echo "<p>Error opening organisationunit</p>\n"; exit; }
		$provincia = pg_fetch_assoc($result)['name'];
		
		// ini value in datavalues for chosen PA each data element within period id
		$result_from_pa = [];
		foreach ($dataelementids as $di) {
			$wherepa = 'sourceid = ' . $no . ' AND periodid = ' . $pe . ' AND dataelementid = ' . $di;
			$querypa = 'SELECT '.
				'( SELECT name FROM dataelement WHERE dataelement.dataelementid = datavalue.dataelementid )'.
				' as dataelementname, '.
				'value '.
				'FROM datavalue WHERE ' . $wherepa;
			$result = pg_query($con, $querypa );
			if (!$result) { echo "<p>Error opening datavalue</p>\n"; exit; }
			$res_all = pg_fetch_assoc($result);
			if ( $res_all ) { array_push($result_from_pa, $res_all); }
		}
		// end value in datavalues for chosen PA each data element within period id

		$form_value = [];
		if ($result_from_pa) {

			// ini build form table from array $result_from_pa
			foreach ($fulldataelementnames as $dn) {
				$arsearch = array_search($dn,array_column($result_from_pa, 'dataelementname'));
				if ( $arsearch !== false ) {
					$form_value[$dn] = $result_from_pa[$arsearch]['value'];
					// array_push($form_value, [$dn,$result_from_pa[$arsearch]['value']]);
				} else {
					$form_value[$dn] = '';
					//array_push($form_value, [$dn,'']);
				}
			}
			// end build form table from array $result_from_pa

			if (! $debug) {
				$template_array = array(
					'rep_cho'			=> $chosen_ou,
					'rep_mes' 			=> $mes,
					'rep_ano' 			=> $ano,
					'rep_par' 			=> $parentname_chosen_ou,
					'rep_ent' 			=> $entidade,
					'rep_loc' 			=> $localidade,
					'rep_ins' 			=> $instituição,
					'rep_frm'			=> $form_value
				);
				$template = $twig->loadTemplate($form_template_file);
				echo $template->render($template_array);
			}
		} else {
			echo '<p>Não existem dados de acordo com as informações seguintes</p>';
			echo '<p>ano <b>' . $endyear . '</b></p>';
			echo '<p>mês <b>' . $endmonth_pt . '</b></p>';
			echo '<p>Posto Administrativo <b>' . $pa . '</b></p>';
			echo '<p>Distrito <b>' . $distrito . '</b></p>';
			echo '<p>Província <b>' . $provincia . '</b></p>';
			echo '<p>Formulário <b>' . $form_name . '</b></p>';
		}
	
	break;

//--------------------------------
    default:

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
	'</b></p><p>orgunit all last children id ($ous) = <b>'.$ous.
	'</b></p><p>Hierarchy level ($le) = <b>'.$le.'</b></p>';
echo '<hr />';
echo '<h2>From database</h2>';
echo '<p>$parentname_chosen_ou = ' . $parentname_chosen_ou . ' (' . $parentid_chosen_ou . ')</p>';
echo '<p>$endmonth_pt = ' . $endmonth_pt . '</p>';
echo '<p>$endyear = ' . $endyear . '</p>';
echo '<p>$form_name = ' . $form_name . '</p>';
echo '<p>$chosen_ou = ' . $chosen_ou . ' (' . $no . ')</p>';
echo '<hr />';
echo '<h2>Processed</h2>';
echo '<p>$entidade = <b>' . $entidade . '</b></p>';
echo '<p>$localidade = <b>' . $localidade . '</b></p>';
echo '<p>$instituição = <b>' . $instituição . '</b></p>';
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

return;

}
//====================================== end bebug ===========================================================

return;
}
//----------------------------------------------------------------------------------------------------------
function generatePageTree($datas, $parent = 0, $depth=0){
    if($depth > 4) return ''; // Make sure not to have an endless recursion
    $tree = '<ul>';
    for($i=0, $ni=count($datas); $i < $ni; $i++){
        if($datas[$i]['parentid'] == $parent){
            $tree .= '<li id="litree_'.(( $depth / 1 ) + 1) .'">';
            $tree .= '<a class="outreeitem" id="'.$datas[$i]['organisationunitid'].'">'.$datas[$i]['name'].'</a>';
            $tree .= generatePageTree($datas, $datas[$i]['organisationunitid'], $depth+1);
            $tree .= '</li>';
        }
    }
    $tree .= '</ul>';
    return $tree;
}
//----------------------------------------------------------------------------------------------------------
function create_array_with_children_from_table ($con, $table, $fields, $additional_fields, $keyfield1, $keyfield2) {
$selected = implode(', ',$fields);
$result = pg_query($con, "SELECT $selected FROM $table");
if (!$result) { echo "<p>Error opening $table</p>\n"; exit; }
$array_table = [];
if ($additional_fields) {
$additional = [];
foreach ($additional_fields as $field) { $additional[$field] = ''; }
while ($row = pg_fetch_assoc($result)) { array_push($array_table, array_merge ($row, $additional)); }
$level_ids = array_column($array_table, $keyfield1);
$max_level = 0;
foreach ($array_table as $k => $r) {
	$id = $r[$keyfield1];
	$pri = $r[$keyfield2];
	$i = 0;
	while ( $pri !== null ) {
		$i++;
		$k2 = array_search($pri, $level_ids);
		$pri = $array_table[$k2][$keyfield2];
	}
	$array_table[$k][$additional_fields[0]] = $i + 1;
	if ( ($i + 1) > $max_level ) { $max_level = $i + 1; }
}

} else {
while ($row = pg_fetch_assoc($result)) { array_push($array_table, $row); }
}

return $array_table;
}
//----------------------------------------------------------------------------------------------------------
function create_combo_from_datasets ($con) {
$periods['en'] = ['Daily','Weekly','Monthly','BiMonthly','Quarterly','SixMonthly','SixMonthlyApril','Yearly','FinancialApril','FinancialJuly','FinancialOct'];
$periods['pt'] = ['Diário','Semanal','Mensal','Bimestral','Trimestral','Semestral','Semestral Abril','Anual','Financiário Abril','Financiário Julho','Financiário Outubro'];
$result = pg_query($con, 'SELECT datasetid, name, (SELECT name FROM periodtype WHERE dataset.periodtypeid = periodtype.periodtypeid) AS periodtypename FROM dataset');
if (!$result) { echo "<p>Error opening periods</p>\n"; exit; }
$combo = '<div class="combo"><label>Escolhe o formulário</label><select id="combo_forms_rep">';
while ($r = pg_fetch_row($result)) {
	$nomeperiodo =  $periods['pt'][array_search($r[2], $periods['en'])];
	$form = $r[1];
	$option_text = $form . ' (' . $nomeperiodo . ')';
	$combo .= '<option value="'.$r[0].'">'.$option_text.'</option>';
}
$combo .= '</select></div>';
return $combo;
}
//----------------------------------------------------------------------------------------------------------
function month_pt_from_month_num ($num) {
$meses = ['Janeiro','Fevereiro','Marco','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$i = (int) $num -1;
return $meses[$i];
}
//----------------------------------------------------------------------------------------------------------
function create_combo_from_periods ($con) {
// periodi definiti, ma non necessariamente disponibili, dipende da datavalue
$periods['en'] = ['Daily','Weekly','Monthly','BiMonthly','Quarterly','SixMonthly','SixMonthlyApril','Yearly','FinancialApril','FinancialJuly','FinancialOct'];
$periods['pt'] = ['Diário','Semanal','Mensal','Bimestral','Trimestral','Semestral','Semestral Abril','Anual','Financiário Abril','Financiário Julho','Financiário Outubro'];
$result = pg_query($con, 'SELECT period.periodid, (SELECT periodtype.name FROM periodtype WHERE periodtype.periodtypeid = period.periodtypeid) AS periodtypename, period.startdate, period.enddate FROM period');
if (!$result) { echo "<p>Error opening periods</p>\n"; exit; }
$combo = '<div class="combo"><label>Escolhe o período pré-definido</label><select id="combo_periods_rep">';
while ($r = pg_fetch_row($result)) {
	$nomeperiodo =  $periods['pt'][array_search($r[1], $periods['en'])];
	
	$startday = substr($r[2], 8, 2);
	$startmonth_pt = month_pt_from_month_num(substr($r[2], 6, 2));
	$startyear = substr($r[2], 0, 4);

	$endday = substr($r[3], 8, 2);
	$endmonth_pt = month_pt_from_month_num(substr($r[3], 6, 2));
	$endyear = substr($r[3], 0, 4);
	
	$option_text = $nomeperiodo . ' - de ' . $startday . '/' . $startmonth_pt. '/' . $startyear . ' até ' . $endday . '/' . $endmonth_pt. '/' . $endyear;
	$combo .= '<option value="'.$r[0].'">'.$option_text.'</option>';
}

$combo .= '</select></div>';
return $combo;
}
//----------------------------------------------------------------------------------------------------------
function create_combo_from_array_ous ($array) {
// $field1 = value $field2 = text $field3 = group
$combo = '<div class="combo"><label>Escolhe a estrutura hierarquica de referência</label><select id="combo_ou_rep">';
$nacional = '<optgroup label="Nacional">';
$provincial = '<optgroup label="Provincial">';
$distrital = '<optgroup label="Distrital">';
foreach ($array as $k => $r) {
	switch ($r['level']) {
		case 1:
			$nacional .= '<option value="'.$r['organisationunitid'].'">'.$r['name'].'</option>';
			break;
		case 2:
			$provincial .= '<option value="'.$r['organisationunitid'].'">'.$r['name'].'</option>';
			break;
		case 3:
			$distrital .= '<option value="'.$r['organisationunitid'].'">'.$r['name'].'</option>';
			break;
	}
}
$nacional .= '</optgroup>';
$provincial .= '</optgroup>';
$distrital .= '</optgroup>';
$combo .= $nacional.$provincial.$distrital.'</select></div>';
return $combo;
}
//----------------------------------------------------------------------------------------------------------
function create_array_from_table ($con, $table, $fields) {
$selected = implode(', ',$fields);
$result = pg_query($con, "SELECT $selected FROM $table");
if (!$result) { echo "<p>Error opening $table</p>\n"; exit; }
$array_table = [];
while ($row = pg_fetch_assoc($result)) { array_push($array_table, $row); }
return $array_table;
}
//----------------------------------------------------------------------------------------------------------
function list_table ($con, $table, $fields) {
$selected = implode(', ',$fields);
$result = pg_query($con, "SELECT $selected FROM $table");
if (!$result) { echo "<p>Error opening $table</p>\n"; exit; }
$th = implode('</th><th>',$fields);
echo '<table cellpadding="2" cellspacing="0"><caption>'.$table.'</caption><thead><tr><th>'.$th.'</th></tr></thead><tbody>';
while ($row = pg_fetch_row($result)) {
	echo '<tr>';
	for ($i = 0; $i < count($fields); $i++) {
		echo '<td>'.$row[$i].'</td>';
	}
	echo '</tr>';
}
echo '</tbody></table>';
}
//----------------------------------------------------------------------------------------------------------
function check_get ($var) {

if($_GET[$var] === '') { echo '<p style="color:red;font-weight:bold;">'.$var.' is an empty string</p>'; return false; }
if($_GET[$var] === false) { echo '<p style="color:red;font-weight:bold;">'.$var.' is false</p>'; return false; }
if($_GET[$var] === null) { echo '<p style="color:red;font-weight:bold;">'.$var.' is null</p>'; return false; }
if(!isset($_GET[$var])) { echo '<p style="color:red;font-weight:bold;">'.$var.' is not set</p>'; return false; }
if(empty($_GET[$var])) { echo '<p style="color:red;font-weight:bold;">'.$var.' is empty</p>'; return false; }


return true;
}
//----------------------------------------------------------------------------------------------------------
?>

</body>
</html>
