$( document ).ready(function() {
var ur = document.URL.substring(0,document.URL.indexOf('?')) ; // document URL without GET

$('.tree li').each( function() {
	if( $( this ).children( 'ul' ).length > 0 ) {
		$( this ).addClass( 'parent' );     
    	}
});

$('.tree a').click( function( ) {
	var aid = this.id;
	var atx = $(this).text();
	var tmp = $(this).parent().attr('id');
	var lev = tmp.substr(tmp.indexOf('_')+1);
	var ouids = [];
	$(this).parent().children().find('.outreeitem').each( function() {
		ouids.push(this.id);	
	});
	if (ouids.length < 1) { ouids.push(this.id); }
	$('#treeinfo').html('Estrutura escolhida<br /><br /><b><span>'+atx+
		'</span><span style="display:none;">'+aid+'</span><span style="display:none;">'+lev+'</span>' );
	$('#treeinfohide').text(ouids.join(';'));
	return false;
});

$('#bu_report').click(function() {
	var pe = $('#combo_periods_rep').val(); // periodid
	var fo = $('#combo_forms_rep').val(); // datasetid
	var no = $('#treeinfo span').eq(1).text(); // orgunit id
	var ous = $('#treeinfohide').text(); // orgunit all children id
	var le = $('#treeinfo span').eq(2).text(); // hierarchy level (1=max)
	var de = '0';
	if ($('#debug').is(":checked")) { de = 'A'; } else { de = 'B'; }
	$('#report').attr('src', 'index_reports.php?pe='+pe+'&fo='+fo+'&no='+no+'&ous='+ous+'&le='+le+'&de='+de);
});

$('#bu_activity').click(function() {
	if ($('#debug').is(":checked")) { de = 'A'; } else { de = 'B'; }
	$('#report').attr('src', 'index_activity.php');
});

 
});
