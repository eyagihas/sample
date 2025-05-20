$(document).ready(function () {
  $('.relatedRecommend__list').slick({
    infinite: true,
    dots: false,
    slidesToShow: 3,
    swipe: true,
    swipeToSlide: true,
    autoplay: false,
    centerMode: true,
    appendArrows: $('.slideArrows__item'),
    prevArrow: '<img src="/image/common/prev_arrow.svg" class="slide-arrow slideArrows__prev" alt="戻る">',
		nextArrow: '<img src="/image/common/next_arrow.svg" class="slide-arrow slideArrows__next" alt="次へ">',
    centerPadding: '500px',

    responsive: [
      {
        breakpoint: 3000,
        settings: {
          centerPadding: '400px',
        }
      },
      {
        breakpoint: 2000,
        settings: {
          centerPadding: '350px',
        }
      },
      {
        breakpoint: 1900,
        settings: {
          centerPadding: '350px',
        }
      },
      {
        breakpoint: 1800,
        settings: {
          centerPadding: '350px',
        }
      }, {
        breakpoint: 1700,
        settings: {
          centerPadding: '300px',
        }
      }, {
        breakpoint: 1600,
        settings: {
          centerPadding: '250px',
        }
      },
      {
        breakpoint: 1500,
        settings: {
          centerPadding: '200px',
        }
      },
      {
        breakpoint: 1400,
        settings: {
          centerPadding: '150px',
        }
      },
      {
        breakpoint: 1300,
        settings: {
          centerPadding: '100px',
        }
      },
      {
        breakpoint: 1200,
        settings: {
          centerPadding: '50px',
        }
      },
      {
        breakpoint: 1000,
        settings: {
          centerPadding: '50px',
        }
      },
      {
        breakpoint: 768, // 399px以下のサイズに適用
        settings: {
          centerPadding: '50px',
          slidesToShow: 1,
        },
      },
    ]
  });
});