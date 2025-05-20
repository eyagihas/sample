$(function(){
	autosize($('textarea'));
	set_disabled();

	//validationEngine
	$('#invisalignForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});
});

//ブラウザの戻るボタンで画面遷移した場合
$(function(){
    if (window.performance.navigation.type == 2) {
		if ($('input[name=clinic_id]').length) {
			var mode = $('form').data('mode');
			var form = get_form(mode);
			form.submit();
		}
    }
});


$('input[name="confirm_check"]').on('change', function(){
	if ($(this).prop('checked') == true) {
		$('#checkTel').prop('disabled', false);
	} else {
		$('#checkTel').prop('disabled', true);
	}
});

$('#checkTel').on('click', function(e){
	e.preventDefault();
	if ($("#invisalignForm").validationEngine("validate")) {
		var form = new FormData($('#invisalignForm').get(0));
		form.append('mode', 'check_tel');
		//var url = get_hostname()+'/kyousei_form/invisalign/';
		var url = get_hostname()+location.pathname;
		CallAjax( url, "post", form, 'html', 'check_tel');

		$(this).attr('disabled', 'disabled');
	}
});

$(document).on('click', '.time-table .add', function(e){
	e.preventDefault();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_operation_time_row' };
	CallAjax( url, "get", data, 'html', 'get_operation_time_row');
});

$(document).on('click', '.time-table .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('td').parent('tr').remove();
});

$(document).on('click', '.fee-table .add', function(e){
	e.preventDefault();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_fee_row' };
	CallAjax( url, "get", data, 'html', 'get_fee_row');
});

$(document).on('click', '.fee-table .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('td').parent('tr').remove();
});

$(document).on('click', '.reserve_visible', function(){
	if ($(this).prop('checked')) {
		$('.reserve_visible').prop('checked', false);
		$(this).prop('checked', true);

		var name = $(this).attr("id");
		if (name === 'reserve_url') {
			$('textarea[name="reserve_url"]').addClass('validate[required]');
			$('input[name="reserve_tel"]').removeClass('validate[required]');
		} else {
			$('textarea[name="reserve_url"]').removeClass('validate[required]');
			$('input[name="reserve_tel"]').addClass('validate[required]');		
		}
	}
});

$(document).on('change', '#featureTypeList', function(){
	var form = new FormData();

	form.append('mode','get_feature_form');
	form.append('clinic_id',$('input[name="clinic_id"]').val());
	form.append('feature_id',$('input[name="feature_id"]').val());
	form.append('feature_id_circle',$('input[name="feature_id_circle"]').val());
	form.append('feature_type_id',$('select[name="feature_type_id"]').val());

	//var url = get_hostname()+'/kyousei_form/invisalign/';
	var url = get_hostname()+location.pathname;
	CallAjax( url, "post", form, 'html', 'get_feature_form');
});

// 「戻る」「次へ」ボタン挙動
$(document).on('click', '.basic-info #close', function(e){
	e.preventDefault();
	$('#basicInfo').empty();
});

$(document).on('click', '.basic-info #next', function(e){
	e.preventDefault();
	if ($("#invisalignForm").validationEngine("validate")) {
		var form = $('#invisalignForm');
		form = append_data(form, 'mode', 'show_attribute_form');

		if ($('input[name=is_pr_reserve_url_visible]').prop('checked') == false) {
			form = append_data(form, 'is_pr_reserve_url_visible', 0);
		}
		if ($('input[name=is_pr_reserve_tel_visible]').prop('checked') == false) {
			form = append_data(form, 'is_pr_reserve_tel_visible', 0);
		}
		form.submit();
	}
});

$(document).on('click', '.attribute-form #prev', function(e){
	e.preventDefault();
	var form = get_form('show_form');
	form.submit();
});

$(document).on('click', '.attribute-form #next', function(e){
	e.preventDefault();
	if ($("#invisalignForm").validationEngine("validate")) {
		var form = $('#invisalignForm');
		form = append_data(form, 'mode', 'show_guideline_form');
		form.submit();
	}
});

$(document).on('click', '.guideline-form #prev', function(e){
	e.preventDefault();
	var form = get_form('show_attribute_form');
	form = append_data(form, 'is_prev', true);
	form.submit();
});

$(document).on('click', '.guideline-form #next', function(e){
	e.preventDefault();
	var type = $('input[name="type"]').val();

	if (type !== 'cms') {
		var image_check = false;
		if ($('#exterior img').length > 0) image_check = true;
		if ($('#interior img').length > 0) image_check = true;
		if (!image_check) {
			set_image_error('外観写真か内観写真どちらかを必ず登録してください');
			return false;
		}
	}

	//setTimeout(function() {
		if ($("#invisalignForm").validationEngine("validate")) {
			var form = $('#invisalignForm');
			form = append_data(form, 'mode', 'show_feature_form');
			form = append_data(form, 'next_feature_id', 1);
			form = append_data(form, 'next_feature_id_circle', '①');
			form.submit();
		}
	//}, 3000); 
});

$(document).on('click', '.feature-form #prev', function(e){
	e.preventDefault();
	var feature_id = $('input[name="feature_id"]').val();
	if (feature_id == 1) {
		var form = get_form('show_guideline_form');
	} else {
		var form = get_form('show_feature_form');
		form = append_data(form, 'next_feature_id', Number(feature_id)-1);
		form = append_data(form, 'next_feature_id_circle', get_feature_id_circle(Number(feature_id)-1));
	}
	
	form = append_data(form, 'is_prev', true);
	form.submit();
});

