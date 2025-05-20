$(function(){
	//validationEngine
	$('#tabForm').validationEngine({
		promptPosition: "bottomLeft",
		ajaxFormValidation: true,
		scroll: false,
		binded: true
	});
});

$('#prefList').on('change',function(){
	var pref_id = $(this).val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_city', pref_id: pref_id };
	CallAjax( url, "get", data, 'html', 'get_data');
});

$('#searchStation #searchIcon').on('click', function(e){
	e.preventDefault();
	var search_text = $('#searchStation input[name="search_text"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_station_list', search_text: search_text };
	CallAjax( url, "get", data, 'html', 'get_station_list' );
});

$('#searchStation #searchText').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#searchStation #searchIcon').trigger('click');
    }
});

$('#cityList').on('change',function(){
	if ($(this).val() !== '') {
		$('input[name="station_group_id"]').removeClass('validate[required]');
	} else {
		$('input[name="station_group_id"]').addClass('validate[required]');
	}
});

$(document).on('click', '.select-station', function(e){
	e.preventDefault();
	var station_id = $(this).data('id');
	var station_name = $(this).data('name');
	$('input[name="station_group_id"]').val(station_id);
	$('input[name="station_name"]').val(station_name);
	$('#cityList').removeClass('validate[required]');
	$('.cityListformError').css('display', 'none');
	$('.close-modal').trigger('click');
});

$('#search').on('click',function(e){
	e.preventDefault();
	if ($('#searchForm').validationEngine("validate")) {
		var pref_id = $('#prefList').val();
		var city_id = $('#cityList').val();
		var station_group_id = $('input[name="station_group_id"]').val();
		var station_name = $('input[name="station_name"]').val();
		var selected_flg = $('input[name="selected_flg"]:checked').val();
		var num = $('input[name="num"]:checked').val();
		var pathname = location.pathname;
		var url = pathname + '?pref_id=' + pref_id + '&city_id=' + city_id +  '&station_group_id=' + station_group_id + '&station_name=' + station_name + '&selected_flg=' + selected_flg + '&num=' + num;
		window.open(url);
	}
});

// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_data = function(){
		$('#cityList').empty();
		$('#cityList').append('<option value="">市区町村を選択</option>');
		$('#cityList').append($.ajaxResponse.responseText);
	}
	this.get_data_2 = function(){
		var win = window.open('');
		win.focus();
		win.document.write($.ajaxResponse.responseText);
		win.document.close();
	}
	this.get_station_list = function(){
		$('.station-row').remove();
		$('.station-table').append($.ajaxResponse.responseText);
	}
};
var Callback = new CallbackClass();
