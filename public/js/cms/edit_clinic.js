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

	$('#addForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});

	get_now_edited_recommend();
	get_recommends_by_clinic(1);
	if (site_pathname === 'kyousei') get_cases_by_clinic(1);

	/* datetimepicker */
	$.datetimepicker.setLocale('ja');
});

/* add */
$('#add').on('click', function(e){
	e.preventDefault();
	$('.h2-box.clinic-name').css('display', 'none');
	$('.cms-list-style.clinic').empty();
	if ($("#addForm").validationEngine("validate")) {
		var form = new FormData($('#addForm').get(0));
		form.append('mode', 'add');
		var url = get_hostname()+cmsroot+'/clinic/0';
		CallAjax( url, "post", form, 'html', 'add_clinic');
	}
});

/* list */
$('#searchIcon').on('click', function(){
	$('#searchForm').submit();
	return false;
});

$(document).on('click', '.clinic-item #edit', function(e){
	e.preventDefault();
	var clinic_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/clinic/'+clinic_id;
	location.href = url;

});

/* detail */
$('#update').on('click', function(){
	if ($("#infoForm").validationEngine("validate")) {
		var clinic_id = $('#infoForm input[name="clinic_id"]').val();
		var url = get_hostname()+cmsroot+'/clinic/' + clinic_id;
		var form = new FormData($('#infoForm').get(0));
		form.append('account_site_pathname', site_pathname);

		$('input[type="checkbox"]').each(function(index, element) {
			var name = $(element).attr('name');
			if ($(element).prop('checked') == false) {
				form.append(name, 0);
			}
		});
		//for(item of form) console.log(item);

		CallAjax( url, "post", form, 'html', 'form_update' );
	}
});

$(document).on('click', '#updateFeature', function(){
	if ($("#featureForm").validationEngine("validate")) {
		var clinic_id = $('#infoForm input[name="clinic_id"]').val();
		var url = get_hostname()+cmsroot+'/clinic/' + clinic_id;
		var form = new FormData($('#featureForm').get(0));
		form.append('site_pathname', site_pathname);
		form.append('site_id', $('input[name="site_id"]').val());

		CallAjax( url, "post", form, 'html', 'form_update' );
	}
});

$(document).on('click', '#updateType2Feature', function(){
	if ($("#type2FeatureForm").validationEngine("validate")) {
		var clinic_id = $('#infoForm input[name="clinic_id"]').val();
		var url = get_hostname()+cmsroot+'/clinic/' + clinic_id;
		var form = new FormData($('#type2FeatureForm').get(0));
		form.append('site_pathname', site_pathname);
		form.append('site_id', $('input[name="site_id"]').val());

		CallAjax( url, "post", form, 'html', 'form_update' );
	}
});

$(document).on('click', '.sort-num', function(){
	if (!$('.order-select').is(':empty')) {
		$('.order-select').empty();
	} else {
		var recommend_id = $('#featureForm input[name="recommend_id"]').val();
		var url = get_hostname()+'/parts/';
		var data = { mode: 'get_order_list', recommend_id: recommend_id, site: site_pathname };
		CallAjax( url, "get", data, 'html', 'get_order_list' );
	}
});

$(document).on('click', '.order-select li', function(e){
	e.preventDefault();
	var recommend_id = $('input[name="recommend_id"]').val();
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var sort_order = $(this).data('order');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'update_order', element_type:site_pathname+'_recommend_clinic', recommend_id: recommend_id, clinic_id: clinic_id, sort_order: sort_order };
	CallAjax( url, "get", data, 'html', 'update_order', sort_order);
});


$('input[name$="_edited"]').change(function(){
	var name = $(this).attr('name');
	name = name.replace('is_', '').replace('_edited', '');

	if ($(this).prop('checked') == true) {
		$('#basic_'+name).addClass('inactive');
		$('#'+name).removeClass('inactive');
	} else {
		$('#basic_'+name).removeClass('inactive');
		$('#'+name).addClass('inactive');
	}
});

$('input[name$="_visible"]').change(function(){
	if(!confirm('変更しますか？')){
        return false;
    }
});

$('input[class$="_check_group"]').on('click', function(){
	if ($(this).prop('checked')) {
		var class_name = $(this).attr('class');
		$('.'+class_name).prop('checked', false);
		$(this).prop('checked', true);
	}
});

