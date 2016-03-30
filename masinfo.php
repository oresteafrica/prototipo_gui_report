<?php
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
require 'common_subs.php';

/*
$con : connection do database
$pe : periodid
$fo : datasetid
$no : orgunit id
*/

$ini_array = parse_ini_file('../cron/prototipo.ini');
$host = $ini_array['host'];
$user = $ini_array['user'];
$pass = $ini_array['pass'];
$dbna = $ini_array['dbna'];

$con = pg_connect ("host=$host dbname=$dbna user=$user password=$pass"); 
if (!$con) { echo "<p>not connected</p>"; exit; } 

$file_url = 'presco-01.pdf';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url);



?>