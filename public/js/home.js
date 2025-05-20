$(document).ready(function () {
  $('.recommend__slide').slick({
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

$(document).ready(function () {
  $('.search__slide').slick({
    infinite: true,
    dots: false,
    slidesToShow: 3,
    swipe: true,
    swipeToSlide: true,
    autoplay: false,
    centerMode: true,
    appendArrows: $('.slideArrows__search-item'),
    prevArrow: '<img src="/image/common/prev_arrow.svg" class="slide-arrow slideArrows__search-prev" alt="戻る">',
		nextArrow: '<img src="/image/common/next_arrow.svg" class="slide-arrow slideArrows__search-next" alt="次へ">',
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

$(document).ready(function () {
  $('.search__slideRailway').slick({
    infinite: true,
    dots: false,
    slidesToShow: 3,
    swipe: true,
    swipeToSlide: true,
    autoplay: false,
    centerMode: true,
    appendArrows: $('.slideArrows__searchRailway-item'),
    prevArrow: '<img src="/image/common/prev_arrow.svg" class="slide-arrow slideArrows__searchRailway-prev" alt="戻る">',
    nextArrow: '<img src="/image/common/next_arrow.svg" class="slide-arrow slideArrows__searchRailway-next" alt="次へ">',
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

  $('.tabItem__wrapper > li').click(function () {
    $('.search__slideRailway').slick('setPosition');
  });

});

$(document).ready(function () {
  $('.experts__slide').slick({
    infinite: true,
    dots: false,
    slidesToShow: 5,
    swipe: true,
    swipeToSlide: true,
    autoplay: false,
    centerMode: true,
    appendArrows: $('.slideArrows__experts-item'),
    prevArrow: '<img src="/image/common/prev_arrow.svg" class="slide-arrow slideArrows__experts-prev" alt="戻る">',
		nextArrow: '<img src="/image/common/next_arrow.svg" class="slide-arrow slideArrows__experts-next" alt="次へ">',
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
          slidesToShow: 4,
        }
      },
      {
        breakpoint: 768, // 399px以下のサイズに適用
        settings: {
          centerPadding: '80px',
          slidesToShow: 1,
        },
      },
    ]
  });
});


document.addEventListener("DOMContentLoaded",() => {
  const title = document.querySelectorAll('.jsAccordion__title');
  
  for (let i = 0; i < title.length; i++){
    let titleEach = title[i];
    let content = titleEach.nextElementSibling;
    titleEach.addEventListener('click', () => {
      titleEach.classList.toggle('is__active');
      content.classList.toggle('is__open');
    });
  }

});

document.addEventListener('DOMContentLoaded', function () {
  const targets = document.getElementsByClassName('tabItem');
  for (let i = 0; i < targets.length; i++) {
      targets[i].addEventListener('click', changeTab, false);
  }
  // タブメニューボタンをクリックすると実行
  function changeTab() {
      // タブのclassを変更
      document.getElementsByClassName('is-active')[0].classList.remove('is-active');
      this.classList.add('is-active');
      // コンテンツのclassの値を変更
      document.getElementsByClassName('is-display')[0].classList.remove('is-display');
      const arrayTabs = Array.prototype.slice.call(targets);
      const index = arrayTabs.indexOf(this);
      document.getElementsByClassName('tabContent')[index].classList.add('is-display');
  };
}, false);