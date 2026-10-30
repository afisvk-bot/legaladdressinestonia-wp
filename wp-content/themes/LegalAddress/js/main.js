document.addEventListener("DOMContentLoaded", function(){
  let vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`);
});

//отметим политику по умолчанию
$("form div.politika input").attr("checked","checked");

// Mobile menu show
$(".mob_btn").click(function () {
  $(this).toggleClass("open");
  $(".menu").toggleClass("active");
})

//Menu mob
const btns = $('.btnContact')
const btnContact = $('#btnContact')
const contactForm = $('#contactForm')
let contactFormIsOpen = false

const contactFormOpen = _ => {
  btnContact.addClass("active")
  contactForm.addClass("open")
  contactForm.css('visibility','visible')
  btnContact.text(btnContact.data("text2"))
  $('html').css('overflow', 'hidden')   // <--- фиксируем страницу
  contactFormIsOpen = true
}

const contactFormClose = _ => {
  btnContact.removeClass("active")
  contactForm.removeClass("open")
  contactForm.css('visibility','hidden') // тут лучше hidden вместо invisible
  btnContact.text(btnContact.data("text1"))
  $('html').css('overflow', '')          // <--- возвращаем скролл
  contactFormIsOpen = false
}

btnContact.click((e) => {
  e.preventDefault()
  if (contactFormIsOpen) {
    contactFormClose()
  } else {
    contactFormOpen()
  }
})

btns.click((e) => {
  e.preventDefault()
  if (!contactFormIsOpen) {
    contactFormOpen()
  }
})

$(function () {

  function closeBranch($li) {
    $li.removeClass('open');

    $li.children('.mob-sub-menu')
       .stop(true, true)
       .slideUp()
       .find('li').removeClass('open')          // снимаем open у всех потомков
       .children('.mob-sub-menu')               // и прячем все вложенные подменю
       .stop(true, true)
       .hide();
  }

  // Открыть пункт (только его собственное подменю)
  function openBranch($li) {
    $li.addClass('open');
    $li.children('.mob-sub-menu').stop(true, true).slideDown();
  }

  /* ---------- аккордеон ---------- */

  function Accordion(el, multiple) {
    this.el       = el;
    this.multiple = !!multiple;                 // false = открыт только один на уровне
    this.el.on('click', '.link', { multiple: this.multiple }, this.dropdown);
  }

  Accordion.prototype.dropdown = function (e) {
    var $link = $(this),
        $li   = $link.parent(),
        $sub  = $li.children('.mob-sub-menu');  // подменю ИМЕННО этого уровня

    // обычный пункт без подкатегорий — не мешаем переходу по ссылке
    if (!$sub.length) return;

    e.preventDefault();
    e.stopPropagation();                        // клик по 3-му уровню не дёргает 2-й

    if ($li.hasClass('open')) {
      closeBranch($li);
      return;
    }

    openBranch($li);

    if (!e.data.multiple) {
      // закрываем ТОЛЬКО соседей своего уровня — вложенность соседей не трогаем
      $li.siblings('li').each(function () {
        closeBranch($(this));
      });
    }
  };

  new Accordion($('#mob-menu'), false);

});





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