<!-- ＃リンクのスムーズスクロール -->

  $(function () {
    var headerHight = 0; //ヘッダーの高さを指定しheaderHightに代入
    $('a[href^="#"]:not(a.noscroll)').click(function () {　//アンカーリンクをクリックでイベント処理
      var href = $(this).attr("href");　//アンカーリンクの属性を取得
      var target = $(href == "#" || href == "" ? "html" : href);　//hrefの値が"#"または""だった場合"html"が、それ以外の場合はhrefをtargetに代入
      var position = target.offset().top - headerHight;　//画面上部からターゲット要素までの距離 - ヘッダー高さをpositionに代入
      $("html, body").animate({ scrollTop: position }, 500, "swing");　// 取得したpositionの位置まで0.5秒でゆっくり移動
      return false;　//clickイベント実行後にaタグのhrefリンクを打ち消す
    });
  });

<!-- /＃リンクのスムーズスクロール -->



<!-- ＃ページトップへ -->

jQuery(function() {
  var pagetop = $('#btnPagetop');
  pagetop.hide();
  $(window).scroll(function () {
      if ($(this).scrollTop() > 100) {
          pagetop.fadeIn();
      } else {
          pagetop.fadeOut();
      }
  });
  pagetop.click(function () {
      $('body,html').animate({
          scrollTop: 0
      }, 500);
      return false;
  });
});

<!-- /＃ページトップへ -->



<!-- ＃ハンバーガーメニュー -->

$(function () {
  $('.burgerBtn').on('click', function () {
    $('.burgerBtn').toggleClass('close');
  $('.header__nav-outer').toggleClass('slide-in');
    $('body').toggleClass('noscroll'); // 追記
  });
});

<!-- /＃ハンバーガーメニュー -->


<!-- ＃カレント表示 -->

$(document).ready(function() {
      if(location.pathname != "/") {
          $('a.header__nav-a[href^="/' + location.pathname.split("/")[1] + '"]').addClass('current');
      } else $('a.header__nav-a:eq(0)').addClass('current');
  });

<!-- /＃カレント表示 -->

<!-- # メガメニュー動的取得 -->
$(document).ready(function () {
  $.ajax({
    type: 'GET',
    url: '/parts/',
    data: {'mode':'get_pref_list', 'type':'megamenu'},
  })
  .done(function (response, status, xhr) {
    $.ajaxResponse = { 'responseText': xhr.responseText, 'status': xhr.status, 'statusText': xhr.statusText };
    $('.header__megamenu-wrapper.pref-list').empty();
    $('.header__megamenu-wrapper.pref-list').append($.ajaxResponse.responseText);
  }).fail(function (jqXHR, textStatus, errorThrown) {
      /*
      console.log(jqXHR);
      alert("失敗: サーバー内でエラーがあったか、サーバーから応答がありませんでした。");
      */
  });

});

<!-- # メガメニュー動的取得 -->
