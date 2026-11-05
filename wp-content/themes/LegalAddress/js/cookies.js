function gtagUpdate(params){
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push(['consent', 'update', params]);
}
function deleteGaCookies(){
  var host = location.hostname.replace(/^www\./, '');
  document.cookie.split(';').forEach(function (c) {
    var name = c.split('=')[0].trim();
    if (/^(_ga|_gid|_gat)/.test(name)) {
      ['', '.' + host, host].forEach(function (d) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + (d ? '; domain=' + d : '');
      });
    }
  });
}
function initCookieConsent(){
  var banner = document.getElementById('lae-cookie-consent');
  if (!banner) return;
  applyTranslations(getCurrentLanguage());

  function saveChoice(choice){
    setCookie(COOKIE_NAME, choice, COOKIE_EXPIRY_DAYS);
    banner.setAttribute('hidden', '');
    if (choice === 'analytics_granted') {
      gtagUpdate({ analytics_storage: 'granted' });
    } else {
      gtagUpdate({ analytics_storage: 'denied' });
      deleteGaCookies();
    }
    window.dataLayer.push({ event: 'lae_consent_update', lae_consent: choice });
  }

  var a = document.getElementById('lae-cookie-accept');
  var r = document.getElementById('lae-cookie-reject');
  var c = document.getElementById('lae-cookie-close');
  if (a) a.addEventListener('click', function(){ saveChoice('analytics_granted'); });
  if (r) r.addEventListener('click', function(){ saveChoice('necessary_only'); });
  if (c) c.addEventListener('click', function(){ saveChoice('necessary_only'); });

  // Ссылка «Cookie settings» в футере снова открывает баннер
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-lae-cookie-settings]')) {
      e.preventDefault();
      banner.removeAttribute('hidden');
    }
  });

  if (!getCookie(COOKIE_NAME)) {
    setTimeout(function(){ banner.removeAttribute('hidden'); }, 800);
  }
}