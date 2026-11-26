/* FISA · Cookies — inicialización de CookieConsent v3 */
(function () {
	'use strict';

	var C = window.FISA_COOKIES;
	var CC = window.CookieConsent;
	if (!C || !CC) return;

	window.dataLayer = window.dataLayer || [];
	function gtag() { window.dataLayer.push(arguments); }

	var categories = {
		necessary: { enabled: true, readOnly: true }
	};

	if (C.analytics) {
		categories.analytics = {
			autoClear: {
				cookies: [
					{ name: /^_ga/ },
					{ name: /^_gid/ },
					{ name: /^_gat/ },
					{ name: /^_clck/ },
					{ name: /^_clsk/ },
					{ name: /^_hj/ }
				]
			}
		};
	}

	if (C.marketing) {
		categories.marketing = {
			autoClear: {
				cookies: [
					{ name: /^_fbp/ },
					{ name: /^_fbc/ },
					{ name: /^_gcl/ },
					{ name: /^_uet/ },
					{ name: /^_tt_/ },
					{ name: /^_ttp/ }
				]
			}
		};
	}

	function sync() {
		var a = CC.acceptedCategory('analytics');
		var m = CC.acceptedCategory('marketing');

		if (C.consentMode) {
			gtag('consent', 'update', {
				analytics_storage: a ? 'granted' : 'denied',
				ad_storage: m ? 'granted' : 'denied',
				ad_user_data: m ? 'granted' : 'denied',
				ad_personalization: m ? 'granted' : 'denied'
			});
		}

		window.dataLayer.push({
			event: 'fisa_cookies_update',
			fisa_cookies_analytics: a,
			fisa_cookies_marketing: m
		});
	}

	var layouts = {
		'box-left': { layout: 'box', position: 'bottom left' },
		'box-right': { layout: 'box', position: 'bottom right' },
		'bar': { layout: 'bar inline', position: 'bottom' }
	};
	var cm = layouts[C.layout] || layouts['box-left'];
	cm.equalWeightButtons = true;
	cm.flipButtons = false;

	var translations = {};
	translations[C.lang] = C.translation;

	CC.run({
		revision: C.revision,
		cookie: {
			name: C.cookie,
			expiresAfterDays: 182
		},
		guiOptions: {
			consentModal: cm,
			preferencesModal: {
				layout: 'box',
				equalWeightButtons: true,
				flipButtons: false
			}
		},
		categories: categories,
		language: {
			default: C.lang,
			translations: translations
		},
		onConsent: sync,
		onChange: sync
	});

	// Links con href="#cookies" (menús de WordPress, footers armados a mano).
	document.addEventListener('click', function (e) {
		var el = e.target.closest ? e.target.closest('a[href="#cookies"]') : null;
		if (!el || el.hasAttribute('data-cc')) return;
		e.preventDefault();
		CC.showPreferences();
	});
})();
