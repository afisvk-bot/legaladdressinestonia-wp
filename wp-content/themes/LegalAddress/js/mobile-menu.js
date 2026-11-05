/* Mobile menu: варианты A/B/C (см. inc/mobile-menu.php). Работает поверх обработчиков main.js:
   main.js переключает .mob_btn.open / .menu.active и блокирует прокрутку страницы. */
(function ($) {
  'use strict';

  if (window.laiMMInit) return;   /* защита от двойной загрузки (оптимизаторы JS) */
  window.laiMMInit = true;

  var cfg     = window.laiMM || { variant: 'c', back: 'Back' };
  var variant = cfg.variant;
  var $body   = $('body');
  var $btn    = $('#btnContact');
  var $panel  = $('#mm-panel');
  var menu    = document.getElementById('mob-menu');
  if (!$panel.length || !menu) return;

  /* высота шапки → откуда начинается панель */
  function setTop() {
    var header = document.querySelector('header');
    if (!header) return;
    var bottom = Math.max(0, Math.round(header.getBoundingClientRect().bottom));
    document.documentElement.style.setProperty('--mm-top', bottom + 'px');
  }

  var $backdrop = $('<div class="mm-backdrop" aria-hidden="true"></div>').appendTo($body);

  function isOpen() { return $panel.hasClass('active'); }
  function sync() {
    var open = isOpen();
    $body.toggleClass('mm-open', open);
    $btn.attr('aria-expanded', open ? 'true' : 'false');
  }
  function close() { if (isOpen()) $btn.trigger('click'); }

  /* main.js уже повесил обработчики на кнопку; наш срабатывает после них */
  $btn.on('mousedown touchstart', setTop);
  $btn.on('click', function () { setTimeout(sync, 0); });
  $backdrop.on('click', close);
  $(document).on('keydown', function (e) { if (e.key === 'Escape') close(); });
  $(window).on('resize orientationchange', function () { if (isOpen()) setTop(); });

  /* CTA: если на странице есть модальная форма — открыть её, иначе перейти на «Контакты» */
  $panel.on('click', '.mm-cta', function (e) {
    var sel = $(this).data('mm-modal');
    var $m  = sel ? $(sel) : $();
    if ($m.length && $.fn.modal) {
      e.preventDefault();
      close();
      setTimeout(function () { $m.modal('show'); }, 250);
    }
  });

  /* ---------- B: переход по уровням ---------- */
  if (variant === 'b') {
    $(menu).find('ul.mob-sub-menu').each(function () {
      var $ul    = $(this);
      var $li    = $ul.parent('li');
      var title  = $.trim($li.children('a').first().text());
      $ul.prepend(
        '<li class="mm-back"><button type="button">' + $('<i>').text(cfg.back).html() + '</button></li>' +
        '<li class="mm-title"><span>' + $('<i>').text(title).html() + '</span></li>'
      );
    });

    /* перехватываем клик до аккордеона из main.js (фаза захвата) */
    menu.addEventListener('click', function (e) {
      var back = e.target.closest('li.mm-back > button');
      if (back) {
        e.preventDefault(); e.stopPropagation();
        $(back).closest('li.mm-in').removeClass('mm-in');
        return;
      }
      var link = e.target.closest('a.link');
      if (link && menu.contains(link)) {
        e.preventDefault(); e.stopPropagation();
        $(link).parent('li').addClass('mm-in');
        menu.scrollTop = 0;
      }
    }, true);

    /* при закрытии — вернуться на первый уровень */
    $btn.on('click', function () {
      setTimeout(function () { if (!isOpen()) $(menu).find('li.mm-in').removeClass('mm-in'); }, 350);
    });
  }

  /* ---------- C: вкладки разделов ---------- */
  if (variant === 'c') {
    var $tabs = $('<div class="mm-tabs" role="tablist"></div>');
    $(menu).children('li').each(function (i) {
      var $li = $(this), $a = $li.children('a').first(), text = $.trim($a.text());
      if ($li.children('ul.mob-sub-menu').length) {
        $('<button type="button" role="tab"></button>').text(text).attr('data-i', i).appendTo($tabs);
      } else {
        $('<a></a>').text(text).attr('href', $a.attr('href')).appendTo($tabs);
      }
    });
    $panel.prepend($tabs);

    function show(i) {
      $tabs.children().removeClass('is-on').attr('aria-selected', 'false');
      $tabs.children('[data-i="' + i + '"]').addClass('is-on').attr('aria-selected', 'true');
      $(menu).children('li').removeClass('mm-tab-on').eq(i).addClass('mm-tab-on');
      menu.scrollTop = 0;
    }
    $tabs.on('click', 'button', function () { show(+$(this).attr('data-i')); });

    /* открыть вкладку раздела, в котором находится текущая страница */
    var cur = $(menu).children('li').index($(menu).children('li.current-menu-ancestor, li.current-menu-parent, li.current-menu-item').first());
    show(cur >= 0 && $(menu).children('li').eq(cur).children('ul').length ? cur : +$tabs.children('button').first().attr('data-i') || 0);
  }

  /* ---------- Переключатель языка (мобильные): открытие по тапу, закрытие снаружи ----------
     Нативный обработчик в фазе захвата — срабатывает раньше обработчиков темы/плагинов. */
  var mq = window.matchMedia('(max-width: 991px)');
  function langClose(except) {
    document.querySelectorAll('.mob_ready li.dropdown.mm-lang-open').forEach(function (li) {
      if (li === except) return;
      li.classList.remove('mm-lang-open');
      var a = li.querySelector('a.dropdown-toggle');
      if (a) a.setAttribute('aria-expanded', 'false');
    });
  }
  document.addEventListener('click', function (e) {
    if (!mq.matches) return;
    var toggle = e.target.closest && e.target.closest('.mob_ready li.dropdown > a.dropdown-toggle');
    if (toggle) {
      e.preventDefault();
      e.stopPropagation();
      var li = toggle.parentNode;
      var open = !li.classList.contains('mm-lang-open');
      langClose(li);
      li.classList.toggle('mm-lang-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      return;
    }
    if (!(e.target.closest && e.target.closest('.mob_ready li.dropdown'))) langClose(null);
  }, true);
  $btn.on('click', function () { langClose(null); });

  /* A/C: раскрыть ветку текущей страницы */
  if (variant !== 'b') {
    $(menu).find('li.current-menu-ancestor, li.current-menu-parent').each(function () {
      if (variant === 'c' && $(this).parent().is(menu)) return;
      $(this).addClass('open').children('ul.mob-sub-menu').show();
    });
  }
})(jQuery);