$(document).on('click', '.time-table .add', function(e){
	e.preventDefault();
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'add_only', element_type:'operation_time', site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'add_operation_time' );
});

$(document).on('click', '.time-table .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('td').parent('tr').remove();
});

$(document).on('click', '.recommend-item .edit-other-clinic', function(e){
	e.preventDefault();
	var recommend_id = $(this).data('id');
	var clinic_id = $('.now-edited-box').data('clinic-id');
	var url = get_hostname()+'/parts/';
	var data = { 
			mode: 'get_clinics_by_recommend',
			site:site_pathname,
			recommend_id: recommend_id,
			clinic_id: clinic_id
		};
	CallAjax( url, "get", data, 'html', 'get_clinics_by_recommend' );

});

$(document).on('click', '.clinic-item .edit-clinic', function(e){
	e.preventDefault();
	var clinic_id = $(this).data('id');
	var recommend_id = $(this).data('recommend-id');
	var url = get_hostname()+cmsroot+'/clinic/'+clinic_id+'?recommend_id='+recommend_id;
	location.href = url;

});

$(document).on('click', '.recommend-item .edit-feature', function(e){
	e.preventDefault();
	var recommend_id = $(this).data('id');
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var url = get_hostname()+'/parts/';
	var data = { 
			mode: 'get_recommend_features_by_clinic',
			site:site_pathname,
			recommend_id: recommend_id,
			clinic_id: clinic_id
		};
	CallAjax( url, "get", data, 'html', 'get_recommend_features_by_clinic' );

});

$(document).on('click', '.recommend-item .edit-type2-feature', function(e){
	e.preventDefault();
	var recommend_id = $(this).data('id');
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var url = get_hostname()+'/parts/';
	var data = { 
			mode: 'get_recommend_type2_features_by_clinic',
			site:site_pathname,
			recommend_id: recommend_id,
			clinic_id: clinic_id
		};
	CallAjax( url, "get", data, 'html', 'get_recommend_type2_features_by_clinic' );

});

$(document).on('click', '.recommend-item .edit-recommend', function(e){
	e.preventDefault();
	var recommend_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/recommend/'+recommend_id;
	location.href = url;
});

$(document).on('click', '.recommend-list-box .pagination__a', function(e){
	e.preventDefault();
	var page = $(this).data('page');
	get_recommends_by_clinic(page);
});

$(document).on('change', 'input[name="price_plan"]', function(e){
	var price_plan = $(this).val();
	if (price_plan !== '0') {
		if (!has_case()) $('.pr-image-area').css('display', 'block');
		$('.sort-num-area').css('display', 'flex');
	} else {
		$('.pr-image-area').css('display', 'none');
		$('.sort-num-area').css('display', 'none');
		unset_sort_orer();
		//$('input[name="sort_order"]').val('');
		//$('span.sort-num').text('');
	}
});

$(document).on('click', '.unrelate-profile', function(e){
	e.preventDefault();
	if(!confirm('本当に解除しますか？')){
        return false;
    }
	var profile_id = $(this).data('profile_id');
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var url = get_hostname()+'/parts/';
	var data = { 
			mode: 'unrelate_profile',
			profile_id: profile_id,
			clinic_id: clinic_id
		};
	CallAjax( url, "get", data, 'html', 'unrelate_profile' );

});

/* image */
$(document).on('click', '.image-box-row .delete-image', function(e){
	if(!confirm('本当に削除しますか？')){
        return false;
    }
    var clinic_id = $('input[name=clinic_id]').val();
    var image_id = $(this).parent('li').data('id');
    var image_dir = '/image/'+clinic_id;
    var mode = ($(this).hasClass('case')) ? 'delete_case_file' : 'delete_file';
    var element_type = ($(this).hasClass('case')) ? 'case_image' : 'clinic_image';

    var form = new FormData();
	form.append('mode',mode);
	form.append('element_type',element_type);
	form.append('clinic_id',clinic_id);
	form.append('image_dir',image_dir);
	form.append('image_id',image_id);
	form.append('site_pathname', site_pathname);

	if ($(this).hasClass('case')) {
    	var case_id = $('input[name=case_id]').val();
    	form.append('case_id',case_id);
    }

	var url = get_hostname()+'/cms/element/';
	CallAjax( url, "post", form, 'html', mode, image_id);
});

