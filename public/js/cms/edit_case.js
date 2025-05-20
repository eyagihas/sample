const site_pathname = $('input[name="site_pathname"]').val();
const cmsroot = '/'+site_pathname+'_cms';

$(function(){
	//validationEngine
	$('#caseForm').validationEngine({
		promptPosition: "bottomLeft",
		/*ajaxFormValidation: true,*/
		scroll: false,
		binded: true
	});
});

$('#createCase').on('click',function(e){
	e.preventDefault();
	if ($('#caseForm').validationEngine("validate")) {
		var form = $('#caseForm');
		form = append_data(form, 'mode', 'create');
		form.submit();
	}
	return false;
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
	var clinic_id = $(this).data('id');
	var clinic_name = $(this).data('name');
	$('input[name="clinic_id"]').val(clinic_id);
	$('input[name="clinic_name"]').val(clinic_name);

	var url = get_hostname()+'/parts/';
	var data = { 
			mode: 'get_self_cases_by_clinic',
			site:site_pathname,
			clinic_id: clinic_id
		};
	CallAjax( url, "get", data, 'html', 'get_self_cases_by_clinic' );

	$('.close-modal').trigger('click');
});

$(document).on('click', '.self-case-list li', function(e){
	var case_title = $(this).children('label').children('span').text();
	$('input[name="case_title"]').val(case_title);
});

function append_data(form, name, value)
{
	$('<input>').attr({'type': 'hidden', 'name': name, 'value': value}).appendTo(form);
	return form;
}



// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_clinic_list = function(){
		$('#clinicList').empty();
		$('#clinicList').append($.ajaxResponse.responseText);
	}
	this.get_self_cases_by_clinic = function(){
		$('#selfCaseList').empty();
		$('#selfCaseList').append($.ajaxResponse.responseText);
	}
};
var Callback = new CallbackClass();
