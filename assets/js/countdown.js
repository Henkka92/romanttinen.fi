/**
 * Live countdown to invite datetime (ISO).
 */
(function () {
  'use strict';

  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function tick(root) {
    var iso = root.getAttribute('data-romant-countdown');
    if (!iso) return;

    var target = Date.parse(iso);
    if (Number.isNaN(target)) return;

    var now = Date.now();
    var diff = Math.max(0, target - now);

    var s = Math.floor(diff / 1000);
    var d = Math.floor(s / 86400);
    s %= 86400;
    var h = Math.floor(s / 3600);
    s %= 3600;
    var m = Math.floor(s / 60);
    s %= 60;

    var elD = root.querySelector('[data-cd="d"]');
    var elH = root.querySelector('[data-cd="h"]');
    var elM = root.querySelector('[data-cd="m"]');
    var elS = root.querySelector('[data-cd="s"]');
    if (elD) elD.textContent = String(d);
    if (elH) elH.textContent = pad(h);
    if (elM) elM.textContent = pad(m);
    if (elS) elS.textContent = pad(s);

    var done = root.querySelector('.romant-countdown-done');
    var cd = root.querySelector('.romant-countdown');
    if (diff === 0 && done) {
      done.hidden = false;
      if (cd) cd.hidden = true;
    }
  }

  function init() {
    var roots = document.querySelectorAll('[data-romant-countdown]');
    if (!roots.length) return;
    roots.forEach(function (root) {
      tick(root);
      setInterval(function () { tick(root); }, 1000);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
