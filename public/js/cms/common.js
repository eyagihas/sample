$(function(){
    /*
    window.setTimeout(function () {
        $(".fade").slideUp("slow")
    }, 5000);

    $('.alert > .close').on('click',function(){
        $('.alert').remove();
    });

    //メニュー開閉
    $('.drawer').drawer();
    $('.menu').on('click',function(e){
        e.preventDefault();
        $('.drawer').drawer('toggle');
    });
    */
});

function get_hostname(){
	var hostname = location.protocol+'//'+location.hostname+':'+location.port;
	return hostname;
}

function html_check(field, rules, i, options) {
    var str = field.val();
    if (str.match(/[<>'"&]/g) !== null) {
        options.allrules.validate2fields.alertText = '半角の<>\'"&は入力できません、全角に変更してください';
        return options.allrules.validate2fields.alertText;
    }
}

function halfkana_check(field, rules, i, options) {
    var str = field.val();
    if (str.match(/[\uFF65-\uFF9F]/g) !== null) {
        options.allrules.validate2fields.alertText = '半角カナは入力できません';
        return options.allrules.validate2fields.alertText;
    }
}

function kana_check(field, rules, i, options) {
    var str = field.val();
    if (!str.match(/[\u30A0-\u30FF]/g)) {
        options.allrules.validate2fields.alertText = '全角カナ文字以外入力できません';
        return options.allrules.validate2fields.alertText;
    }
}

function alphanumeric_check(field, rules, i, options) {
    var str = field.val();
    if (!str.match(/^[a-zA-Z0-9!-/:-@¥[-`{-~]/g)) {
        options.allrules.validate2fields.alertText = '英数字記号以外入力できません';
        return options.allrules.validate2fields.alertText;
    }
}

function postcode_check(field, rules, i, options) {
    var str = field.val();
    if (!str.match(/^\d{3}-\d{4}$|^\d{7}$/g)) {
        options.allrules.validate2fields.alertText = '郵便番号が正しくありません';
        return options.allrules.validate2fields.alertText;
    }
}

// Ajax処理
$.ajaxResponse = {};
var CallAjax = function(url,method,data,data_type,callback_function,callback_param){
	var ajax_request = {url:url, type:method, data:data, dataType:data_type, cache: false};
	if ( method == 'post' ) {
		ajax_request.processData = false;
		ajax_request.contentType = false;
	}

	$.ajax(ajax_request)
	.done(function (response, status, xhr) {
		$.ajaxResponse = { 'responseText': xhr.responseText, 'status': xhr.status, 'statusText': xhr.statusText };
		return Callback[callback_function](callback_param);
	}).fail(function (jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			alert("失敗: サーバー内でエラーがあったか、サーバーから応答がありませんでした。");
	});

};
