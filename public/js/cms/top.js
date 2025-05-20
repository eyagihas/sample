$('#clinicSearchIcon').on('click', function(){
	var site_pathname = $(this).prev('input').data('site');
	var search_text = $(this).prev('input').val();
	var url = '/'+site_pathname+'_cms/clinic_list/?search_text='+search_text;
	location.href = url;
});

$('#citySearchIcon').on('click', function(){
	var site_pathname = $(this).prev('input').data('site');
	var search_text = $(this).prev('input').val();
	var url = '/'+site_pathname+'_cms/city_list/?search_text='+search_text;
	location.href = url;
});

$('#stationSearchIcon').on('click', function(){
	var site_pathname = $(this).prev('input').data('site');
	var search_text = $(this).prev('input').val();
	var url = '/'+site_pathname+'_cms/station_list/?search_text='+search_text;
	location.href = url;
});

$('#clinicSearch').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#clinicSearchIcon').trigger('click');
    }
});

$('#citySearch').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#citySearchIcon').trigger('click');
    }
});

$('#stationSearch').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#stationSearchIcon').trigger('click');
    }
});

$('#tagSearchIcon').on('click', function(){
	var site_pathname = $(this).prev('input').data('site');
	var search_text = $(this).prev('input').val();
	var url = '/'+site_pathname+'_cms/tag_list/?site_pathname='+site_pathname+'&search_text='+search_text;
	location.href = url;
});

$('#tagSearch').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#tagSearchIcon').trigger('click');
    }
});