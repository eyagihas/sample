$('.tabItem').on('click', function(){
  var type = ($(this).hasClass('tab__area')) ? 'area' : 'station';
  var url = location.pathname;
  url += (type === 'station') ? '?station' : '';

  var position = $(window).scrollTop();

  location.href = url;
  $(window).scrollTop(postion);
});
