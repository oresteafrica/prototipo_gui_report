<?php

$file = 'prototipo_masinfo.xls';

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");
readfile($file);
    
    
    
    


/*

$file_url = 'presco-01.pdf';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url);


header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=".$file."");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: binary/octet-stream");


*/




?>
