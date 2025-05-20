const site_pathname = $('input[name="site_pathname"]').val();
const cmsroot = '/'+site_pathname+'_cms';

$(function(){
	autosize($('textarea'));
	
	//validationEngine
	$('#searchForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});

	//validationEngine
	$('#editForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});

	/* datetimepicker */
	$.datetimepicker.setLocale('ja');
	activate_datetimepicker();
});

$('#prefList').on('change',function(){
	var pref_id = $(this).val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_city', pref_id: pref_id };
	CallAjax( url, "get", data, 'html', 'get_data');
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

$('#search').on('click',function(e){
	e.preventDefault();
	if ($('#searchForm').validationEngine("validate")) {
		var pref_name = $('#prefList option:selected').text();
		var city_name = $('#cityList option:selected').text();
		var attribute_id = $('input[name="attribute_flgname"]:checked').attr('id');
		var attribute_name = $('label[for="' + attribute_id + '"]').text();
		var form = $('#searchForm');
		form = append_data(form, 'pref_name', pref_name);
		form = append_data(form, 'city_name', city_name);
		form = append_data(form, 'attribute_name', attribute_name);
		form = append_data(form, 'mode', 'create');
		form.submit();
	}
	return false;
});

$('#publishAt').on('change', function(e){
	var publish_at = $(this).val();
	var updated_at = $('#updatedAt').val();
	if (updated_at === '' || updated_at < publish_at) {
		$('#updatedAt').val(publish_at);
	}
});

$('#preview').on('click',function(e){
	e.preventDefault();
	form_send('preview');
	
});

$('#update').on('click',function(e){
	e.preventDefault();
	if(!confirm('本番にデータを反映しますか？')){
        return false;
    }
	form_send('update');
});


$('#publish').on('click',function(e){
	e.preventDefault();
	if(!confirm('本当に公開しますか？')){
        return false;
    }
	var recommend_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'publish', element_type:site_pathname+'_recommend', recommend_id: recommend_id };
	CallAjax( url, "get", data, 'html', 'publish');
});

$('#unpublish').on('click',function(e){
	e.preventDefault();
	if(!confirm('本当に非公開にしますか？')){
        return false;
    }
	var recommend_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'unpublish', element_type:site_pathname+'_recommend', recommend_id: recommend_id };
	CallAjax( url, "get", data, 'html', 'unpublish');
});

$('.recommend-item .unpublish').on('click',function(e){
	e.preventDefault();
	if(!confirm('本当に非公開にしますか？')){
        return false;
    }
	var recommend_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'unpublish', element_type:site_pathname+'_recommend', recommend_id: recommend_id };
	CallAjax( url, "get", data, 'html', 'unpublish');
});

$('.date-input-set .material-icons').on('click', function(){
	$(this).prev().focus();
});

$('input[name="is_same_lead"]').on('change', function(){
	if ($(this).prop('checked')) {
		$('input[name="description"]').val('');
		$('input[name="description"]').css('display', 'none');
	} else {
		$('input[name="description"]').css('display', 'block');
	}
});

/* upload image */
$('#selectImage').on('click', function(e){
	e.preventDefault();
	$('input[type=file]').trigger('click');
});

$('input[type="file"]').on('change', function(e){
	var recommend_id = $('input[name="recommend_id"]').val();
	var image_id = $('input[name="image_id"]').val();
	var image_dir = $('input[name="image_id"]').data('dir');
	var host = $('input[name="image_id"]').data('host');

	var form = new FormData();
	form.append('image_file',$(this).prop('files')[0]);
	form.append('mode','upload_file');
	form.append('element_type','recommend_image');
	form.append('recommend_id',recommend_id);
	form.append('image_id',image_id);
	form.append('image_dir',image_dir);
	form.append('host',host);
	
	var url = get_hostname()+'/cms/element/';
	CallAjax( url, "post", form, 'html', 'upload_file');
});

$('.sort-num').on('click',function(){
	var clinic_id = $(this).data('id');
	if (!$('#order' + clinic_id).is(':empty')) {
		$('#order' + clinic_id).empty();
	} else {
		var recommend_id = $(this).data('recommend-id');
		var clinic_id = $(this).data('id');
		var url = get_hostname()+'/parts/';
		var data = { mode: 'get_order_list', recommend_id: recommend_id, site: site_pathname };
		CallAjax( url, "get", data, 'html', 'get_order_list', clinic_id);
	}
});

$(document).on('click', '.clinic-item .delete-clinic', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	var recommend_id = $('input[name="recommend_id"]').val();
	var clinic_id = $(this).data('id');

	var url = get_hostname()+'/parts/';
	var data = { mode: 'delete_clinic', recommend_id: recommend_id, clinic_id: clinic_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'delete_clinic', clinic_id );
});

$(document).on('click', '.clinic-item .edit-clinic', function(e){
	e.preventDefault();
	var clinic_id = $(this).data('id');
	var recommend_id = $('input[name="recommend_id"]').val();
	var url = get_hostname()+cmsroot+'/clinic/'+clinic_id+'?recommend_id='+recommend_id;
	location.href = url;

});

