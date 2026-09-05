/**
 * Generate 1080x1920 story PNG via canvas — teaser only (no spoilers / dress).
 */
(function () {
  'use strict';

  var WINE = '#4A1F2C';
  var CREAM = '#F3E6D8';
  var ROSE = '#C9A090';
  var BLUSH = '#EBD9CE';

  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function countdownParts(iso) {
    var target = Date.parse(iso || '');
    if (Number.isNaN(target)) {
      return { d: '–', h: '–', m: '–', s: '–', done: false };
    }
    var diff = Math.max(0, target - Date.now());
    var s = Math.floor(diff / 1000);
    var d = Math.floor(s / 86400);
    s %= 86400;
    var h = Math.floor(s / 3600);
    s %= 3600;
    var m = Math.floor(s / 60);
    s %= 60;
    return {
      d: String(d),
      h: pad(h),
      m: pad(m),
      s: pad(s),
      done: diff === 0
    };
  }

  function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
    var words = String(text).split(/\s+/);
    var line = '';
    var lines = [];
    for (var i = 0; i < words.length; i++) {
      var test = line ? line + ' ' + words[i] : words[i];
      if (ctx.measureText(test).width > maxWidth && line) {
        lines.push(line);
        line = words[i];
      } else {
        line = test;
      }
    }
    if (line) lines.push(line);
    lines.forEach(function (l, idx) {
      ctx.fillText(l, x, y + idx * lineHeight);
    });
    return lines.length * lineHeight;
  }

  function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
  }

  function drawStory(canvas, frame) {
    var ctx = canvas.getContext('2d');
    var w = canvas.width;
    var h = canvas.height;

    var grad = ctx.createLinearGradient(0, 0, 0, h);
    grad.addColorStop(0, CREAM);
    grad.addColorStop(0.55, BLUSH);
    grad.addColorStop(1, '#d9b8bc');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, w, h);

    var padX = 80;
    var y = 280;

    ctx.textAlign = 'center';
    var logoImg = frame.querySelector('img.romant-logo');
    if (logoImg && logoImg.naturalWidth > 0) {
      var maxH = 64;
      var aspect = logoImg.naturalWidth / logoImg.naturalHeight;
      var lh = maxH;
      var lw = lh * aspect;
      if (lw > w - padX * 2) {
        lw = w - padX * 2;
        lh = lw / aspect;
      }
      ctx.drawImage(logoImg, (w - lw) / 2, y - lh * 0.35, lw, lh);
      y += lh * 0.65 + 48;
    } else {
      ctx.fillStyle = ROSE;
      ctx.font = '600 28px "DM Sans", sans-serif';
      ctx.fillText('romanttinen', w / 2, y);
      y += 70;
    }

    var inviter = (frame.getAttribute('data-inviter') || '').trim();
    if (inviter) {
      ctx.fillStyle = WINE;
      ctx.font = '500 32px "DM Sans", sans-serif';
      y += wrapText(ctx, inviter + ' kutsui sinut', w / 2, y, w - padX * 2, 40);
      y += 36;
    } else {
      y += 30;
    }

    ctx.fillStyle = WINE;
    ctx.font = '600 72px "Cormorant Garamond", Georgia, serif';
    y += wrapText(ctx, frame.getAttribute('data-title') || 'Sinut on kutsuttu treffeille', w / 2, y, w - padX * 2, 80);
    y += 80;

    var cd = countdownParts(frame.getAttribute('data-romant-countdown') || '');
    if (cd.done) {
      ctx.fillStyle = WINE;
      ctx.font = '500 36px "DM Sans", sans-serif';
      ctx.fillText('Hetki on täällä.', w / 2, y);
    } else {
      var boxW = w - padX * 2;
      var boxH = 200;
      ctx.fillStyle = 'rgba(255,255,255,0.55)';
      roundRect(ctx, padX, y, boxW, boxH, 28);
      ctx.fill();

      var units = [
        { v: cd.d, l: 'pv' },
        { v: cd.h, l: 't' },
        { v: cd.m, l: 'min' },
        { v: cd.s, l: 's' }
      ];
      var cell = boxW / 4;
      units.forEach(function (u, i) {
        var cx = padX + cell * i + cell / 2;
        ctx.fillStyle = WINE;
        ctx.font = '600 56px "DM Sans", sans-serif';
        ctx.fillText(u.v, cx, y + 90);
        ctx.fillStyle = ROSE;
        ctx.font = '500 22px "DM Sans", sans-serif';
        ctx.fillText(u.l, cx, y + 140);
      });
    }

    ctx.fillStyle = ROSE;
    ctx.font = '600 24px "DM Sans", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('romanttinen.fi', w / 2, h - 80);
  }

  function init() {
    var btn = document.getElementById('romant-download-story');
    var frame = document.getElementById('romant-story-frame');
    var canvas = document.getElementById('romant-story-canvas');
    if (!btn || !frame || !canvas) return;

    btn.addEventListener('click', function () {
      drawStory(canvas, frame);
      canvas.toBlob(function (blob) {
        if (!blob) return;
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'romanttinen-tarina.png';
        a.click();
        URL.revokeObjectURL(a.href);
      }, 'image/png');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
