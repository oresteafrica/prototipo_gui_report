$( document ).ready(function() {
//----------------------------------------------------------------------------------------------------------------------
var path = window.location.href.substring(0,window.location.href.lastIndexOf('/')+1) ;

var options = { 
	target:   '#output',
	beforeSubmit:  beforeSubmit, 
	success:       afterSuccess, 
	uploadProgress: OnProgress, 
	resetForm: true 
}; 
		
$('#MyUploadForm').submit(function() { $(this).ajaxSubmit(options); return false; });

//----------------------------------------------------------------------------------------------------------------------
$('#bu_generate_csv').click(function() {
	var f = path + $('#output').text();
	var p = $('#dimension_years option:selected').text()+$('#dimension_months').val();
	var d = $('#dataset_list').val();
	var u = 'csv_download.php?f='+f+'&p='+p+'&d='+d ;
	$('.all_dimensions').hide();
	$('#dataset_list').empty();
	window.location.href = u ;
});
//----------------------------------------------------------------------------------------------------------------------
function afterSuccess() {
	$('#submit-btn').show();
	$('progress').delay(500).fadeOut();
	if ($('#output').text()==0) { alert('Something got wrong with the upload. Try again later.'); return false; }
	get_datasets_names();
}
//----------------------------------------------------------------------------------------------------------------------
function beforeSubmit(){
	if (window.File && window.FileReader && window.FileList && window.Blob) {
		if( !$('#FileInput').val()) { alert('Choose file first'); return false; }
		var maxbytes = 31457280;
		var fsize = $('#FileInput')[0].files[0].size;
		var ftype = $('#FileInput')[0].files[0].type;
		var this_year = new Date().getFullYear();
		var option_years = '';
		for ( temp_year = (this_year-5); temp_year < (this_year+6); temp_year++ ) { option_years += '<option>'+temp_year+'</option>'; }
		$('#dimension_years').append(option_years);
		switch(ftype) {
			case 'text/xml':
               break;
			default:
				alert('Unsupported file type');
			return false
		}
		if(fsize > maxbytes) {
			alert('Size:'+bytesToSize(fsize) +' File is too big, it should be less than'+bytesToSize(maxbytes));
			return false
		}		
		$('#submit-btn').hide();
	} else {
		alert('Browser not compatible');
		return false;
	}
}
//----------------------------------------------------------------------------------------------------------------------
function OnProgress(event, position, total, percentComplete) {
	$('progress').show();
	$('progress').val(percentComplete);
}
//----------------------------------------------------------------------------------------------------------------------
function bytesToSize(bytes) {
	var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
	if (bytes == 0) return '0 Bytes';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}
//----------------------------------------------------------------------------------------------------------------------
function get_datasets_names() {
	$.ajax({
		url: 'dhis2_get_n_datasets.php?f='+$('#output').text(),
		type: 'GET',
		dataType: 'html',
		beforeSend: function(a){},
		success: function(a){
			$('#dataset_list').append(a);
			$('.all_dimensions').show();
		},
		error: function(a,b,c){ alert( 'get_datasets_names('+url+')\na = ' + a.responseText + '\nb = ' + b + '\nc = ' + c ) },
		complete: function(a,b){}
	});
}
//----------------------------------------------------------------------------------------------------------------------
});