$(document).on('click', '.clinic-item .update-clinic', function(e){
	e.preventDefault();
	if(!confirm('本番にデータを反映しますか？')){
        return false;
    }
	var recommend_id = $('input[name="recommend_id"]').val();
	var clinic_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/clinic/'+clinic_id;
	var this_button = $(this);
	var formData = new FormData();
	formData.append('mode', 'update_preview');
	formData.append('recommend_id', recommend_id);
	formData.append('clinic_id', clinic_id);
	formData.append('site_pathname', site_pathname);
	CallAjax( url, "post", formData, 'html', 'update_clinic_preview', this_button);
});

/* search clinic modal */
$('#searchClinic #searchIcon').on('click', function(e){
	e.preventDefault();
	var condition = $('select[name="condition"]').val();
	var search_text = $('input[name="search_text"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_clinic_list', condition: condition, search_text: search_text, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'get_clinic_list' );
});

$('#searchText').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#searchClinic #searchIcon').trigger('click');
    }
});


$(document).on('click', '.add-clinic', function(e){
	e.preventDefault();
	var recommend_id = $('input[name="recommend_id"]').val();
	var clinic_id = $(this).data('id');
	var clinic_name_str = $('#resultClinic'+clinic_id+' .name-box').text();
	
	var url = get_hostname()+'/parts/';
	var data = { mode: 'add_clinic', recommend_id: recommend_id, clinic_id: clinic_id, clinic_name_str: clinic_name_str, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'add_clinic', clinic_id );
});

/* list */
$('.recommend-item #edit').on('click', function(e){
	e.preventDefault();
	var recommend_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/recommend/'+recommend_id;
	location.href = url;

});

$(document).on('click', '.order-select li', function(e){
	e.preventDefault();
	var recommend_id = $('input[name="recommend_id"]').val();
	var clinic_id = $(this).parent().parent('div').data('id');
	var sort_order = $(this).data('order');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'update_order', element_type:site_pathname+'_recommend_clinic', recommend_id: recommend_id, clinic_id: clinic_id, sort_order: sort_order };
	var param = { sort_order:sort_order, clinic_id:clinic_id};
	CallAjax( url, "get", data, 'html', 'update_order', param);
});

$('#searchIcon').on('click', function(){
	$('#searchForm').submit();
	return false;
});

/* function */
function activate_datetimepicker() {
	$('#publishAt').datetimepicker({
		timepicker:false,
		format: 'Y-m-d',
		scrollMonth: false,
		scrollInput: false
	});
	$('#updatedAt').datetimepicker({
		timepicker:false,
		format: 'Y-m-d',
		scrollMonth: false,
		scrollInput: false
	});
}

function append_data(form, name, value)
{
	$('<input>').attr({'type': 'hidden', 'name': name, 'value': value}).appendTo(form);
	return form;
}

function form_send(mode)
{
	if ($('#editForm').validationEngine("validate")) {
		var recommend_id = $('input[name="recommend_id"]').val();
		var url = get_hostname()+cmsroot+'/recommend/'+recommend_id;
		var form = new FormData($('#editForm').get(0));
		form.append('mode', mode);
		if (!form.has('filename') ) form.append('filename', '');
		CallAjax( url, "post", form, 'html', mode );
	}
}


// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_data = function(){
		$('#cityList').empty();
		$('#cityList').append('<option value="">市区町村を選択</option>');
		$('#cityList').append($.ajaxResponse.responseText);
	}
	this.get_station_list = function(){
		$('.station-row').remove();
		$('.station-table').append($.ajaxResponse.responseText);
	}
	this.get_order_list = function(clinic_id){
		$('#order'+clinic_id).append($.ajaxResponse.responseText);
	}
	this.get_clinic_list = function(){
		$('#clinicList').empty();
		$('#clinicList').append($.ajaxResponse.responseText);
	}
	this.preview = function(){
		const obj = JSON.parse($.ajaxResponse.responseText);
		window.open(obj.preview_url);
	}
	this.update = function(){
		alert('更新しました');
	}
	this.publish = function(refresh_url){
		alert('公開しました');
		location.reload();
	}
	this.unpublish = function(refresh_url){
		alert('非公開にしました');
		location.reload();
	}
	this.update_order = function(param){
		$('#clinic' + param.clinic_id + ' .sort-box .sort-num').text(param.sort_order);
		$('#order' + param.clinic_id).empty();
		if ( !($('#clinic' + param.clinic_id + ' .btn-box .update-clinic').length) ) {
			$('#clinic' + param.clinic_id + ' .btn-box').prepend('<button class="btn red-color update-clinic" data-id="'+param.clinic_id+'">更新</button>');
		}
	}
	this.update_clinic_preview = function(button){
		alert('更新しました');
		//alert($.ajaxResponse.responseText);
		button.remove();
	}
	this.add_clinic = function(clinic_id){
		try {
			JSON.parse($.ajaxResponse.responseText);
			alert('すでに追加されています');
		} catch (error) {
			$('.clinic-list-box ul').append($.ajaxResponse.responseText);
			$('#resultClinic'+clinic_id+' .btn-box .add-clinic').prop('disabled',true);
			alert('追加しました');
		}
	}
	this.delete_clinic = function(clinic_id){
		$('.clinic-list-box ul li#clinic'+clinic_id).remove();
		alert('削除しました');
	}
	this.upload_file = function(){
		$('#mainVisual').empty();
		$('#mainVisual').append($.ajaxResponse.responseText);
	}
};
var Callback = new CallbackClass();
