<?php
require 'kint/Kint.class.php';
$debug = false;
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

$sql = "SELECT DISTINCT(id) FROM new_adm_ter ORDER BY id ASC";
$tabquery = $db->query($sql);
$array_ids = $tabquery->fetchAll(PDO::FETCH_ASSOC);

foreach ($array_ids as &$row) {
	do {
		$uid = generate_uid_dhis2();
	} while (in_array($uid,$array_ids));
	$row['uid'] = $uid;
}

$sql = "SELECT nome as name, id as uid, id as code, referencia as parent FROM new_adm_ter ORDER BY id ASC";
$tabquery = $db->query($sql);
$tabquery->setFetchMode(PDO::FETCH_ASSOC);
$array_table = [];
foreach ($tabquery as $tabres) {
	array_push($array_table, $tabres);
}

foreach ($array_table as &$row) {
	$id = $row['uid'];
	$parent = $row['parent'];
	$key_id = array_search($id, array_column($array_ids, 'id'));
	$key_parent = array_search($parent, array_column($array_ids, 'id'));
	$row['uid'] = $array_ids[$key_id]['uid'];
	$row['parent'] = $array_ids[$key_parent]['uid'];
}

$key_mgcas = array_search('MGCAS', $array_table);
$array_table[$key_mgcas]['parent'] = '';

$csv_string = 'name,uid,code,parent'."\n";

foreach ($array_table as $record) {
	$csv_string .= $record['name'].','.$record['uid'].','.$record['code'].','.$record['parent']."\n";
}

if ($debug) {
echo '<hr />';
!Kint::dump( $array_table );
echo '<hr />';
exit;
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=estrutura_organica.csv");
echo $csv_string;
//----------------------------------------------------------------------------------------------------------
function generate_uid_dhis2() {
    $schar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alpha = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$first = $char[rand(0, strlen($char) - 1)];
    $second = '';
    for ($i = 0; $i < 10; $i++) {
        $second .= $alpha[rand(0, strlen($alpha) - 1)];
    }
	$uid = $first.$second;
	return $uid;
}
//----------------------------------------------------------------------------------------------------------
?>
