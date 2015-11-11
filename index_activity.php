<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
</head>
<body>

<style>
div {
display:inline-block;
margin-right:40px;
vertical-align:top;
}
</style>

<?php
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
require 'common_subs.php';

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

$aous_level = create_array_from_table ($con, 'organisationunit', ['organisationunitid', 'name', 'parentid']);
$array_tree = generateArrayTree($aous_level);
$array_datasets = array_datasets($con);

echo '<div><table style="border-collapse:collapse;">';
echo '<thead><th>nível</th><th>id</th><th>pid</th><th>nome</th></thead><tbody>';
echo generateTableTree($aous_level).'</tbody></table></div>';



echo '<div>';
!Kint::dump($array_datasets);
//!Kint::dump($aous_level);

echo $array_datasets[1]['name'];

echo '</div>';

//!Kint::dump($array_tree);
//echo '<table style="border-collapse:collapse;">'.generateTableTree($aous_level).'</table>';


?>

</body>
</html>
