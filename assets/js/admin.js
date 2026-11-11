// Mobile sidebar
(function () {
  var btn = document.querySelector('.a-menu');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var open = document.body.classList.toggle('side-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();

// Confirm before destructive actions
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
  f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
});

// Preview a chosen photo before saving
document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
  input.addEventListener('change', function () {
    var img = document.querySelector('img[data-preview-for="' + input.name + '"]');
    if (!img || !input.files || !input.files[0]) return;
    img.src = URL.createObjectURL(input.files[0]);
    img.parentElement.hidden = false;
  });
});

// Warn about very large files before upload
document.querySelectorAll('input[type=file]').forEach(function (input) {
  input.addEventListener('change', function () {
    var big = Array.prototype.some.call(input.files || [], function (f) { return f.size > 6 * 1024 * 1024; });
    if (big) { alert('One of these photos is larger than 6 MB. Please choose a smaller one.'); input.value = ''; }
  });
});
