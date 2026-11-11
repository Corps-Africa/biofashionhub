// Mobile menu
(function () {
  var btn = document.querySelector('.nav-toggle');
  if (!btn) return;
  function setOpen(open) {
    document.body.classList.toggle('nav-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
  }
  btn.addEventListener('click', function () { setOpen(!document.body.classList.contains('nav-open')); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
  document.querySelectorAll('.main-nav a').forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
})();

// Product photo switcher
document.querySelectorAll('.pd-thumbs button').forEach(function (b) {
  b.addEventListener('click', function () { document.getElementById('pd-main').src = b.dataset.src; });
});

// Lookbook lightbox
(function () {
  var box = document.getElementById('lightbox');
  if (!box || typeof box.showModal !== 'function') return;
  var img = box.querySelector('img'), cap = box.querySelector('.lightbox-caption'), wa = box.querySelector('.lightbox-wa');
  document.querySelectorAll('.lightbox-open').forEach(function (b) {
    b.addEventListener('click', function () {
      img.src = b.dataset.src;
      img.alt = b.dataset.caption || '';
      cap.textContent = b.dataset.caption || '';
      var msg = 'Hello Bio Fashion Hub, I saw this in your lookbook' + (b.dataset.caption ? ': ' + b.dataset.caption : '') + '. Can you tell me more?';
      wa.href = wa.dataset.base + '?text=' + encodeURIComponent(msg);
      box.showModal();
    });
  });
  box.querySelector('.lightbox-close').addEventListener('click', function () { box.close(); });
  box.addEventListener('click', function (e) { if (e.target === box) box.close(); });
})();
