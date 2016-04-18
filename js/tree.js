$( document ).ready(function() {
$('#divtree').jstree();
$('#bu_ou_csv_dhis2').click(function() {
	$('#frame_download').attr('src', 'download_ou_csv_dhis2.php?type=csv');
});
$('#bu_ou_xml_dhis2').click(function() {
	$('#frame_download').attr('src', 'download_ou_csv_dhis2.php?type=xml');
});
$('#bu_ou_dxf_dhis2').click(function() {
	$('#frame_download').attr('src', 'download_ou_csv_dhis2.php?type=dxf');
});
});
