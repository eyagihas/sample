const site_pathname = $('input[name="site_pathname"]').val();
const cmsroot = '/'+site_pathname+'_cms';

$(function(){
	autosize($('textarea'));
	set_upload_disabled();

	//validationEngine
	$('#infoForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});

});

$(document).on('click', '.profile-item #edit', function(e){
	e.preventDefault();
	var profile_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/profile/'+profile_id;
	location.href = url;
});

$(document).on('keyup', 'input[name="doctor_en_name"]', function(e){
	var str = $.trim($(this).val());
	$(this).val(str);
});

$(document).on('click', '.career .add', function(e){
	e.preventDefault();
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'add_only', element_type:'profile_career', site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'add_profile_career' );
});

$(document).on('click', '.career-row .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('.career-row').remove();
});

$(document).on('click', '.qualification .add', function(e){
	e.preventDefault();
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'add_only', element_type:'profile_qualification', site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'add_profile_qualification' );
});

$(document).on('click', '.qualification-row .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('.qualification-row').remove();
});

$(document).on('click', '.clinic-add', function(e){
	e.preventDefault();
	var url = get_hostname()+cmsroot+'/element/';
	var order = ($('.clinic-item').length > 0 ) ? $('.clinic-item:last').data('order') : 0;
	var data = { mode: 'add_only', element_type:'profile_clinic', site_pathname: site_pathname, order: order+1 };
	CallAjax( url, "get", data, 'html', 'add_profile_clinic' );
});

$(document).on('click', '.code-search', function(e){
	var order = $(this).data('order');
	$('#searchModal input[name="clinic_order"]').val(order);
});

$(document).on('click', '.clinic-item .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parents('.clinic-item').remove();
});

$('#preview').on('click',function(e){
	e.preventDefault();
	form_send('preview');
	
});

$('#add').on('click',function(e){
	e.preventDefault();
	if(!confirm('データを新規作成しますか？')){
        return false;
    }
	form_send('add');
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
	var profile_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'publish', element_type:'profile', profile_id: profile_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'publish');
});

$('#unpublish').on('click',function(e){
	e.preventDefault();
	if(!confirm('本当に非公開にしますか？')){
        return false;
    }
	var profile_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'unpublish', element_type:'profile', profile_id: profile_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'unpublish');
});

$('.profile-item .unpublish').on('click',function(e){
	e.preventDefault();
	if(!confirm('本当に非公開にしますか？')){
        return false;
    }
	var profile_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'unpublish', element_type:'profile', profile_id: profile_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'unpublish');
});


/* image */
$(document).on('click', '.image-box-row .delete-image', function(e){
	if(!confirm('本当に削除しますか？')){
        return false;
    }
    var image_type = $(this).data('type');
    var image_dir = '/image/profile';
    var image_id = $(this).parent('li').data('id');

    var form = new FormData();
	form.append('mode','delete_profile_file');
	form.append('element_type', image_type);
	form.append('profile_id', $('input[name=profile_id]').val());
	form.append('image_dir', image_dir);
	form.append('image_id', image_id);
	form.append('doctor_en_name', $('input[name=doctor_en_name]').val());
	var url = get_hostname()+'/cms/element/';

	CallAjax( url, "post", form, 'html', 'delete_profile_file', image_id);
});

$(document).on('click', '.image-box-row.profile li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('profile', image_id);
});

$(document).on('click', '.select-image', function(e){
	e.preventDefault();
	$(this).prev('label').children('input[name=image_file]').trigger('click');
});

$(document).on('click', 'input[type="file"]', function(e){
	var image_type = $(this).data('type');
	var doctor_en_name = $('input[name="doctor_en_name"]').val();
	if (doctor_en_name === '') {
		alert('画像をアップロードするには「院長名英字」を入力してください。');
		return false;
	}
	/*
	if (image_type === 'profile_banner') {
		var clinic_id = $('input[name="clinic_id"]').val();
		if (clinic_id === '' ||  clinic_id === '0') {
			alert('画像をアップロードするには「医院ID」を入力してください。');
			return false;
		}
	}
	*/
});

$(document).on('change', 'input[type="file"]', function(e){
	var profile_id = $('input[name="profile_id"]').val();
	var image_type = $(this).data('type');
	var doctor_en_name = $('input[name="doctor_en_name"]').val();
	//var clinic_id = $('input[name="clinic_id"]').val();
	//var image_dir = (image_type==='profile_image') ? '/image/profile' : '/image/'+clinic_id;
	var image_dir = '/image/profile';

	var form = new FormData();
	form.append('image_file',$(this).prop('files')[0]);
	form.append('mode','upload_profile_file');
	form.append('element_type',image_type);
	form.append('profile_id',profile_id);
	form.append('image_dir',image_dir);
	form.append('doctor_en_name',doctor_en_name);
	form.append('site_id',$('input[name="site_id"]').val());
	form.append('host',$('input[name="plus_url"]').val());

	var url = get_hostname()+'/cms/element/';
	var param = { image_type: image_type, image_file: $(this).prop('files')[0] };
	CallAjax( url, "post", form, 'html', 'upload_profile_file', param);
});

/* search clinic modal */
$('#searchClinic #searchIcon').on('click', function(e){
	e.preventDefault();
	//var condition = $('select[name="condition"]').val();
	var search_text = $('input[name="search_text"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_clinic_list', search_text: search_text, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'get_clinic_list' );
});

$('#searchText').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#searchClinic #searchIcon').trigger('click');
    }
});


