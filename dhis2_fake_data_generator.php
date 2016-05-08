<?php
if(isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"]== UPLOAD_ERR_OK) {
	$UploadDirectory	= 'uploads/';
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){ echo 0; }
	if ($_FILES["FileInput"]["size"] > 31457280) {	echo 0;	}
	switch(strtolower($_FILES['FileInput']['type'])) {
		case 'text/xml':
			break;
		default:
			echo 0;
	}
	$File_Name          = strtolower($_FILES['FileInput']['name']);
	$File_Ext           = substr($File_Name, strrpos($File_Name, '.'));
	$Random_Number      = rand(0, 9999999999);
	$NewFileName 		= $Random_Number.$File_Ext;
	if(move_uploaded_file($_FILES['FileInput']['tmp_name'], $UploadDirectory.$NewFileName )) {
		echo $UploadDirectory.$NewFileName;
	} else {
		echo 0;
	}
}
?>
