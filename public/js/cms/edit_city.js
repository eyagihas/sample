const site_pathname = $('input[name="site_pathname"]').val();
const cmsroot = '/'+site_pathname+'_cms';

$(function(){
	//validationEngine
	$('#editCity').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});
	$('#addForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});
});

$('#searchIcon').on('click', function(){
	$('#searchForm').submit();
	return false;
});

$('.edit-city').on('click', function(){
	var city_id = $(this).data('id');
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_city_info', city_id: city_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'get_city_info');
});

$('#add').on('click', function(e){
	e.preventDefault();
	if ($("#addForm").validationEngine("validate")) {
		var form = new FormData($('#addForm').get(0));
		form.append('mode', 'add');
		var url = get_hostname()+cmsroot+'/city/0';
		CallAjax( url, "post", form, 'html', 'add');
	}
});

$(document).on('click', '#updateCity', function(){
	if ($("#editCity").validationEngine("validate")) {
		var city_id = $('input[name="city_id"]').val();
		var url = get_hostname()+cmsroot+'/city/' + city_id;
		var form = new FormData($('#editCity').get(0));
		form.append('mode', 'update');
		console.log(url);

		CallAjax( url, "post", form, 'html', 'update' );
	}
});

/* upload image */
$(document).on('click', '#selectImage', function(e){
	e.preventDefault();
	$('input[type=file]').trigger('click');
});

$(document).on('change', 'input[type="file"]', function(e){
	var city_id = $('input[name="city_id"]').val();
	var image_dir = $('input[name="image_file"]').data('dir');
	var host = $('input[name="image_file"]').data('host');

	var form = new FormData();
	form.append('image_file',$(this).prop('files')[0]);
	form.append('mode','upload_file');
	form.append('element_type','city_image');
	form.append('city_id',city_id);
	form.append('image_dir',image_dir);
	form.append('host',host);
	var url = get_hostname()+'/cms/element/';
	CallAjax( url, "post", form, 'html', 'upload_file');
});


// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_city_info = function(){
		$('#editModal').empty();
		$('#editModal').append($.ajaxResponse.responseText);
	}
	this.add = function(){
		alert('追加しました');
		location.href = get_hostname()+cmsroot+'/city_list/';
	}
	this.update = function(){
		alert('更新しました');
	}
	this.upload_file = function(){
		$('#mainVisual').empty();
		$('#mainVisual').append($.ajaxResponse.responseText);
	}
};
var Callback = new CallbackClass();
