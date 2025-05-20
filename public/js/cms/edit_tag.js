const site_pathname = $('input[name="site_pathname"]').val();
const cmsroot = '/'+site_pathname+'_cms';

$(function(){
	//validationEngine
	$('#editTag').validationEngine({
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

$('.edit-tag').on('click', function(){
	var tag_id = $(this).data('id');
	var url = get_hostname()+'/parts/';
	var data = { mode: 'get_tag_info', tag_id: tag_id, site_pathname: site_pathname };
	CallAjax( url, "get", data, 'html', 'get_tag_info');
});

$('.delete-tag').on('click', function(){
	if(!confirm('本当に削除しますか？')){
        return false;
    }
	var tag_id = $(this).data('id');
	var url = get_hostname()+'/parts/';
	var data = { mode: 'delete_tag', tag_id: tag_id, site_pathname: site_pathname };
	var button = $(this);
	CallAjax( url, "get", data, 'html', 'delete_tag', button);
});

$('#add').on('click', function(e){
	e.preventDefault();
	if ($("#addForm").validationEngine("validate")) {
		var form = new FormData($('#addForm').get(0));
		form.append('mode', 'add');
		var url = get_hostname()+cmsroot+'/tag/0';
		CallAjax( url, "post", form, 'html', 'add');
	}
});

$(document).on('click', '#updateTag', function(){
	if ($("#editTag").validationEngine("validate")) {
		var tag_id = $('input[name="tag_id"]').val();
		var tag_name = $('input[name="tag_name"]').val();
		var url = get_hostname()+cmsroot+'/tag/' + tag_id;
		var form = new FormData($('#editTag').get(0));
		form.append('mode', 'update');

		CallAjax( url, "post", form, 'html', 'update' );
	}
});



// Ajax処理コールバック処理
var CallbackClass = function() {
	this.get_tag_info = function(){
		$('#editModal').empty();
		$('#editModal').append($.ajaxResponse.responseText);
	}
	this.add = function(){
		alert('追加しました');
		location.href = get_hostname()+cmsroot+'/tag_list/';
	}
	this.update = function(){
		alert('更新しました');
		location.reload();
	}
	this.delete_tag = function(button){
		alert('削除しました');
		button. parent().parent('li').remove();
	}
};
var Callback = new CallbackClass();