$(document).on('click', '.image-box-row.mv li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('mv', image_id);
});

$(document).on('click', '.image-box-row.info li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('info', image_id);
});

$(document).on('click', '.image-box-row.feature li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('feature', image_id);
});

$(document).on('click', '.image-box-row.pr li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('pr', image_id);
});

$(document).on('click', '.image-box-row.before li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('before', image_id);
});

$(document).on('click', '.image-box-row.after li img', function(){
	var image_id = $(this).parent('li').data('id');
	change_selected_image('after', image_id);
});

$(document).on('click', '.select-image', function(e){
	e.preventDefault();
	$(this).prev('label').children('input[name=image_file]').trigger('click');
});

$(document).on('change', 'input[type="file"]', function(e){
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var image_dir = '/image/'+clinic_id;
	var case_id = ($('#caseForm input[name="case_id"]').length) ? 
				  $('#caseForm input[name="case_id"]').val() : 0;
	var host = $('input[name="plus_url"]').val();
	var mode = (case_id > 0) ? 'upload_case_file' : 'upload_file';
	var element_type = (case_id > 0) ? 'case_image' : 'clinic_image';
	var case_type = (case_id > 0) ? $(this).attr('class').replace('_file','') : '';

	var form = new FormData();
	form.append('image_file',$(this).prop('files')[0]);
	form.append('mode',mode);
	form.append('element_type',element_type);
	form.append('clinic_id',clinic_id);
	form.append('case_id',case_id);
	form.append('image_dir',image_dir);
	form.append('case_type',case_type);
	form.append('host',host);
	form.append('site_pathname', site_pathname);
	
	var url = get_hostname()+'/cms/element/';
	CallAjax( url, "post", form, 'html', mode, case_type);
});

$(document).on('click', '.fee-table .add', function(e){
	e.preventDefault();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_feature_fee' };
	CallAjax( url, "get", data, 'html', 'get_feature_fee');
});

$(document).on('click', '.fee-table .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('td').parent('tr').remove();
});

$(document).on('click', '.flow-table .add', function(e){
	e.preventDefault();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_feature_flow' };
	CallAjax( url, "get", data, 'html', 'get_feature_flow');
});

$(document).on('click', '.flow-table .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parent('td').parent('tr').remove();
});

$(document).on('change', '.feature-type', function(e){
	var feature_id = $(this).data('id');
	$('#feature'+feature_id).empty();

	var feature_id_circle = $(this).data('id_circle');
	var type = $(this).val();

	if (type !== '') {
		var clinic_id = $('#type2FeatureForm input[name="clinic_id"]').val();
		var recommend_id = $('#type2FeatureForm input[name="recommend_id"]').val();

		var url = get_hostname()+'/parts/';
		var data = { 
				mode: 'get_'+type+'_feature',
				feature_id: feature_id,
				feature_id_circle: feature_id_circle,
				site_pathname: site_pathname,
				clinic_id: clinic_id,
				recommend_id: recommend_id
			};
		CallAjax( url, "get", data, 'html', 'get_'+type+'_feature', feature_id);
	}

	// PR画像項目の表示・非表示設定
	if (has_case()) {
		$('.pr-image-area').css('display', 'none');
		//$('input[name="pr_image_id"]').val('');
	} else {
		if ($('input[name="price_plan"]:checked').val() === '1') {
			$('.pr-image-area').css('display', 'block');
		}
	}
});

$(document).on('click', '.case-list .add-case', function(e){
	var case_id = $(this).data('id');
	var feature_id = $(this).data('feature_id');

	$('#feature'+feature_id+' .case-item').not('#resultCase'+case_id).remove();
	$('input[name="case_id['+feature_id+']"]').val(case_id);
	$('#resultCase'+case_id+' button').text('削除');
	$('#resultCase'+case_id+' button').addClass('remove-case');

});

$(document).on('click', '.case-list .remove-case', function(e){
	var feature_id = $(this).data('feature_id');
	$(this).remove();
	$('input[name="case_id['+feature_id+']"]').val(0);
});

/* modal */
$('#searchCity #searchIcon').on('click', function(e){
	e.preventDefault();
	var condition = $('select[name="condition"]').val();
	var search_text = $('input[name="search_text"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_city_list', condition: condition, search_text: search_text };
	CallAjax( url, "get", data, 'html', 'get_city_list' );
});

