<?php
$xml_file = $_GET['f'];
$dom = new DOMDocument();
$dom->Load($xml_file);
$n_dataSets = $dom->getElementsByTagName('dataSet')->length;
if ($n_dataSets < 1) { echo 0; exit; }
$dataSets = $dom->getElementsByTagName('dataSet');
$option_tag = '';
foreach($dataSets as $dataSet){
	$option_tag .= '<option value="'.$dataSet->getAttribute('id').'">'.$dataSet->getAttribute('name').'</option>';
}
echo $option_tag;
?>