$(document).on('click', '.feature-form #next', function(e){
	e.preventDefault();

	var feature_type_id = $('#featureTypeList').val();
	var type = $('input[name="type"]').val();

	if (type !== 'cms') {
		var image_check = false;
		if (feature_type_id == 1 && $('.image-box img').length > 0) {
			image_check = true;
		} else if (feature_type_id == 2 && $('.image-box img').length > 1) {
			image_check = true;
		}
		if (!image_check) {
			set_image_error('画像を登録してください');
			return false;
		}			
	}

	//setTimeout(function() {
		if ($("#invisalignForm").validationEngine("validate")) {
			var feature_id = $('input[name="feature_id"]').val();
			var next_feature_id = Number(feature_id)+1;
			var form = $('#invisalignForm');
			form = append_data(form, 'mode', 'show_feature_form');
			form = append_data(form, 'next_feature_id', next_feature_id);
			form = append_data(form, 'next_feature_id_circle', get_feature_id_circle(next_feature_id));
			form.submit();
		}
	//}, 3000);
});

$(document).on('click', '.feature-form #register', function(e){
	e.preventDefault();

	var feature_type_id = $('#featureTypeList').val();
	var type = $('input[name="type"]').val();

	if (type !== 'cms') {
		var image_check = false;
		if (feature_type_id == 1 && $('.image-box img').length > 0) {
			image_check = true;
		} else if (feature_type_id == 2 && $('.image-box img').length > 1) {
			image_check = true;
		}
		if (!image_check) {
			set_image_error('画像を登録してください');
			return false;
		}		
	}

	//setTimeout(function() {
		if ($("#invisalignForm").validationEngine("validate")) {
			var form = $('#invisalignForm');
			form = append_data(form, 'mode', 'show_thanks');
			form.submit();
		}
	//}, 3000);
});

// upload file
$(document).on('click', '.select-image', function(e){
	e.preventDefault();
	$(this).prev('label').children('input[name=image_file]').trigger('click');
});

$(document).on('change', 'input[type="file"]', function(e){
	var clinic_id = $('input[name="clinic_id"]').val();
	var image_dir = '/image/'+clinic_id;
	var image_type = $(this).data('type');

	if ($(this).prop('files')[0]) {
		var form = new FormData();
		form.append('image_file',$(this).prop('files')[0]);
		form.append('mode','upload_file');
		form.append('element_type',image_type);
		form.append('clinic_id',clinic_id);
		form.append('image_dir',image_dir);
		//var url = get_hostname()+'/kyousei_form/invisalign/';
		var url = get_hostname()+location.pathname;
		CallAjax( url, "post", form, 'html', 'upload_file', image_type);
	}
});

function set_disabled()
{
	var type = $('input[name="type"]').val();
	if (type === 'cms') {
		$('#invisalignForm').find('input').prop('disabled', true);
		$('#invisalignForm').find('textarea').prop('disabled', true);
		$('#invisalignForm').find('select').prop('disabled', true);
		$('input[name="clinic_id"]').prop('disabled', false);
	}
}

function append_data(form, name, value)
{
	$('<input>').attr({'type': 'hidden', 'name': name, 'value': value}).appendTo(form);
	return form;
}

function get_form(mode)
{
	$('#invisalignForm').validationEngine('detach');
	var form = $('#invisalignForm');
	form = append_data(form, 'mode', mode);
	return form;
}

function get_feature_id_circle(feature_id)
{
	switch (feature_id) {
		case 1:
			return '①';
			break;
		case 2:
			return '②';
			break;
		case 3:
			return '③';
			break;
	}
}

function get_image_error_mark(message)
{
	var elem = 
	'<div class="formError imageError" style="position:inherit;">' +
	'<div class="formErrorArrow formErrorArrowBottom">' +
	'<div class="line1"></div>' +
	'<div class="line2"></div>' +
	'<div class="line3"></div>' + 
	'<div class="line4"></div>' +
	'<div class="line5"></div>' +
	'<div class="line6"></div>' +
	'<div class="line7"></div>' +
	'<div class="line8"></div>' +
	'<div class="line9"></div>' +
	'<div class="line10"></div>' +
	'</div>' +
	'<div class="formErrorContent">* ' + message + '</div>' +
	'</div>';

	return elem;
}

function set_image_error(message)
{
	$('.imageError').remove();
	var elem = get_image_error_mark(message);
	$('.image-box .row').append(elem);
	window.scroll({top: 100, behavior: "smooth"});
}


// Ajax処理コールバック処理
var CallbackClass = function() {
	this.check_tel = function(){
		$('#basicInfo').empty();
		$('#telInfo .not-exists').remove();

		try {
			JSON.parse($.ajaxResponse.responseText);
			$('#telInfo').append('<p class="not-exists">登録されていない電話番号です。</p>');
		} catch (error) {
			
			$('#basicInfo').append($.ajaxResponse.responseText);
		}
	}
	this.get_operation_time_row = function(){
		$('#operation_times table tbody').append($.ajaxResponse.responseText);
	}
	this.get_fee_row = function(){
		$('.fee-table table tbody').append($.ajaxResponse.responseText);
	}
	this.upload_file = function(image_type){
		$('#'+image_type).empty();
		$('#'+image_type).append($.ajaxResponse.responseText);

		$('input[data-type="'+image_type+'"]').parent('label').parent('div.row').find('div.imageError').remove();
	}
	this.get_feature_form = function(){
		$('#featureForm').empty();
		$('#featureForm').append($.ajaxResponse.responseText);
		autosize($('textarea'));
	}
};
var Callback = new CallbackClass();