$('#searchCity #searchText').on('keyup', function(e){
	if(e.key==='Enter'||e.keyCode===13){
        $('#searchCity #searchIcon').trigger('click');
    }
});

$(document).on('click', '.select-city', function(e){
	e.preventDefault();
	var city_id = $(this).data('id');
	$('input[name="city_id"]').val(city_id);
	$('body').css('overflow', 'auto');
	$('.modal').unwrap();
	$('.modal').css('display', 'none');
});

$(document).on('click', '.station-add', function(e){
	e.preventDefault();
	var url = get_hostname()+cmsroot+'/element/';
	var order = ($('.station-item').length > 0 ) ? $('.station-item:last').data('order') : 0;
	var data = { mode: 'add_only', element_type:'clinic_station', site_pathname: site_pathname, order: order+1 };
	CallAjax( url, "get", data, 'html', 'add_clinic_station' );
});

$(document).on('click', '.code-search', function(e){
	var order = $(this).data('order');
	$('#searchStationModal input[name="station_order"]').val(order);
});

$(document).on('click', '.station-item .remove', function(e){
	e.preventDefault();
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	$(this).parents('.station-item').remove();
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
	var order = $('#searchStationModal input[name="station_order"]').val();
	$('input[name="station_id_list['+order+']"]').val(station_id);
	$('.close-modal').trigger('click');
});

$(document).on('click', '.date-input-set .material-icons', function(){
	$(this).prev().focus();
});


/* edit case */
$(document).on('click', '.case-item .edit-case', function(e){
	e.preventDefault();
	var case_id = $(this).data('id');
	var clinic_id = $(this).data('clinic-id');
	var url = get_hostname()+'/parts/';
	var data = { 
			mode: 'get_case_by_id',
			site:site_pathname,
			case_id: case_id,
			clinic_id: clinic_id
		};
	CallAjax( url, "get", data, 'html', 'get_case_by_id' );

});

$(document).on('click', '.case-item .update-case', function(e){
	e.preventDefault();
	if(!confirm('本番にデータを反映しますか？')){
        return false;
    }
	var case_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/case/'+case_id;
	var this_button = $(this);
	var formData = new FormData();
	formData.append('mode', 'update_preview');
	formData.append('case_id', case_id);
	formData.append('site_pathname', site_pathname);
	CallAjax( url, "post", formData, 'html', 'update_case_preview', this_button);

});

$(document).on('click', '.case-item .preview-case', function(e){
	var clinic_id = $(this).data('clinic-id');
	var attribute_pathname = $(this).data('attribute-pathname');
	var plus_url = $('input[name="plus_url"]').val();
	var url = plus_url+'/clinic/'+clinic_id+'/case/'+attribute_pathname+'/?preview';
	window.open(url);
});

$(document).on('click', '.case-item .publish', function(e){
	e.preventDefault();
	if(!confirm('本当に公開しますか？')){
        return false;
    }
	var case_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'publish', element_type: site_pathname+'_case', case_id: case_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'publish');
});

$(document).on('click', '.case-item .unpublish', function(e){
	e.preventDefault();
	if(!confirm('本当に非公開にしますか？')){
        return false;
    }
	var case_id = $(this).data('id');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'unpublish', element_type: site_pathname+'_case', case_id: case_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'unpublish');
});

$(document).on('click', '.case-sort-num', function(){
	if (!$('.case-order-select').is(':empty')) {
		$('.case-order-select').empty();
	} else {
		var case_id = $('input[name="case_id"]').val();
		var clinic_id = $('input[name="clinic_id"]').val();
		var case_attribute_id = $('input[name="case_attribute_id"]').val();
		var url = get_hostname()+'/parts/';
		var data = { mode: 'get_case_order_list', clinic_id: clinic_id, case_attribute_id: case_attribute_id, site: site_pathname };
		CallAjax( url, "get", data, 'html', 'get_case_order_list' );
	}
});

$(document).on('click', '.case-order-select li', function(e){
	e.preventDefault();
	var case_id = $('input[name="case_id"]').val();
	var sort_order = $(this).data('order');
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'update_order', element_type:site_pathname+'_case', case_id: case_id, sort_order: sort_order };
	CallAjax( url, "get", data, 'html', 'case_update_order', sort_order);
});

