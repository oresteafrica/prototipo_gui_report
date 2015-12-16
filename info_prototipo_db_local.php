<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<style>
td {
border:solid black 1px;
}
table {
margin-bottom:20px;
}
caption {
font-weight:bold;
color:red;
}
</style>
</head>
<body>
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

$ini_array = parse_ini_file('../cron/prototipo.ini');
$host = $ini_array['host'];
$user = $ini_array['user'];
$pass = $ini_array['pass'];
$dbna = $ini_array['dbna'];

echo "<p>before create connection</p>"; 
$con = pg_connect ("host=$host dbname=$dbna user=$user password=$pass"); 
echo "<p>After connection is created</p>"; 
if (!$con) { echo "<p>not connected</p>"; exit; } 

echo '<style></style>';


list_all_tables ($con,$dbna);

list_table ($con, 'datavalue', ['dataelementid', 'periodid', 'sourceid', 'value', 'storedby']);
list_table ($con, 'organisationunit', ['organisationunitid', 'uid', 'name', 'parentid', 'coordinates']);
list_table ($con, 'period', ['periodid', 'periodtypeid', 'startdate', 'enddate']);
list_table ($con, 'periodtype', ['periodtypeid', 'name']);
list_table ($con, 'dataelement', ['dataelementid', 'uid', 'name', 'domaintype', 'aggregationtype']);
list_table ($con, 'dataelementgroup', ['dataelementgroupid', 'uid', 'name']);
list_table ($con, 'dataelementgroupmembers', ['dataelementgroupid', 'dataelementid']);
list_table ($con, 'dataset', ['datasetid', 'name', 'periodtypeid']);
list_table ($con, 'datasetmembers', ['datasetid', 'dataelementid']);
list_table ($con, 'datasetsource', ['datasetid', 'sourceid']);
list_table ($con, 'users', ['userid', 'username', 'openid', 'password', 'passwordlastupdated', 'lastlogin', 'restoretoken', 'restorecode', 'restoreexpiry', 'selfregistered', 'invitation', 'disabled', 'created']);
list_table ($con, 'userrole', ['userroleid', 'uid', 'code', 'created', 'lastupdated', 'name', 'description', 'userid', 'publicaccess']);
list_table ($con, 'userroleauthorities', ['userroleid', 'authority']);
list_table ($con, 'userdatavieworgunits', ['userinfoid', 'organisationunitid']);
list_table ($con, 'usermembership', ['organisationunitid', 'userinfoid']);
list_table ($con, 'usersetting', ['userinfoid', 'name', 'value']);


function list_all_tables ($con,$dbName) {
//$result = pg_query($con, 'SELECT * FROM ' . $dbName . '.tables');
$result = pg_query($con, 'SELECT * FROM information_schema.tables where table_schema = \'public\'');
echo '<hr />';
echo '<h3>'.$dbName.'</h3>';
echo '<hr />';
while ($row = pg_fetch_row($result)) {
	echo implode(' | ',$row) . '<br />';
}
echo '<hr />';
}

function list_table ($con,$table, $fields) {
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
?>
</body>
</html>
