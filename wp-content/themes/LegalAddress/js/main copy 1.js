//отметим политику по умолчанию
$("form div.politika input").attr("checked","checked");

$(document).ready(function () {
  $(".collapse.show").each(function () {
    $(this)
      .prev(".card-header")
      .find(".fa")
      .addClass("fa-minus")
      .removeClass("fa-plus");
  });

  $(".collapse")
    .on("show.bs.collapse", function () {
      $(this)
        .prev(".card-header")
        .find(".fa")
        .removeClass("fa-plus")
        .addClass("fa-minus");
    })
    .on("hide.bs.collapse", function () {
      $(this)
        .prev(".card-header")
        .find(".fa")
        .removeClass("fa-minus")
        .addClass("fa-plus");
    });
});

// Slider
$(document).ready(function () {
  var $slider = $('.one-photo');
  var $counter = $('.counter');
  $slider.on('init', function (event, slick) {
    updateProgressBar(0, slick.slideCount);
    updateCounter(0, slick.slideCount);

    animateCenterSlide();
  });

  $slider.on('afterChange', function (event, slick, currentSlide) {
    updateProgressBar(currentSlide, slick.slideCount);
    updateCounter(currentSlide, slick.slideCount);

    animateCenterSlide();
  });

  $slider.slick({
    centerMode: true,
    centerPadding: '16px',
    slidesToShow: 1,
    arrows: true,
    dots: false,
    infinite: true,
    autoplay: false,
    responsive: [
      {
        breakpoint: 576,
        settings: {
          // centerPadding: '8px',
          arrows: false,
        }
      }
    ]
  });

  function animateCenterSlide() {
    // Убираем класс у всех карточек
    $('.one-photo .team-card').removeClass('animate');
    // Ждем, пока Slick обновит классы
    requestAnimationFrame(function () {
      $('.one-photo .slick-center .team-card').addClass('animate');
    });
  }

  function updateProgressBar(current, total) {
    let percent = ((current + 1) / total) * 100;
    $('.progress-bar span').css('width', percent + '%');
  }

  function updateCounter(current, total) {
    $counter.html((current + 1) + ' / ' + total);
  }

});