$(document).on('click', '.profile-search #searchIcon', function(e){
	e.preventDefault();
	var search_text = $('.profile-search  input[name="search_text"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_profile_list', search_text: search_text, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'get_profile_list' );
});

$(document).on('keyup', '.profile-search #searchText', function(e){
	e.preventDefault();
	if(e.key==='Enter'||e.keyCode===13){
        $('.profile-search #searchIcon').trigger('click');
    }
});


$(document).on('keypress', '.profile-search #searchText', function(e){
	e.preventDefault();
	if(e.key==='Enter'||e.keyCode===13){
        $('.profile-search #searchIcon').trigger('click');
    }
});


$(document).on('click', '.add-profile', function(e){
	e.preventDefault();
	var doctor_id = $(this).data('id');
	$('input[name="doctor_id"]').val(doctor_id);
	$('#profileList').empty();
});

$(document).on('click', '#updateCase', function(){
	if ($("#caseForm").validationEngine("validate")) {
		var case_id = $('#caseForm input[name="case_id"]').val();
		var url = get_hostname()+cmsroot+'/case/' + case_id;
		var form = new FormData($('#caseForm').get(0));
		form.append('site_pathname', site_pathname);
		form.append('site_id', $('input[name="site_id"]').val());

		CallAjax( url, "post", form, 'html', 'form_update' );
	}
});

/* basic tab */
$('#sectionNav li#basic a').on('click', function(e){
	e.preventDefault();
	var clinic_id = $(this).data('id');
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_basic_clinic_info', clinic_id: clinic_id };
	CallAjax( url, "get", data, 'html', 'get_basic_clinic_info' );
});

/* function */
function get_now_edited_recommend()
{
	if ($('.now-edited-box').length > 0) {
		var clinic_id = $('#infoForm input[name="clinic_id"]').val();
		var recommend_id = $('.now-edited-box').data('id');
		var url = get_hostname()+'/parts/';
		var data = { mode: 'get_now_edited_recommend', site:site_pathname, clinic_id: clinic_id, recommend_id: recommend_id };
		CallAjax( url, "get", data, 'html', 'get_now_edited_recommend' );
	}
	
}

function get_recommends_by_clinic(page)
{
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_recommends_by_clinic', site:site_pathname, clinic_id: clinic_id, page: page };
	CallAjax( url, "get", data, 'html', 'get_recommends_by_clinic' );
}

function get_cases_by_clinic(page)
{
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_cases_by_clinic', site:site_pathname, clinic_id: clinic_id, page: page };
	CallAjax( url, "get", data, 'html', 'get_cases_by_clinic' );
}

