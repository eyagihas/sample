const site_pathname = $('input[name="site_pathname"]').val();
const cmsroot = '/'+site_pathname+'_cms';

$(function(){
	//validationEngine
	$('#searchForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});

	if ($('input[name=section]').val() === 'recommend') {
		get_top_link_list('recommend');
	} else {
		get_top_link_list('city');
		get_top_link_list('station');		
	}
});

$('#prefList').on('change',function(){
	var pref_id = $(this).val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_city', pref_id: pref_id };
	CallAjax( url, "get", data, 'html', 'get_city');
});

$('#cityList').on('change',function(){
	if ($(this).val() !== '') {
		$('input[name="station_group_id"]').removeClass('validate[required]');
	} else {
		$('input[name="station_group_id"]').addClass('validate[required]');
	}
});

$('#searchStation #searchIcon').on('click', function(e){
	e.preventDefault();
	var search_text = $('#searchStation input[name="search_text"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_station_list', search_text: search_text, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'get_station_list' );
});

$('#searchStation #searchText').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#searchStation #searchIcon').trigger('click');
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

$('#search').on('click', function(e){
	e.preventDefault();
	if ($('#searchForm').validationEngine("validate")) {
		var section = $('input[name=section]').val();
		var pref_id = $('#prefList').val();
		var city_id = $('#cityList').val();
		var station_group_id = $('input[name="station_group_id"]').val();
		var url = get_hostname()+'/parts/';

		if (section === 'city_station') {
			var data = { mode: 'get_city_station', pref_id: pref_id, city_id: city_id, station_group_id: station_group_id, site_pathname: site_pathname };
			CallAjax( url, "get", data, 'html', 'get_city_station' );
		} else {
			var attribute_id = $('input[name="attribute_flgname"]:checked').val();
			var data = { mode: 'get_recommend', pref_id: pref_id, city_id: city_id, station_group_id: station_group_id, attribute_id: attribute_id, site_pathname: site_pathname };
			CallAjax( url, "get", data, 'html', 'get_recommend' );
		}
	}

});

$(document).on('click', '#searchResult .add-link', function(e){
	e.preventDefault();
	var type = $(this).data('type');
	var id = $(this).data('id');
	var element_type = (type === 'recommend') ? 'top_recommend' : 'top_city_station';
	var colname = (type === 'station') ? 'station_group_id' : type+'_id';
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'add_link', element_type: element_type, colname: colname, id: id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'add_link', type );
});

$(document).on('click', '#searchResult .remove-link', function(e){
	e.preventDefault();
	var type = $(this).data('type');
	var id = $(this).data('id');
	var element_type = (type === 'recommend') ? 'top_recommend' : 'top_city_station';
	var colname = (type === 'station') ? 'station_group_id' : type+'_id';
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'remove_link', element_type: element_type, colname: colname, id: id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'remove_link', type );
});

$(document).on('click', '.list-box .remove-link', function(e){
	e.preventDefault();
	var type = $(this).data('type');
	var id = $(this).data('id');
	var element_type = (type === 'recommend') ? 'top_recommend' : 'top_city_station';
	var colname = (type === 'station') ? 'station_group_id' : type+'_id';
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'remove_link', element_type: element_type, colname: colname, id: id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'remove_link_from_list', type );
});

$(document).on('click', '.internal-links .pagination__a', function(e){
	e.preventDefault();
	var type = $(this).parents('.list-box').attr('id');
	var page = $(this).data('page');
	get_top_link_list(type, page);
});

function get_top_link_list(type='city', page=1)
{
	var section = $('input[name=section]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_top_'+section+'_cmslist', site_pathname: site_pathname, type: type, page: page };
	CallAjax( url, "get", data, 'html', 'get_top_link_list', type );
}

// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_city = function(){
		$('#cityList').empty();
		$('#cityList').append('<option value="">市区町村を選択</option>');
		$('#cityList').append($.ajaxResponse.responseText);
	}
	this.get_station_list = function(){
		$('.station-row').remove();
		$('.station-table').append($.ajaxResponse.responseText);
	}
	this.get_city_station = function(){
		$('#searchResult .h2-box').css('display', 'flex');
		$('#searchResult .cms-list-style').empty();
		$('#searchResult .cms-list-style').append($.ajaxResponse.responseText);
		$('#prefList').val('');
		$('#cityList').val('');
		$('input[name="station_group_id"]').val('');
	}
	this.get_recommend = function(){
		$('#searchResult .h2-box').css('display', 'flex');
		$('#searchResult .cms-list-style').empty();
		$('#searchResult .cms-list-style').append($.ajaxResponse.responseText);
		$('#prefList').val('');
		$('#cityList').val('');
		$('input[name="station_group_id"]').val('');
	}
	this.add_link = function(type){
		$('#searchResult button').text('非掲載にする');
		$('#searchResult button').removeClass('add-link');
		$('#searchResult button').addClass('remove-link');
		alert('掲載しました');
		get_top_link_list(type);
	}
	this.remove_link = function(type){
		$('#searchResult button').text('掲載する');
		$('#searchResult button').removeClass('remove-link');
		$('#searchResult button').addClass('add-link');
		alert('非掲載にしました');
		get_top_link_list(type);
	}
	this.remove_link_from_list = function(type){
		alert('非掲載にしました');
		get_top_link_list(type);
	}
	this.get_top_link_list = function(type){
		$('#'+type+'.list-box').empty();
		$('#'+type+'.list-box').append($.ajaxResponse.responseText);
	}
};
var Callback = new CallbackClass();
