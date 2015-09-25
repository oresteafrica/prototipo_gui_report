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

//$host = '127.0.0.1';
$host = 'localhost';
$user = 'oreste'; 
$pass = 'vaffax'; 
$dbName = 'mgcas9_dhis2';
echo "<p>before create connection</p>"; 
$con = pg_connect ("host=$host dbname=$dbName user=$user password=$pass"); 
echo "<p>After connection is created</p>"; 
if (!$con) { echo "<p>not connected</p>"; exit; } 

echo '<style></style>';

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
