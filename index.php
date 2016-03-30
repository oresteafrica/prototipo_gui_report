<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<link rel="stylesheet" href="css/index.css">
	<script type="text/javascript" src="js/jquery-2.1.4.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
require 'common_subs.php';
?>

<div id="main"><div id="menu">
<div class="bu">
<button id="bu_report">Redigir relatório</button>
<button id="bu_activity">Actividade</button>
<button onclick="javascript:window.print();" id="bu_print">Imprimir</button>
<button id="bu_masinfo" style="display:none;">Exportar para Masinfo</button>
<input type="checkbox" id="debug" style="display:'.$display_check_debug.';" /></div>

<?php
$ini_array = parse_ini_file('../cron/prototipo.ini');
$host = $ini_array['host'];
$user = $ini_array['user'];
$pass = $ini_array['pass'];
$dbna = $ini_array['dbna'];$con = pg_connect ("host=$host dbname=$dbna user=$user password=$pass"); 
if (!$con) { echo "<p>not connected</p>"; exit; } 
$result = pg_query($con, "SELECT MAX(level) FROM orgunitlevel");
$aresult = pg_fetch_array($result);
$oumaxlevel = $aresult[0];
$aous_level = create_array_from_table ($con, 'organisationunit', ['organisationunitid', 'name', 'parentid']);
$combo_periods = create_combo_from_periods($con);
echo $combo_periods;
$combo_forms = create_combo_from_datasets($con);
echo $combo_forms;
?>

<div class="tree">
<div id="treeinfo">Escolhe a estrutura navigando no diagrama de tipo árvore abaixo</div>
<div id="treeinfohide"></div>
<?php echo generatePageTree($aous_level); ?>
</div>

<label style="float:left;margin-left:10px;">nível </label><span id="spanniv" style="float:left;margin-left:4px;"></span>
<label style="float:left;margin-left:10px;">formulário n.  </label><span id="spanform" style="float:left;margin-left:4px;"></span>


</div>



<iframe id="report" frameborder="0" style="min-height:900px;">
<p>Este navegador (browser) não é atualizado.</p>
</iframe>


</div>

</body>
</html>
