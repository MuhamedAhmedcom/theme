/**
 * Contact form → Contact Form 7 REST feedback, with an accessible result modal.
 * Vanilla JS, loaded on the contact page only. Strings come from window.mxCf7
 * (rendered in the page language).
 */
(function () {
	'use strict';
	var cfg = window.mxCf7 || {};
	var t = cfg.i18n || {};
	var form = document.querySelector('form[data-mx-cf7]');
	if (!form || !cfg.endpoint || !window.fetch || !window.FormData) { return; }
	var live = form.querySelector('[data-mx-status]');
	var btn = form.querySelector('[type=submit]');
	var lastFocus = null;
	var dlg = null;

	function el(tag, cls, text) {
		var n = document.createElement(tag);
		if (cls) { n.className = cls; }
		if (text) { n.textContent = text; }
		return n;
	}

	function closeModal() {
		if (!dlg) { return; }
		dlg.classList.remove('is-open');
		document.removeEventListener('keydown', onKey, true);
		document.documentElement.classList.remove('mx-modal-lock');
		var d = dlg; dlg = null;
		setTimeout(function () { if (d.parentNode) { d.parentNode.removeChild(d); } }, 200);
		if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
	}

	function focusables() {
		return dlg ? Array.prototype.slice.call(dlg.querySelectorAll('a[href],button:not([disabled])')) : [];
	}

	function onKey(e) {
		if (!dlg) { return; }
		if (e.key === 'Escape') { e.preventDefault(); closeModal(); return; }
		if (e.key === 'Tab') {
			var f = focusables(); if (!f.length) { return; }
			var first = f[0], last = f[f.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		}
	}

	function openModal(kind, title, body) {
		closeModal();
		lastFocus = document.activeElement;
		dlg = el('div', 'mx-modal mx-modal--' + kind);
		var back = el('div', 'mx-modal__backdrop');
		back.addEventListener('click', closeModal);
		var box = el('div', 'mx-modal__box');
		box.setAttribute('role', kind === 'ok' ? 'dialog' : 'alertdialog');
		box.setAttribute('aria-modal', 'true');
		box.setAttribute('aria-labelledby', 'mx-modal-title');
		box.setAttribute('aria-describedby', 'mx-modal-body');
		var x = el('button', 'mx-modal__x');
		x.type = 'button'; x.setAttribute('aria-label', t.close || 'Close'); x.innerHTML = '<span aria-hidden="true">&times;</span>';
		x.addEventListener('click', closeModal);
		var icon = el('div', 'mx-modal__icon');
		icon.setAttribute('aria-hidden', 'true');
		icon.innerHTML = kind === 'ok'
			? '<svg viewBox="0 0 52 52" width="52" height="52"><circle cx="26" cy="26" r="24" fill="none" stroke="currentColor" stroke-width="3"/><path class="mx-modal__tick" d="M15 27l7 7 15-16" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
			: '<svg viewBox="0 0 52 52" width="52" height="52"><circle cx="26" cy="26" r="24" fill="none" stroke="currentColor" stroke-width="3"/><path d="M26 14v15M26 36v2" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/></svg>';
		var h = el('h2', 'mx-modal__title', title); h.id = 'mx-modal-title';
		var p = el('p', 'mx-modal__body', body); p.id = 'mx-modal-body';
		var acts = el('div', 'mx-modal__actions');
		if (kind !== 'ok' && cfg.email) {
			var a = el('a', 'mx-btn mx-btn--ghost', t.emailMe || cfg.email);
			a.href = 'mailto:' + cfg.email; acts.appendChild(a);
		}
		var ok = el('button', 'mx-btn mx-btn--primary', kind === 'ok' ? (t.done || 'OK') : (t.close || 'Close'));
		ok.type = 'button'; ok.addEventListener('click', closeModal); acts.appendChild(ok);
		box.appendChild(x); box.appendChild(icon); box.appendChild(h); box.appendChild(p); box.appendChild(acts);
		dlg.appendChild(back); dlg.appendChild(box);
		document.body.appendChild(dlg);
		document.documentElement.classList.add('mx-modal-lock');
		document.addEventListener('keydown', onKey, true);
		requestAnimationFrame(function () { if (dlg) { dlg.classList.add('is-open'); ok.focus(); } });
	}

	function status(cls, msg) {
		if (live) { live.className = 'mx-form__status' + (cls ? ' ' + cls : ''); live.textContent = msg || ''; }
	}

	function busy(on) {
		if (!btn) { return; }
		btn.disabled = on;
		btn.setAttribute('aria-busy', on ? 'true' : 'false');
		btn.classList.toggle('is-busy', on);
	}

	function clearErrors() {
		form.querySelectorAll('[aria-invalid]').forEach(function (n) { n.removeAttribute('aria-invalid'); });
		form.querySelectorAll('.mx-field__err').forEach(function (n) { n.textContent = ''; });
	}

	function readJson(r) {
		return r.text().then(function (s) {
			s = String(s || '').replace(/^\uFEFF/, '').trim();
			try { return JSON.parse(s); } catch (e) { throw new Error('non-json'); }
		});
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		clearErrors();
		var data = new FormData(form);
		var e164 = data.get('mx_phone_e164');
		if (e164) { data.set('mx_phone', e164); }
		['action', 'mx_nonce'].forEach(function (k) { data.delete(k); });
		busy(true);
		status('', t.sending);
		fetch(cfg.endpoint, { method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'application/json' } })
			.then(readJson)
			.then(function (res) {
				busy(false);
				var st = res && res.status;
				if (st === 'mail_sent') {
					form.reset();
					clearErrors();
					status('is-ok', t.okBody);
					openModal('ok', t.okTitle, t.okBody);
					return;
				}
				if (st === 'validation_failed' || st === 'acceptance_missing') {
					var first = null;
					(res.invalid_fields || []).forEach(function (f) {
						var name = f.field || '';
						var inp = form.querySelector('[name="' + name + '"]');
						var slot = document.getElementById('mx-err-' + name);
						if (inp) { inp.setAttribute('aria-invalid', 'true'); first = first || inp; }
						if (slot) { slot.textContent = (t.fields && t.fields[name]) || t.invalid; }
					});
					status('is-err', t.invalid);
					if (first) { first.focus(); }
					return;
				}
				var msg = st === 'spam' ? t.spam : (st === 'mail_failed' ? t.mailFailed : t.failed);
				status('is-err', msg);
				openModal('err', t.errTitle, msg);
			})
			.catch(function () {
				busy(false);
				status('is-err', t.failed);
				openModal('err', t.errTitle, t.failed);
			});
	}, true);
}());