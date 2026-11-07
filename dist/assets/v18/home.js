/* v1.8 front-end behaviour. Vanilla, progressive enhancement, no dependencies. */
(function () {

	/* 1.9.4 reviews read-more: shown only when the text is really clamped. */
	document.querySelectorAll('.mx-quote').forEach(function (q) {
		var t = q.querySelector('[data-mx-clamp]'), b = q.querySelector('[data-mx-more]');
		if (!t || !b) { return; }
		var check = function () { if (!q.classList.contains('is-open')) { b.hidden = t.scrollHeight <= t.clientHeight + 2; } };
		check();
		window.addEventListener('resize', check, { passive: true });
		var more = b.textContent;
		b.addEventListener('click', function () {
			var open = q.classList.toggle('is-open');
			b.setAttribute('aria-expanded', open ? 'true' : 'false');
			b.textContent = open ? (document.documentElement.lang.indexOf('ar') === 0 ? 'عرض أقل' : 'Show less') : more;
		});
	});
	'use strict';
	var doc = document.documentElement;
	var i18n = window.mxI18n || {};
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
	var level = doc.getAttribute('data-motion') || i18n.motion || 'full';
	var still = reduce.matches || level === 'off';
	var subtle = level === 'subtle';
	if (!still) { doc.classList.add('mx-motion'); }

	/* Theme mode toggle: two states, light <-> dark. "system" resolves to the OS preference. */
	function currentMode() {
		var m = doc.getAttribute('data-theme');
		if (m !== 'light' && m !== 'dark') {
			m = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
			doc.setAttribute('data-theme', m);
		}
		return m;
	}
	document.querySelectorAll('[data-mx-theme]').forEach(function (btn) {
		function label() {
			var m = currentMode();
			var to = m === 'dark' ? 'light' : 'dark';
			btn.setAttribute('aria-label', (i18n.switchTo && i18n.switchTo[to]) || ('Switch to ' + to + ' mode'));
			btn.setAttribute('aria-pressed', m === 'dark' ? 'true' : 'false');
			btn.setAttribute('data-mode', m);
		}
		label();
		btn.addEventListener('click', function () {
			var next = currentMode() === 'dark' ? 'light' : 'dark';
			doc.setAttribute('data-theme', next);
			try { localStorage.setItem('mah-theme', next); } catch (e) { /* Storage blocked: the choice lasts for this page only. */ }
			label();
		});
	});

	/* Mobile menu */
	document.querySelectorAll('[data-mx-menu]').forEach(function (btn) {
		var nav = document.getElementById(btn.getAttribute('aria-controls'));
		if (!nav) { return; }
		btn.addEventListener('click', function () {
			var open = btn.getAttribute('aria-expanded') !== 'true';
			btn.setAttribute('aria-expanded', String(open));
			btn.setAttribute('aria-label', open ? i18n.closeMenu || '' : i18n.openMenu || '');
			nav.classList.toggle('is-open', open);
		});
	});

	/* Sub-menus: tap/click toggles; Escape closes and returns focus. Hover/focus is CSS. */
	document.querySelectorAll('.mx-sub-toggle').forEach(function (t) {
		var li = t.parentNode;
		t.addEventListener('click', function () {
			var open = t.getAttribute('aria-expanded') !== 'true';
			document.querySelectorAll('.mx-nav .is-sub-open').forEach(function (o) {
				if (o !== li) { o.classList.remove('is-sub-open'); o.querySelector('.mx-sub-toggle').setAttribute('aria-expanded', 'false'); }
			});
			t.setAttribute('aria-expanded', String(open));
			li.classList.toggle('is-sub-open', open);
		});
		li.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && li.classList.contains('is-sub-open')) {
				li.classList.remove('is-sub-open'); t.setAttribute('aria-expanded', 'false'); t.focus();
			}
		});
	});
	document.addEventListener('click', function (e) {
		document.querySelectorAll('.mx-nav .is-sub-open').forEach(function (o) {
			if (!o.contains(e.target)) { o.classList.remove('is-sub-open'); o.querySelector('.mx-sub-toggle').setAttribute('aria-expanded', 'false'); }
		});
	});

	/* Logo strip pause button (WCAG 2.2.2) */
	document.querySelectorAll('[data-mx-pause]').forEach(function (b) {
		var strip = b.closest('section').querySelector('.mx-logos');
		b.addEventListener('click', function () {
			var on = b.getAttribute('aria-pressed') !== 'true';
			b.setAttribute('aria-pressed', String(on));
			if (strip) { strip.classList.toggle('is-paused', on); }
		});
	});

	/* Back to top */
	document.querySelectorAll('[data-mx-top]').forEach(function (b) {
		b.addEventListener('click', function () {
			var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
			var main = document.getElementById('content');
			if (main) { main.focus({ preventScroll: true }); }
		});
	});

	/* Contact form: send in place when JS runs; the form still posts normally without it. */
	function mxReadAjaxJson(r) {
		return r.text().then(function (text) {
			var body = String(text == null ? '' : text).replace(/^\uFEFF/, '').trim();
			if (!r.ok && !body) {
				throw new Error('empty');
			}
			var ct = '';
			try { ct = String(r.headers.get('content-type') || '').toLowerCase(); } catch (e) { ct = ''; }
			var looksJson = ct.indexOf('application/json') !== -1 || body.charAt(0) === '{';
			if (!looksJson) {
				throw new Error('non-json');
			}
			try {
				return JSON.parse(body);
			} catch (e) {
				throw new Error('non-json');
			}
		});
	}
	document.querySelectorAll('form[data-mx-form]:not([data-mx-cf7])').forEach(function (f) {
		var live = f.querySelector('[data-mx-status]');
		f.addEventListener('submit', function (e) {
			if (!window.fetch || !window.FormData) { return; }
			e.preventDefault();
			var btn = f.querySelector('[type=submit]');
			f.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
			f.querySelectorAll('.mx-field__err').forEach(function (el) { el.textContent = ''; });
			if (btn) { btn.disabled = true; }
			if (live) { live.className = 'mx-form__status'; live.textContent = i18n.sending || ''; }
			var data = new FormData(f); data.append('mx_ajax', '1');
			var actionAttr = f.getAttribute('action');
			var base = document.baseURI && document.baseURI !== 'about:blank' ? document.baseURI : location.href;
			var actionURL = (actionAttr == null || actionAttr === '') ? base : new URL(actionAttr, base).href;
			fetch(actionURL, { method: 'POST', body: data, credentials: 'same-origin' }).then(mxReadAjaxJson).then(function (res) {
				if (btn) { btn.disabled = false; }
				if (!res || typeof res !== 'object') { throw new Error('non-json'); }
				var d = res.data || {};
				if (res.success) {
					f.reset();
					if (live) { live.className = 'mx-form__status is-ok'; live.textContent = d.message || ''; live.focus(); }
					return;
				}
				var first = null;
				Object.keys(d.errors || {}).forEach(function (k) {
					var el = f.querySelector('[name="' + k + '"]');
					var msg = document.getElementById('mx-err-' + k);
					if (el) { el.setAttribute('aria-invalid', 'true'); first = first || el; }
					if (msg) { msg.textContent = d.errors[k]; }
				});
				if (live) { live.className = 'mx-form__status is-err'; live.textContent = d.message || i18n.failed || ''; }
				if (first) { first.focus(); }
			}).catch(function () {
				if (btn) { btn.disabled = false; }
				if (live) { live.className = 'mx-form__status is-err'; live.textContent = i18n.failed || ''; }
			});
		});
	});

	/* Header border after scroll */
	var bar = document.querySelector('.mx-bar');
	if (bar) {
		var io0 = new IntersectionObserver(function (e) { bar.classList.toggle('is-scrolled', !e[0].isIntersecting); });
		var s = document.createElement('div'); s.style.cssText = 'position:absolute;top:0;height:8px;width:1px';
		document.body.prepend(s); io0.observe(s);
		/* Keep the reserved space equal to the real bar height (logo/menu sizes are admin-set). */
		var setH = function () { document.documentElement.style.setProperty('--mx-bar-h', bar.offsetHeight + 'px'); };
		setH();
		if ('ResizeObserver' in window) { new ResizeObserver(setH).observe(bar); } else { window.addEventListener('resize', setH); }
		/* Optional (admin): hide on scroll down, show on scroll up. Default is fixed with no motion. */
		if (document.documentElement.getAttribute('data-header') === 'hide') {
			var lastY = window.scrollY, ticking = false;
			window.addEventListener('scroll', function () {
				if (ticking) { return; }
				ticking = true;
				requestAnimationFrame(function () {
					var y = Math.max(0, window.scrollY), nav = document.getElementById('mx-nav');
					var open = nav && nav.classList.contains('is-open');
					if (open || y < bar.offsetHeight || y < lastY - 4) { bar.classList.remove('is-tucked'); }
					else if (y > lastY + 4) { bar.classList.add('is-tucked'); }
					lastY = y; ticking = false;
				});
			}, { passive: true });
			bar.addEventListener('focusin', function () { bar.classList.remove('is-tucked'); });
		}
	}

	/* Related posts carousel: buttons + arrow keys, RTL-aware; hidden when nothing overflows. */
	document.querySelectorAll('.mx-rel').forEach(function (sec) {
		var tr = sec.querySelector('.mx-rel__track'), nav = sec.querySelector('.mx-rel__nav');
		if (!tr || !nav) { return; }
		var btns = nav.querySelectorAll('.mx-rel__btn');
		var isRtl = function () { return getComputedStyle(tr).direction === 'rtl'; };
		var step = function () { var c = tr.querySelector('.mx-card'); return c ? c.getBoundingClientRect().width + parseFloat(getComputedStyle(tr).columnGap || 0) : tr.clientWidth; };
		var pos = function () { return Math.abs(tr.scrollLeft); };
		var sync = function () {
			var max = tr.scrollWidth - tr.clientWidth;
			nav.hidden = max < 4;
			btns[0].disabled = pos() < 4;
			btns[1].disabled = pos() > max - 4;
		};
		var go = function (d) { tr.scrollBy({ left: d * step() * (isRtl() ? -1 : 1) }); };
		btns.forEach(function (b) { b.addEventListener('click', function () { go(+b.getAttribute('data-dir')); }); });
		tr.addEventListener('keydown', function (e) {
			if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') { return; }
			e.preventDefault();
			var d = e.key === 'ArrowRight' ? 1 : -1;
			go(isRtl() ? -d : d);
		});
		tr.addEventListener('scroll', function () { requestAnimationFrame(sync); }, { passive: true });
		window.addEventListener('resize', sync);
		sync();
	});

	/* Reveal */
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } });
		}, { rootMargin: '0px 0px 8% 0px', threshold: 0 });
		document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });
		/* Safety net: anything on screen when scrolling stops is fully shown. */
		var revT;
		window.addEventListener('scroll', function () {
			clearTimeout(revT);
			revT = setTimeout(function () {
				document.querySelectorAll('[data-reveal]:not(.is-in)').forEach(function (el) {
					var r = el.getBoundingClientRect();
					if (r.top < window.innerHeight && r.bottom > 0) { el.classList.add('is-in'); }
				});
			}, 150);
		}, { passive: true });
	} else {
		document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('is-in'); });
	}

	/* Device stage: scroll progress drives --e (entry), --p (progress) and the active case. */
	document.querySelectorAll('[data-mx-stage]').forEach(function (stage) {
		var cases = stage.querySelectorAll('.mx-case');
		var shotsD = stage.querySelectorAll('.mx-mac .mx-shot');
		var shotsM = stage.querySelectorAll('.mx-phone .mx-shot');
		var dots = stage.querySelectorAll('.mx-rail button');
		var num = stage.querySelector('[data-mx-num]');
		var cur = stage.querySelector('[data-mx-cur]');
		var n = cases.length, active = -1, visible = false, ticking = false;
		var mobileMq = window.matchMedia('(max-width: 767px)');
		if (!n) { return; }

		function set(list, i) {
			list.forEach(function (el, k) {
				el.classList.toggle('is-prev', k === active && k !== i);
				el.classList.toggle('is-active', k === i);
				if (k !== i && k !== active) { el.classList.remove('is-prev'); }
			});
		}
		function activate(i) {
			if (i === active) { return; }
			set(shotsD, i); set(shotsM, i);
			if (!mobileMq.matches) { cases.forEach(function (c, k) { c.classList.toggle('is-active', k === i); c.setAttribute('aria-hidden', String(k !== i)); c.inert = k !== i; }); }
			dots.forEach(function (d, k) { d.setAttribute('aria-current', String(k === i)); });
			var label = String(i + 1).padStart(2, '0');
			if (num) { num.textContent = label; }
			if (cur) { cur.textContent = label; }
			active = i;
		}
		function frame() {
			ticking = false;
			var r = stage.getBoundingClientRect();
			var vh = window.innerHeight;
			var total = r.height - vh;
			var p = Math.min(1, Math.max(0, -r.top / total));
			var e = (still || subtle) ? 1 : Math.min(1, Math.max(0, (vh - r.top) / vh - 0.15) / 0.85);
			stage.style.setProperty('--p', p.toFixed(4));
			stage.style.setProperty('--e', e.toFixed(4));
			/* first slot of scroll is the entry, then one slot per case */
			activate(Math.min(n - 1, Math.floor(p * n * 0.999)));
		}
		function onScroll() { if (mobileMq.matches) { return; } if (visible && !ticking) { ticking = true; requestAnimationFrame(frame); } }
		new IntersectionObserver(function (e) { visible = e[0].isIntersecting; if (visible) { onScroll(); } }).observe(stage);
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll);
		dots.forEach(function (d, k) {
			d.addEventListener('click', function () {
				var top = stage.getBoundingClientRect().top + window.scrollY;
				var total = stage.offsetHeight - window.innerHeight;
				window.scrollTo({ top: top + total * ((k + 0.5) / n), behavior: still ? 'auto' : 'smooth' });
			});
		});
		activate(0); frame();
		var unlock = function () { if (mobileMq.matches) { cases.forEach(function (c) { c.removeAttribute('aria-hidden'); c.inert = false; }); } else { active = -1; frame(); } };
		if (mobileMq.addEventListener) { mobileMq.addEventListener('change', unlock); } else { mobileMq.addListener(unlock); }
		unlock();
	});

	/* Work: mobile snap carousel (<=767px). All cards stay accessible; arrows/dots move the track. */
	(function () {
		var mq = window.matchMedia('(max-width: 767px)');
		document.querySelectorAll('[data-mx-stage]').forEach(function (stage) {
			var track = stage.querySelector('.mx-cases');
			var cases = track ? Array.prototype.slice.call(track.querySelectorAll('.mx-case')) : [];
			if (cases.length < 2) { return; }
			var rtl = (document.documentElement.dir || getComputedStyle(track).direction) === 'rtl';
			var isAr = /^ar/i.test(document.documentElement.lang || '');
			var nav = document.createElement('div');
			nav.className = 'mx-carousel-nav';
			var dotsW = document.createElement('div');
			dotsW.className = 'mx-carousel-dots';
			dotsW.setAttribute('role', 'group');
			dotsW.setAttribute('aria-label', isAr ? 'المشاريع' : 'Projects');
			var arrows = document.createElement('div');
			arrows.className = 'mx-carousel-arrows';
			var prev = document.createElement('button');
			var next = document.createElement('button');
			prev.type = next.type = 'button';
			prev.setAttribute('aria-label', isAr ? 'المشروع السابق' : 'Previous project');
			next.setAttribute('aria-label', isAr ? 'المشروع التالي' : 'Next project');
			prev.innerHTML = '<span aria-hidden="true">&larr;</span>';
			next.innerHTML = '<span aria-hidden="true">&rarr;</span>';
			arrows.appendChild(prev); arrows.appendChild(next);
			var dots = cases.map(function (c, k) {
				var b = document.createElement('button');
				b.type = 'button';
				var h = c.querySelector('h3');
				b.setAttribute('aria-label', (k + 1) + ' / ' + cases.length + (h ? ' — ' + h.textContent.trim() : ''));
				b.addEventListener('click', function () { go(k); });
				dotsW.appendChild(b);
				return b;
			});
			nav.appendChild(dotsW); nav.appendChild(arrows);
			track.parentNode.insertBefore(nav, track.nextSibling);
			var cur = 0;
			function go(k) {
				k = Math.max(0, Math.min(cases.length - 1, k));
				var tr = track.getBoundingClientRect(), cr = cases[k].getBoundingClientRect();
				var delta = rtl ? (cr.right - tr.right) : (cr.left - tr.left);
				track.scrollBy({ left: delta, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
			}
			function mark() {
				if (!mq.matches) { return; }
				var tr = track.getBoundingClientRect(), best = 0, bd = Infinity;
				cases.forEach(function (c, k) {
					var cr = c.getBoundingClientRect();
					var d = Math.abs(rtl ? (cr.right - tr.right) : (cr.left - tr.left));
					if (d < bd) { bd = d; best = k; }
				});
				cur = best;
				cases.forEach(function (c, k) {
					c.classList.toggle('is-active', k === best);
					c.removeAttribute('aria-hidden');
					c.inert = false;
					c.setAttribute('aria-roledescription', isAr ? 'شريحة' : 'slide');
					c.setAttribute('aria-label', (k + 1) + ' / ' + cases.length);
				});
				dots.forEach(function (d, k) { d.setAttribute('aria-current', String(k === best)); });
				track.style.blockSize = (cases[best].offsetHeight + 14) + 'px';
				prev.disabled = best === 0;
				next.disabled = best === cases.length - 1;
			}
			prev.addEventListener('click', function () { go(cur - 1); });
			next.addEventListener('click', function () { go(cur + 1); });
			track.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowRight') { go(cur + (rtl ? -1 : 1)); }
				if (e.key === 'ArrowLeft') { go(cur + (rtl ? 1 : -1)); }
			});
			var t;
			track.addEventListener('scroll', function () { clearTimeout(t); t = setTimeout(mark, 60); }, { passive: true });
			function onMq() {
				if (mq.matches) { mark(); } else { track.style.blockSize = ''; cases.forEach(function (c) { c.removeAttribute('aria-roledescription'); c.removeAttribute('aria-label'); }); }
			}
			if (mq.addEventListener) { mq.addEventListener('change', onMq); } else { mq.addListener(onMq); }
			onMq();
			window.addEventListener('load', mark);
			window.addEventListener('resize', function () { clearTimeout(t); t = setTimeout(mark, 120); });
		});
	})();


	/* Reading progress + active TOC entry (single posts). */
	var bar2 = document.querySelector('.mx-progress span');
	var prose = document.querySelector('.mx-prose');
	if (bar2 && prose) {
		var busy = false;
		var tick = function () {
			busy = false;
			var r = prose.getBoundingClientRect();
			var p = Math.min(1, Math.max(0, -r.top / Math.max(1, r.height - window.innerHeight)));
			bar2.style.transform = 'scaleX(' + p.toFixed(4) + ')';
		};
		window.addEventListener('scroll', function () { if (!busy) { busy = true; requestAnimationFrame(tick); } }, { passive: true });
		tick();
	}
	var tocLinks = document.querySelectorAll('.mx-toc a');
	/* 2.0.1: smart sticky TOC. Fits: plain CSS sticky. Taller than the viewport: it travels with the
	   page and stops when its bottom (scrolling down) or its top (scrolling up) is visible. No inner scroll. */
	var tocBox = document.querySelector('.mx-toc');
	if (tocBox && tocBox.parentNode) {
		var col = tocBox.parentNode, st = { on: false, o: 0, y: window.scrollY, T: 96, C: 0, CH: 0, h: 0 }, raf = 0;
		var measure = function () {
			tocBox.style.position = ''; tocBox.style.top = '';
			st.on = false;
			if (window.matchMedia('(max-width: 900px)').matches) { return; }
			st.T = parseFloat(getComputedStyle(tocBox).top) || 96;
			st.h = tocBox.offsetHeight;
			if (st.h <= window.innerHeight - st.T - 24) { return; }
			var r = col.getBoundingClientRect();
			st.C = r.top + window.scrollY; st.CH = r.height;
			st.on = true; tocBox.style.position = 'relative';
			st.o = Math.max(0, Math.min(st.CH - st.h, window.scrollY + st.T - st.C)); st.y = window.scrollY;
			tocBox.style.top = st.o + 'px';
		};
		var frame = function () {
			raf = 0;
			if (!st.on) { return; }
			var y = window.scrollY, o = st.o, vh = window.innerHeight;
			if (y > st.y) { if (st.C + o + st.h < y + vh - 24) { o = y + vh - 24 - st.h - st.C; } }
			else if (y < st.y) { if (st.C + o > y + st.T) { o = y + st.T - st.C; } }
			st.y = y;
			o = Math.max(0, Math.min(st.CH - st.h, o));
			if (o !== st.o) { st.o = o; tocBox.style.top = o + 'px'; }
		};
		var remeasure = function () { measure(); frame(); };
		window.addEventListener('scroll', function () { if (!raf) { raf = requestAnimationFrame(frame); } }, { passive: true });
		window.addEventListener('resize', remeasure, { passive: true });
		window.addEventListener('load', remeasure);
		if ('ResizeObserver' in window) { new ResizeObserver(function () { if (!raf) { raf = requestAnimationFrame(remeasure); } }).observe(col); }
		measure();
	}
	if (tocLinks.length && 'IntersectionObserver' in window) {
		var map = {};
		tocLinks.forEach(function (a) { map[decodeURIComponent(a.hash.slice(1))] = a; });
		var tio = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting && map[en.target.id]) {
					tocLinks.forEach(function (a) { a.removeAttribute('aria-current'); });
					map[en.target.id].setAttribute('aria-current', 'true');
				}
			});
		}, { rootMargin: '0px 0px -70% 0px' });
		Object.keys(map).forEach(function (id) { var h = document.getElementById(id); if (h) { tio.observe(h); } });
	}

	/* 1.9.0: copy-link button and collapsed TOC on phones. */
	document.querySelectorAll('[data-mx-copy]').forEach(function (b) {
		b.addEventListener('click', function () {
			var u = b.getAttribute('data-mx-copy'), t = b.textContent;
			if (navigator.clipboard) { navigator.clipboard.writeText(u).then(function () { b.textContent = b.getAttribute('data-done') || t; setTimeout(function () { b.textContent = t; }, 2000); }); }
			else { window.prompt('', u); }
		});
	});
	var tocD = document.querySelector('.mx-toc details');
	if (tocD && window.matchMedia('(max-width: 900px)').matches) { tocD.removeAttribute('open'); }
})();