function activate_datetimepicker() {
	$('#contractStartOn').datetimepicker({
		timepicker:false,
		format: 'Y-m-d',
		scrollMonth: false,
		scrollInput: false
	});
	$('#contractEndOn').datetimepicker({
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
	$('#casePublishAt').datetimepicker({
		timepicker:false,
		format: 'Y-m-d',
		scrollMonth: false,
		scrollInput: false
	});
}

function change_selected_image(type, image_id)
{
	$('.image-box-row.'+type+ ' li').removeClass('active '+site_pathname+'-active');
	$('.image-box-row.'+type+' li[data-id="'+image_id+'"]').addClass('active '+site_pathname+'-active');
	$('input[name="'+type+'_image_id"]').val(image_id);
}

function unset_sort_orer()
{
	var recommend_id = $('input[name="recommend_id"]').val();
	var clinic_id = $('#infoForm input[name="clinic_id"]').val();
	var sort_order = null;
	var url = get_hostname()+cmsroot+'/element/';
	var data = { mode: 'update_order', element_type:site_pathname+'_recommend_clinic', recommend_id: recommend_id, clinic_id: clinic_id, sort_order: sort_order };
	CallAjax( url, "get", data, 'html', 'update_order', sort_order);
}

function has_case()
{
	var has_case = false;
	$('.feature-type').each(function(){
		if ($(this).val() === 'case') {
			has_case = true;
			return false;
		}
	});
	return has_case;
}

// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_now_edited_recommend = function(){
		$('.now-edited-box').empty();
		$('.now-edited-box').append($.ajaxResponse.responseText);
	}
	this.get_recommends_by_clinic = function(){
		$('.recommend-list-box').empty();
		$('.recommend-list-box').append($.ajaxResponse.responseText);
	}
	this.get_cases_by_clinic = function(){
		$('.case-list-box').empty();
		$('.case-list-box').append($.ajaxResponse.responseText);
	}
	this.get_city_list = function(){
		$('.city-row').remove();
		$('.city-table').append($.ajaxResponse.responseText);
	}
	this.add_clinic_station = function(){
		$('.station-list').append($.ajaxResponse.responseText);
	}
	this.get_station_list = function(){
		$('.station-row').remove();
		$('.station-table').append($.ajaxResponse.responseText);
	}
	this.add_operation_time = function(){
		$('.time-table table').append($.ajaxResponse.responseText);
	}
	this.form_update = function(){
		alert('更新しました');
		//alert($.ajaxResponse.responseText);
	}
	this.get_recommend_features_by_clinic = function(){
		$('#featureModal').empty();
		$('#featureModal').append($.ajaxResponse.responseText);
		activate_datetimepicker();
		autosize($('textarea'));
	}
	this.get_recommend_type2_features_by_clinic = function(){
		$('#type2FeatureModal').empty();
		$('#type2FeatureModal').append($.ajaxResponse.responseText);
		activate_datetimepicker();
		autosize($('textarea'));
		if (has_case()) $('.pr-image-area').css('display', 'none');
	}
	this.upload_file = function(){
		$('.image-box-row').append($.ajaxResponse.responseText);
	}
	this.upload_case_file = function(case_type){
		$('.image-box-row.'+case_type).append($.ajaxResponse.responseText);
	}
	this.delete_file = function(image_id){
		$('li.clinic-image[data-id='+image_id+']').remove();
		alert('削除しました');
	}
	this.delete_case_file = function(image_id){
		$('li.case-image[data-id='+image_id+']').remove();
		alert('削除しました');
	}
	this.get_basic_clinic_info = function(){
		$('.info-wrapper').empty();
		$('.info-wrapper').append($.ajaxResponse.responseText);
		$('#sectionNav li').removeClass('active');
		$('#sectionNav li#basic').addClass('active');
	}
	this.get_clinics_by_recommend = function(){
		$('#otherClinicModal').empty();
		$('#otherClinicModal').append($.ajaxResponse.responseText);
	}
	this.get_order_list = function(){
		$('.order-select').empty();
		$('.order-select').append($.ajaxResponse.responseText);
	}
	this.get_case_order_list = function(){
		$('.case-order-select').empty();
		$('.case-order-select').append($.ajaxResponse.responseText);
	}
	this.update_order = function(sort_order){
		$('.sort-num').text(sort_order);
		$('input[name=sort_order]').val(sort_order);
		$('.order-select').empty();
	}
	this.case_update_order = function(sort_order){
		$('.case-sort-num').text(sort_order);
		$('input[name=sort_order]').val(sort_order);
		$('.case-order-select').empty();
	}
	this.add_clinic = function(){
		try {
			JSON.parse($.ajaxResponse.responseText);
			alert('歯科DBに存在しない医院IDです')
		} catch (error) {
			$('.h2-box.clinic-name').css('display', 'flex');
			$('.cms-list-style.clinic').append($.ajaxResponse.responseText);
		}
	}
	this.unrelate_profile = function(){
		$('.profile').empty();
		alert('解除しました');
	}
	this.get_feature_fee = function(){
		$('.fee-table table tbody').append($.ajaxResponse.responseText);
	}
	this.get_feature_flow = function(){
		$('.flow-table table tbody').append($.ajaxResponse.responseText);
	}
	this.get_basic_feature = function(feature_id){
		$('#feature'+feature_id).append($.ajaxResponse.responseText);
	}
	this.get_case_feature = function(feature_id){
		$('#feature'+feature_id).append($.ajaxResponse.responseText);
	}
	this.get_case_by_id = function(){
		$('#caseModal').empty();
		$('#caseModal').append($.ajaxResponse.responseText);
		autosize($('textarea'));
		activate_datetimepicker();
	}
	this.get_profile_list = function(){
		$('#profileList').empty();
		$('#profileList').append($.ajaxResponse.responseText);
	}
	this.update_case_preview = function(button){
		alert('更新しました');
		//alert($.ajaxResponse.responseText);
		button.remove();
	}
	this.publish = function(){
		alert('公開しました');
		location.reload();
	}
	this.unpublish = function(){
		alert('非公開にしました');
		location.reload();
	}
};
var Callback = new CallbackClass();