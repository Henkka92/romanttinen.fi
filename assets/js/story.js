/**
 * Generate 1080x1920 story PNG via canvas — locked Wine/Cream teaser (no spoilers).
 */
(function () {
  'use strict';

  var WINE = '#4A1A23';
  var CREAM = '#E8DED1';

  var BOKEH = [
    { x: 0.16, y: 0.12, r: 0.20, rgb: [232, 222, 209], a: 0.46 },
    { x: 0.86, y: 0.10, r: 0.24, rgb: [176, 88, 102], a: 0.42 },
    { x: 0.10, y: 0.42, r: 0.18, rgb: [232, 222, 209], a: 0.22 },
    { x: 0.92, y: 0.52, r: 0.22, rgb: [120, 40, 55], a: 0.50 },
    { x: 0.70, y: 0.20, r: 0.14, rgb: [232, 222, 209], a: 0.28 },
    { x: 0.38, y: 0.90, r: 0.26, rgb: [168, 78, 92], a: 0.34 },
    { x: 0.22, y: 0.78, r: 0.12, rgb: [232, 222, 209], a: 0.36 },
    { x: 0.80, y: 0.86, r: 0.11, rgb: [232, 222, 209], a: 0.26 },
    { x: 0.52, y: 0.06, r: 0.10, rgb: [196, 130, 138], a: 0.28 },
    { x: 0.08, y: 0.62, r: 0.09, rgb: [160, 70, 85], a: 0.30 }
  ];

  function rgba(rgb, a) {
    return 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + a + ')';
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

  function drawBokeh(ctx, w, h) {
    ctx.fillStyle = WINE;
    ctx.fillRect(0, 0, w, h);

    BOKEH.forEach(function (orb) {
      var x = orb.x * w;
      var y = orb.y * h;
      var r = orb.r * w;
      var g = ctx.createRadialGradient(x, y, 0, x, y, r);
      g.addColorStop(0, rgba(orb.rgb, orb.a));
      g.addColorStop(0.42, rgba(orb.rgb, orb.a * 0.45));
      g.addColorStop(1, rgba(orb.rgb, 0));
      ctx.fillStyle = g;
      ctx.beginPath();
      ctx.arc(x, y, r, 0, Math.PI * 2);
      ctx.fill();
    });

    var vignette = ctx.createRadialGradient(w / 2, h / 2, w * 0.18, w / 2, h / 2, w * 0.78);
    vignette.addColorStop(0, 'rgba(40, 8, 14, 0)');
    vignette.addColorStop(1, 'rgba(40, 8, 14, 0.38)');
    ctx.fillStyle = vignette;
    ctx.fillRect(0, 0, w, h);
  }

  function drawStory(canvas) {
    var ctx = canvas.getContext('2d');
    var w = canvas.width;
    var h = canvas.height;

    drawBokeh(ctx, w, h);

    var cardX = 86;
    var cardY = 200;
    var cardW = 908;
    var cardH = 1520;
    var cardR = 52;
    var cx = w / 2;

    ctx.save();
    ctx.shadowColor = 'rgba(20, 4, 8, 0.35)';
    ctx.shadowBlur = 48;
    ctx.shadowOffsetY = 18;
    ctx.fillStyle = CREAM;
    roundRect(ctx, cardX, cardY, cardW, cardH, cardR);
    ctx.fill();
    ctx.restore();

    ctx.textAlign = 'center';
    ctx.fillStyle = WINE;
    ctx.textBaseline = 'alphabetic';

    var y = cardY + 168;
    ctx.font = '500 52px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('Romanttinen', cx, y);

    y += 36;
    ctx.strokeStyle = WINE;
    ctx.lineWidth = 1.25;
    ctx.beginPath();
    ctx.moveTo(cx - 38, y);
    ctx.lineTo(cx + 38, y);
    ctx.stroke();

    y += 108;
    ctx.font = '600 72px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('Sinut on kutsuttu', cx, y);
    y += 86;
    ctx.fillText('treffeille', cx, y);

    y += 64;
    ctx.font = '500 30px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('Pieni kutsu — avaa kun olet valmis.', cx, y);

    y += 72;
    var box = 168;
    var gap = 28;
    var total = box * 3 + gap * 2;
    var startX = cx - total / 2;
    var units = ['päivää', 'tuntia', 'min'];
    units.forEach(function (label, i) {
      var bx = startX + i * (box + gap);
      ctx.strokeStyle = WINE;
      ctx.lineWidth = 1.75;
      roundRect(ctx, bx, y, box, box, 18);
      ctx.stroke();

      ctx.fillStyle = WINE;
      ctx.font = '500 52px "Cormorant Garamond", Georgia, serif';
      ctx.fillText('··', bx + box / 2, y + 78);
      ctx.font = '500 22px "Cormorant Garamond", Georgia, serif';
      ctx.fillText(label, bx + box / 2, y + 128);
    });

    y += box + 78;
    ctx.font = '500 34px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('Avaa kutsu', cx, y);
    var ctaW = ctx.measureText('Avaa kutsu').width;
    ctx.strokeStyle = WINE;
    ctx.lineWidth = 1.15;
    ctx.beginPath();
    ctx.moveTo(cx - ctaW / 2, y + 10);
    ctx.lineTo(cx + ctaW / 2, y + 10);
    ctx.stroke();

    ctx.font = '400 22px "DM Sans", "Cormorant Garamond", Georgia, serif';
    ctx.fillStyle = WINE;
    ctx.fillText('romanttinen.fi', cx, cardY + cardH - 64);
  }

  function whenFontsReady(cb) {
    if (document.fonts && document.fonts.ready) {
      var loads = [];
      try {
        loads.push(document.fonts.load('500 52px "Cormorant Garamond"'));
        loads.push(document.fonts.load('600 72px "Cormorant Garamond"'));
        loads.push(document.fonts.load('400 22px "DM Sans"'));
      } catch (e) {
        loads = [];
      }
      Promise.all(loads.concat([document.fonts.ready])).then(cb, cb);
    } else {
      cb();
    }
  }

  function init() {
    var btn = document.getElementById('romant-download-story');
    var canvas = document.getElementById('romant-story-canvas');
    if (!btn || !canvas) return;

    btn.addEventListener('click', function () {
      whenFontsReady(function () {
        drawStory(canvas);
        canvas.toBlob(function (blob) {
          if (!blob) return;
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = 'romanttinen-tarina.png';
          a.click();
          URL.revokeObjectURL(a.href);
        }, 'image/png');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
