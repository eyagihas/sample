$('.tabItem').on('click', function(){
  console.log('test');
  var type = ($(this).hasClass('tab__area')) ? 'area' : 'station';
  var url = '/recommend/';
  url += (type === 'station') ? '?station' : '';

  var position = $(window).scrollTop();

  location.href = url;
  $(window).scrollTop(postion);
});
