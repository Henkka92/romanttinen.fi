/**
 * Generate 1080×1920 story PNG — Henry PASS teaser (fabric + cream card).
 * No spoilers / date / place / hints on the image.
 */
(function () {
  'use strict';

  var WINE = '#4A1F2C';
  var CARD = '#FFFDF9';
  var CELL_BG = '#FFFFFF';
  var CELL_BORDER = 'rgba(74, 31, 44, 0.14)';
  var LABEL = 'rgba(74, 31, 44, 0.42)';
  var BRAND_FAINT = 'rgba(74, 31, 44, 0.28)';
  var FALLBACK_BG = '#e8d4c4';

  var cachedFabric = null;
  var cachedFabricUrl = '';

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
      d: pad(d),
      h: pad(h),
      m: pad(m),
      s: pad(s),
      done: diff === 0
    };
  }

  function roundRect(ctx, x, y, w, h, r) {
    var rad = Math.min(r, w / 2, h / 2);
    ctx.beginPath();
    ctx.moveTo(x + rad, y);
    ctx.arcTo(x + w, y, x + w, y + h, rad);
    ctx.arcTo(x + w, y + h, x, y + h, rad);
    ctx.arcTo(x, y + h, x, y, rad);
    ctx.arcTo(x, y, x + w, y, rad);
    ctx.closePath();
  }

  function drawCoverTop(ctx, img, w, h) {
    var ir = img.naturalWidth / img.naturalHeight;
    var cr = w / h;
    var dw;
    var dh;
    var dx;
    var dy;
    if (ir > cr) {
      dh = h;
      dw = h * ir;
      dx = (w - dw) / 2;
      dy = 0;
    } else {
      dw = w;
      dh = w / ir;
      dx = 0;
      dy = 0;
    }
    ctx.drawImage(img, dx, dy, dw, dh);
  }

  function drawBokeh(ctx, w, h) {
    var orbs = [
      { x: 0.12 * w, y: 0.07 * h, r: 130, a: 0.34 },
      { x: 0.88 * w, y: 0.09 * h, r: 150, a: 0.26 },
      { x: 0.74 * w, y: 0.035 * h, r: 90, a: 0.2 },
      { x: 0.22 * w, y: 0.14 * h, r: 70, a: 0.16 }
    ];
    orbs.forEach(function (o) {
      var g = ctx.createRadialGradient(o.x, o.y, 0, o.x, o.y, o.r);
      g.addColorStop(0, 'rgba(255, 226, 150,' + o.a + ')');
      g.addColorStop(0.45, 'rgba(255, 210, 120,' + (o.a * 0.35) + ')');
      g.addColorStop(1, 'rgba(255, 210, 120, 0)');
      ctx.fillStyle = g;
      ctx.beginPath();
      ctx.arc(o.x, o.y, o.r, 0, Math.PI * 2);
      ctx.fill();
    });
  }

  function drawDivider(ctx, cx, y, width) {
    var half = width / 2;
    var arcW = 44;
    var arcH = 15;
    ctx.save();
    ctx.strokeStyle = WINE;
    ctx.globalAlpha = 0.72;
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(cx - half, y);
    ctx.lineTo(cx - arcW / 2, y);
    ctx.quadraticCurveTo(cx, y - arcH, cx + arcW / 2, y);
    ctx.lineTo(cx + half, y);
    ctx.stroke();
    ctx.restore();
  }

  function drawStory(canvas, frame, fabricImg) {
    var ctx = canvas.getContext('2d');
    var w = canvas.width;
    var h = canvas.height;
    var cx = w / 2;

    ctx.fillStyle = FALLBACK_BG;
    ctx.fillRect(0, 0, w, h);

    if (fabricImg && fabricImg.naturalWidth > 0) {
      drawCoverTop(ctx, fabricImg, w, h);
    }

    drawBokeh(ctx, w, h);

    var wash = ctx.createLinearGradient(0, 0, 0, h);
    wash.addColorStop(0, 'rgba(243, 230, 216, 0.2)');
    wash.addColorStop(0.42, 'rgba(243, 230, 216, 0.06)');
    wash.addColorStop(1, 'rgba(243, 230, 216, 0.22)');
    ctx.fillStyle = wash;
    ctx.fillRect(0, 0, w, h);

    var cardX = Math.round(w * 0.075);
    var cardY = Math.round(h * 0.065);
    var cardW = w - cardX * 2;
    var cardH = h - cardY * 2;
    var cardR = 44;

    ctx.save();
    ctx.shadowColor = 'rgba(74, 31, 44, 0.18)';
    ctx.shadowBlur = 48;
    ctx.shadowOffsetY = 18;
    ctx.fillStyle = CARD;
    roundRect(ctx, cardX, cardY, cardW, cardH, cardR);
    ctx.fill();
    ctx.restore();

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.55)';
    ctx.lineWidth = 1.5;
    roundRect(ctx, cardX, cardY, cardW, cardH, cardR);
    ctx.stroke();

    ctx.textAlign = 'center';
    ctx.textBaseline = 'alphabetic';

    var innerL = cardX + 78;
    var innerR = cardX + cardW - 78;
    var innerW = innerR - innerL;

    ctx.fillStyle = WINE;
    ctx.font = '600 40px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('romanttinen.fi', cx, cardY + 118);

    var titleLh = 86;
    var teaserSize = 30;
    var gapTitleRule = 50;
    var gapRuleTeaser = 54;
    var gapTeaserCd = 62;
    var gapCdBtn = 54;
    var btnH = 92;
    var cd = countdownParts(frame.getAttribute('data-romant-countdown') || '');
    var gap = 18;
    var cellW = (innerW - gap * 3) / 4;
    var cellH = Math.min(cellW, 170);
    var cdBlock = cd.done ? 120 : cellH;

    var blockH = titleLh * 2 + gapTitleRule + gapRuleTeaser + teaserSize + gapTeaserCd + cdBlock + gapCdBtn + btnH;
    var areaTop = cardY + 118 + 28;
    var areaBot = cardY + cardH - 110;
    var y = areaTop + Math.max(12, (areaBot - areaTop - blockH) / 2);

    ctx.font = '600 72px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('Sinut on', cx, y + titleLh - 8);
    ctx.fillText('kutsuttu treffeille.', cx, y + titleLh * 2 - 8);
    y += titleLh * 2;

    y += gapTitleRule;
    drawDivider(ctx, cx, y, Math.min(innerW * 0.62, 420));

    y += gapRuleTeaser;
    var teaser = frame.getAttribute('data-teaser') || 'Pieni kutsu — avaa kun olet valmis.';
    ctx.fillStyle = WINE;
    ctx.font = '400 30px "Cormorant Garamond", Georgia, serif';
    ctx.fillText(teaser, cx, y);

    y += gapTeaserCd;

    if (cd.done) {
      ctx.fillStyle = WINE;
      ctx.font = '500 36px "Cormorant Garamond", Georgia, serif';
      ctx.fillText('Hetki on täällä.', cx, y + 48);
      y += cdBlock;
    } else {
      var units = [
        { v: cd.d, l: 'Päivää' },
        { v: cd.h, l: 'Tuntia' },
        { v: cd.m, l: 'Minuuttia' },
        { v: cd.s, l: 'Sekuntia' }
      ];
      units.forEach(function (u, i) {
        var x = innerL + i * (cellW + gap);
        ctx.fillStyle = CELL_BG;
        ctx.strokeStyle = CELL_BORDER;
        ctx.lineWidth = 1.5;
        roundRect(ctx, x, y, cellW, cellH, 16);
        ctx.fill();
        ctx.stroke();

        var ncx = x + cellW / 2;
        ctx.fillStyle = WINE;
        ctx.font = '600 52px "Cormorant Garamond", Georgia, serif';
        ctx.fillText(u.v, ncx, y + cellH * 0.52);
        ctx.fillStyle = LABEL;
        ctx.font = '400 20px "Cormorant Garamond", Georgia, serif';
        ctx.fillText(u.l, ncx, y + cellH * 0.8);
      });
      y += cellH;
    }

    y += gapCdBtn;
    var cta = frame.getAttribute('data-cta') || 'Avaa kutsu';
    var btnW = innerW;
    var btnX = innerL;
    ctx.fillStyle = WINE;
    roundRect(ctx, btnX, y, btnW, btnH, btnH / 2);
    ctx.fill();
    ctx.fillStyle = '#FFFFFF';
    ctx.font = '600 36px "Cormorant Garamond", Georgia, serif';
    ctx.fillText(cta, cx, y + btnH * 0.64);

    ctx.fillStyle = BRAND_FAINT;
    ctx.font = '500 24px "Cormorant Garamond", Georgia, serif';
    ctx.fillText('romanttinen.fi', cx, cardY + cardH - 58);
  }

  function loadFabric(url) {
    if (!url) {
      return Promise.resolve(null);
    }
    if (cachedFabric && cachedFabricUrl === url && cachedFabric.complete && cachedFabric.naturalWidth > 0) {
      return Promise.resolve(cachedFabric);
    }
    return new Promise(function (resolve) {
      var img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = function () {
        cachedFabric = img;
        cachedFabricUrl = url;
        resolve(img);
      };
      img.onerror = function () {
        resolve(null);
      };
      img.src = url;
    });
  }

  function waitFonts() {
    if (document.fonts && document.fonts.ready) {
      return document.fonts.ready.catch(function () { return null; });
    }
    return Promise.resolve();
  }

  function downloadPng(canvas, frame) {
    var url = frame.getAttribute('data-fabric') || '';
    Promise.all([loadFabric(url), waitFonts()]).then(function (res) {
      drawStory(canvas, frame, res[0]);
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

  function init() {
    var btn = document.getElementById('romant-download-story');
    var frame = document.getElementById('romant-story-frame');
    var canvas = document.getElementById('romant-story-canvas');
    if (!btn || !frame || !canvas) return;

    loadFabric(frame.getAttribute('data-fabric') || '');

    btn.addEventListener('click', function () {
      downloadPng(canvas, frame);
    });

    if (canvas.hasAttribute('data-romant-preview')) {
      Promise.all([loadFabric(frame.getAttribute('data-fabric') || ''), waitFonts()]).then(function (res) {
        canvas.hidden = false;
        drawStory(canvas, frame, res[0]);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