$(document).on('click', '.add-clinic', function(e){
	e.preventDefault();
	var clinic_id = $(this).data('id');
	var order = $('input[name="clinic_order"]').val();
	$('input[name="clinic_id['+order+']"]').val(clinic_id);
	get_recommends_by_clinic(clinic_id, order);
	$('.close-modal').trigger('click');
});

$(document).on('click', '.recommend-item .edit-recommend', function(e){
	e.preventDefault();
	var recommend_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/recommend/'+recommend_id;
	location.href = url;
});

$(document).on('click', '.explanation-item .edit-explanation', function(e){
	e.preventDefault();
	var explanation_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/explanation/'+explanation_id;
	location.href = url;
});

function set_upload_disabled()
{
	var profile_id = $('input[name="profile_id"]').val();
	if (profile_id == 0) {
		$('input[type="file"]').prop('disabled', true);
		$('.select-image').prop('disabled', true);
		$('.profile-image-area').css('display', 'none');
		$('.banner-image-area').css('display', 'none');
	}
}

function change_selected_image(type, image_id)
{
	$('.image-box-row.'+type+ ' li').removeClass('active '+site_pathname+'-active');
	$('.image-box-row.'+type+' li[data-id="'+image_id+'"]').addClass('active '+site_pathname+'-active');
	$('input[name="'+type+'_image_id"]').val(image_id);
}

function form_send(mode)
{
	if ($('#infoForm').validationEngine("validate")) {
		var profile_id = $('input[name="profile_id"]').val();
		var url = get_hostname()+cmsroot+'/profile/'+profile_id;
		var form = new FormData($('#infoForm').get(0));
		form.append('mode', mode);
		//if (!form.has('filename') ) form.append('filename', '');
		CallAjax( url, "post", form, 'html', mode );
	}
}

function get_recommends_by_clinic(clinic_id, order)
{
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_recommends_by_clinic', site:site_pathname, clinic_id: clinic_id, type:'profile' };
	CallAjax( url, "get", data, 'html', 'get_recommends_by_clinic', order);
}

function upload_profile_file_to_other(plus_url, image_type, image_file, is_break)
{
	var profile_id = $('input[name="profile_id"]').val();
	var doctor_en_name = $('input[name="doctor_en_name"]').val();
	//var clinic_id = $('input[name="clinic_id"]').val();
	//var image_dir = (image_type==='profile_image') ? '/image/profile' : '/image/'+clinic_id;
	var image_dir = '/image/profile';

	var form = new FormData();
	form.append('image_file',image_file);
	form.append('mode','upload_profile_file2');
	form.append('element_type',image_type);
	form.append('profile_id',profile_id);
	form.append('image_dir',image_dir);
	form.append('doctor_en_name',doctor_en_name);
	form.append('host',plus_url);
	var url = get_hostname()+'/cms/element/';
	var param = { image_type: image_type, image_file: image_file, is_break: is_break };
	CallAjax( url, "post", form, 'html', 'upload_profile_file2', param);
};

// Ajax処理コールバック処理
var CallbackClass = function() {
	this.add_profile_career = function(){
		$('.career-list').append($.ajaxResponse.responseText);
	}
	this.add_profile_qualification = function(){
		$('.qualification-list').append($.ajaxResponse.responseText);
	}
	this.add_profile_clinic = function(){
		$('.clinic-list').append($.ajaxResponse.responseText);
	}
	this.upload_profile_file = function(param){
    	var other_url = $('.other-site').eq(0).val();
    	upload_profile_file_to_other(other_url, param.image_type, param.image_file, false);
    	
    	if (param.image_type === 'profile_image') {
			$('.image-box-row.profile').append($.ajaxResponse.responseText);
		} else {
			$('#profileBanner').empty();
			$('#profileBanner').append($.ajaxResponse.responseText);
		}
	}
	this.upload_profile_file2 = function(param){
		if (!param.is_break) {
			var other_url = $('.other-site').eq(1).val();
    		upload_profile_file_to_other(other_url, param.image_type, param.image_file, true);
		}
	}
	this.form_update = function(profile_id){
		const obj = JSON.parse($.ajaxResponse.responseText);
		if (profile_id === '0') {
			var new_url = get_hostname()+cmsroot+'/profile/'+obj.new_profile_id;
			location.href = new_url;
		} else {
			alert('更新しました');
		}
	}
	this.delete_profile_file = function(image_id){
		$('li.profile-image[data-id='+image_id+']').remove();
		alert('削除しました');
	}
	this.get_clinic_list = function(){
		$('#clinicList').empty();
		$('#clinicList').append($.ajaxResponse.responseText);
	}
	this.preview = function(){
		const obj = JSON.parse($.ajaxResponse.responseText);
		window.open(obj.preview_url);
	}
	this.add = function(){
		alert('追加しました');
		const obj = JSON.parse($.ajaxResponse.responseText);
		var new_url = get_hostname()+cmsroot+'/profile/'+obj.new_profile_id;
		location.href = new_url;
	}
	this.update = function(){
		alert('更新しました');
	}
	this.publish = function(){
		alert('公開しました');
		location.reload();
	}
	this.unpublish = function(){
		alert('非公開にしました');
		location.reload();
	}
	this.get_recommends_by_clinic = function(order){
		$('#order'+order+' .recommend-list-box').empty();
		$('#order'+order+' .recommend-list-box').append($.ajaxResponse.responseText);
	}
};
var Callback = new CallbackClass();
