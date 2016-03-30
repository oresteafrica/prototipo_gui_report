<?php
//----------------------------------------------------------------------------------------------------------
function not_yet () {
	echo '<h3>É possível redigir o relatório apenas a nível distrital</h3>';
	echo '<p>Níveis acima do distrital serão redigidos pelo software Masinfo/Devinfo</p>';
}
//----------------------------------------------------------------------------------------------------------
function generateArrayTree($datas, $parent = 0, $depth=0){
    if($depth > 4) return ''; // Make sure not to have an endless recursion
	$ArrayTree = [];
	$nivel = (( $depth / 1 ) + 1);
	$tdstyle = 'style="border:solid black 1px;padding:2px;"';
    $tree = '<tr>';
    for($i=0, $ni=count($datas); $i < $ni; $i++){
        if($datas[$i]['parentid'] == $parent){
            $tree .= '<td '. $tdstyle . '">' . $nivel . '</td>';
            $tree .= '<td '. $tdstyle . '">' . $datas[$i]['organisationunitid'] . '</td>';
            $tree .= '<td '. $tdstyle . '">' . $datas[$i]['parentid'] . '</td>';
            $tree .= '<td '. $tdstyle . '">' . $datas[$i]['name'] . '</td>';
            $tree .= generateArrayTree($datas, $datas[$i]['organisationunitid'], $depth+1);
        }
    }
    $tree .= '</tr>';
    return $tree;

}
//----------------------------------------------------------------------------------------------------------
function generateTableTree($datas, $parent = 0, $depth=0){
    if($depth > 4) return ''; // Make sure not to have an endless recursion
	$nivel = (( $depth / 1 ) + 1);
	$tdstyle = 'style="border:solid black 1px;padding:2px;"';
    $tree = '<tr>';
    for($i=0, $ni=count($datas); $i < $ni; $i++){
        if($datas[$i]['parentid'] == $parent){
            $tree .= '<td '. $tdstyle . '">' . $nivel . '</td>';
            $tree .= '<td '. $tdstyle . '">' . $datas[$i]['organisationunitid'] . '</td>';
            $tree .= '<td '. $tdstyle . '">' . $datas[$i]['parentid'] . '</td>';
            $tree .= '<td '. $tdstyle . '">' . $datas[$i]['name'] . '</td>';
            $tree .= generateTableTree($datas, $datas[$i]['organisationunitid'], $depth+1);
        }
    }
    $tree .= '</tr>';
    return $tree;
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
function array_datasets ($con) {
$result = pg_query($con, 'SELECT datasetid, name FROM dataset');
if (!$result) { echo "<p>Error opening periods</p>\n"; exit; }
$array_dataset = pg_fetch_all($result);
return $array_dataset;
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