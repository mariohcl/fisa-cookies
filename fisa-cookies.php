<?php
/**
 * Plugin Name:       FISA · Cookies
 * Description:       Aviso y gestión de consentimiento de cookies (Ley 21.719) con Google Consent Mode v2. Usa CookieConsent v3 de Orest Bida (MIT).
 * Version:           1.0.3
 * Author:            FISA
 * Text Domain:       fisa-cookies
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Plugin URI:        https://github.com/mariohcl/fisa-cookies
 */

defined( 'ABSPATH' ) || exit;

define( 'FISA_COOKIES_VERSION', '1.0.3' );
define( 'FISA_COOKIES_FILE', __FILE__ );

// Actualizaciones desde GitHub (se cargan siempre, aunque el aviso esté desactivado).
require_once __DIR__ . '/inc/actualizaciones.php';

// Recuperación: define( 'FISA_COOKIES_DISABLE', true ); en wp-config.php
if ( defined( 'FISA_COOKIES_DISABLE' ) && FISA_COOKIES_DISABLE ) {
	return;
}

define( 'FISA_COOKIES_URL', plugin_dir_url( __FILE__ ) );
define( 'FISA_COOKIES_OPTION', 'fisa_cookies' );
define( 'FISA_COOKIES_COOKIE', 'fisa_cookies' );

/* -------------------------------------------------------------------------
 * Opciones
 * ---------------------------------------------------------------------- */

function fisa_cookies_defaults() {
	return array(
		'enabled'      => 1,
		'layout'       => 'box-left',
		'color'        => '#1f2937',
		'color_text'   => '#ffffff',
		'privacy_page' => 0,
		'analytics'    => 1,
		'marketing'    => 1,
		'consent_mode' => 1,
		'ytnocookie'   => 1,
		'revision'     => 1,
	);
}

function fisa_cookies_opt( $key = null ) {
	$opts = wp_parse_args( (array) get_option( FISA_COOKIES_OPTION, array() ), fisa_cookies_defaults() );
	return null === $key ? $opts : ( isset( $opts[ $key ] ) ? $opts[ $key ] : null );
}

function fisa_cookies_lang() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	if ( ! $lang ) {
		$lang = substr( get_locale(), 0, 2 );
	}
	return 'es' === $lang ? 'es' : 'en';
}

function fisa_cookies_privacy_url() {
	$id = (int) fisa_cookies_opt( 'privacy_page' );
	if ( ! $id ) {
		return '';
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( $id );
		if ( $tr ) {
			$id = $tr;
		}
	}
	return 'publish' === get_post_status( $id ) ? get_permalink( $id ) : '';
}

/* -------------------------------------------------------------------------
 * Textos ES / EN  (modificables con el filtro 'fisa_cookies_texts')
 * ---------------------------------------------------------------------- */

function fisa_cookies_texts( $lang ) {
	$url  = fisa_cookies_privacy_url();
	$opts = fisa_cookies_opt();

	if ( 'es' === $lang ) {
		$t = array(
			'title'         => 'Usamos cookies',
			'desc'          => 'Usamos cookies propias y de terceros para que el sitio funcione, medir las visitas y, si lo permites, mostrarte publicidad relacionada. Puedes aceptarlas, rechazarlas o elegir cuáles permitir.',
			'accept_all'    => 'Aceptar todas',
			'reject'        => 'Rechazar',
			'reject_all'    => 'Rechazar todas',
			'configure'     => 'Configurar',
			'save'          => 'Guardar preferencias',
			'close'         => 'Cerrar',
			'privacy'       => 'Política de privacidad',
			'pm_title'      => 'Preferencias de cookies',
			'intro_title'   => 'Uso de cookies',
			'intro_desc'    => 'Las cookies son pequeños archivos que el sitio guarda en tu navegador. Puedes cambiar tu elección en cualquier momento desde el enlace “Configurar cookies” al pie de página.',
			'nec_title'     => 'Necesarias',
			'nec_desc'      => 'Permiten que el sitio funcione: idioma, seguridad de los formularios y tu elección sobre cookies. No se pueden desactivar.',
			'ana_title'     => 'Analítica',
			'ana_desc'      => 'Nos ayudan a entender cómo se usa el sitio (páginas visitadas, duración de la visita) para mejorarlo. Por ejemplo, Google Analytics.',
			'mkt_title'     => 'Marketing',
			'mkt_desc'      => 'Permiten medir campañas y mostrarte publicidad relacionada en otras plataformas. Por ejemplo, Meta, Google Ads y LinkedIn.',
			'more_title'    => 'Más información',
			'more_desc'     => 'Si tienes dudas sobre el uso de tus datos, escríbenos a <a href="mailto:info@fisa.cl">info@fisa.cl</a>.',
			'more_desc_url' => 'Revisa nuestra <a href="%s">política de privacidad</a> o escríbenos a <a href="mailto:info@fisa.cl">info@fisa.cl</a>.',
			'link'          => 'Configurar cookies',
		);
	} else {
		$t = array(
			'title'         => 'We use cookies',
			'desc'          => 'We use our own and third-party cookies to make the site work, measure visits and, if you allow it, show you related advertising. You can accept them, reject them or choose which ones to allow.',
			'accept_all'    => 'Accept all',
			'reject'        => 'Reject',
			'reject_all'    => 'Reject all',
			'configure'     => 'Settings',
			'save'          => 'Save preferences',
			'close'         => 'Close',
			'privacy'       => 'Privacy policy',
			'pm_title'      => 'Cookie preferences',
			'intro_title'   => 'Cookie usage',
			'intro_desc'    => 'Cookies are small files the site stores in your browser. You can change your choice at any time from the “Cookie settings” link in the footer.',
			'nec_title'     => 'Necessary',
			'nec_desc'      => 'They make the site work: language, form security and your cookie choice. They cannot be disabled.',
			'ana_title'     => 'Analytics',
			'ana_desc'      => 'They help us understand how the site is used (pages visited, visit length) so we can improve it. For example, Google Analytics.',
			'mkt_title'     => 'Marketing',
			'mkt_desc'      => 'They let us measure campaigns and show you related advertising on other platforms. For example, Meta, Google Ads and LinkedIn.',
			'more_title'    => 'More information',
			'more_desc'     => 'If you have questions about how we use your data, write to us at <a href="mailto:info@fisa.cl">info@fisa.cl</a>.',
			'more_desc_url' => 'Read our <a href="%s">privacy policy</a> or write to us at <a href="mailto:info@fisa.cl">info@fisa.cl</a>.',
			'link'          => 'Cookie settings',
		);
	}

	$t = apply_filters( 'fisa_cookies_texts', $t, $lang );

	$sections = array(
		array( 'title' => $t['intro_title'], 'description' => $t['intro_desc'] ),
		array( 'title' => $t['nec_title'], 'description' => $t['nec_desc'], 'linkedCategory' => 'necessary' ),
	);
	if ( $opts['analytics'] ) {
		$sections[] = array( 'title' => $t['ana_title'], 'description' => $t['ana_desc'], 'linkedCategory' => 'analytics' );
	}
	if ( $opts['marketing'] ) {
		$sections[] = array( 'title' => $t['mkt_title'], 'description' => $t['mkt_desc'], 'linkedCategory' => 'marketing' );
	}
	$sections[] = array(
		'title'       => $t['more_title'],
		'description' => $url ? sprintf( $t['more_desc_url'], esc_url( $url ) ) : $t['more_desc'],
	);

	return array(
		'consentModal'     => array(
			'title'              => $t['title'],
			'description'        => $t['desc'],
			'acceptAllBtn'       => $t['accept_all'],
			'acceptNecessaryBtn' => $t['reject'],
			'showPreferencesBtn' => $t['configure'],
			'footer'             => $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $t['privacy'] ) . '</a>' : '',
		),
		'preferencesModal' => array(
			'title'              => $t['pm_title'],
			'acceptAllBtn'       => $t['accept_all'],
			'acceptNecessaryBtn' => $t['reject_all'],
			'savePreferencesBtn' => $t['save'],
			'closeIconLabel'     => $t['close'],
			'sections'           => $sections,
		),
		'_link'            => $t['link'],
	);
}

/* -------------------------------------------------------------------------
 * Frontend
 * ---------------------------------------------------------------------- */

function fisa_cookies_active() {
	return ! is_admin() && fisa_cookies_opt( 'enabled' ) && ! wp_doing_ajax();
}

// Consent Mode v2: valores por defecto lo antes posible, antes de GTM / gtag.
add_action( 'wp_head', function () {
	if ( ! fisa_cookies_active() || ! fisa_cookies_opt( 'consent_mode' ) ) {
		return;
	}
	$rev = (int) fisa_cookies_opt( 'revision' );
	$js  = "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}"
		. "(function(){var a=false,m=false;try{var c=document.cookie.match(/(?:^|;\\s*)" . FISA_COOKIES_COOKIE . "=([^;]*)/);"
		. "if(c){var d=JSON.parse(decodeURIComponent(c[1]));if(d&&d.revision===" . $rev . "&&d.categories){a=d.categories.indexOf('analytics')>-1;m=d.categories.indexOf('marketing')>-1;}}}catch(e){}"
		. "var g=function(v){return v?'granted':'denied';};"
		. "gtag('consent','default',{analytics_storage:g(a),ad_storage:g(m),ad_user_data:g(m),ad_personalization:g(m),functionality_storage:'granted',security_storage:'granted',wait_for_update:500});"
		. "gtag('set','ads_data_redaction',true);})();";
	wp_print_inline_script_tag( $js, array( 'id' => 'fisa-cookies-consent-default' ) );
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! fisa_cookies_active() ) {
		return;
	}
	$o    = fisa_cookies_opt();
	$lang = fisa_cookies_lang();
	$tr   = fisa_cookies_texts( $lang );
	unset( $tr['_link'] );

	wp_enqueue_style( 'fisa-cookies-lib', FISA_COOKIES_URL . 'assets/vendor/cookieconsent/cookieconsent.css', array(), '3.3.0-' . FISA_COOKIES_VERSION );
	wp_enqueue_style( 'fisa-cookies', FISA_COOKIES_URL . 'assets/fisa-cookies.css', array( 'fisa-cookies-lib' ), FISA_COOKIES_VERSION );
	wp_add_inline_style( 'fisa-cookies', sprintf(
		'#cc-main{--fisa-cc-primary:%1$s;--fisa-cc-primary-text:%2$s;}',
		sanitize_hex_color( $o['color'] ) ?: '#1f2937',
		sanitize_hex_color( $o['color_text'] ) ?: '#ffffff'
	) );

	wp_enqueue_script( 'fisa-cookies-lib', FISA_COOKIES_URL . 'assets/vendor/cookieconsent/cookieconsent.umd.js', array(), '3.3.0', true );
	wp_enqueue_script( 'fisa-cookies', FISA_COOKIES_URL . 'assets/fisa-cookies.js', array( 'fisa-cookies-lib' ), FISA_COOKIES_VERSION, true );
	wp_add_inline_script( 'fisa-cookies', 'window.FISA_COOKIES=' . wp_json_encode( array(
		'cookie'      => FISA_COOKIES_COOKIE,
		'revision'    => (int) $o['revision'],
		'layout'      => $o['layout'],
		'analytics'   => (bool) $o['analytics'],
		'marketing'   => (bool) $o['marketing'],
		'consentMode' => (bool) $o['consent_mode'],
		'lang'        => $lang,
		'translation' => $tr,
	) ) . ';', 'before' );
} );

// Link para reabrir las preferencias (footer, menús, etc.).
function fisa_cookies_link( $echo = true, $text = '' ) {
	$tr   = fisa_cookies_texts( fisa_cookies_lang() );
	$html = '<a href="#cookies" class="fisa-cookies-link" data-cc="show-preferencesModal">' . esc_html( $text ? $text : $tr['_link'] ) . '</a>';
	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $html;
}

add_shortcode( 'fisa_cookies_link', function ( $atts ) {
	$atts = shortcode_atts( array( 'texto' => '' ), $atts );
	return fisa_cookies_link( false, $atts['texto'] );
} );

// YouTube sin cookies en contenido y embeds de WordPress.
function fisa_cookies_ytnocookie( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, 'youtube.com/embed/' ) ) {
		return $html;
	}
	return preg_replace( '#(?:https?:)?//(?:www\.)?youtube\.com/embed/#i', 'https://www.youtube-nocookie.com/embed/', $html );
}

add_action( 'init', function () {
	if ( fisa_cookies_opt( 'ytnocookie' ) ) {
		add_filter( 'embed_oembed_html', 'fisa_cookies_ytnocookie', 20 );
		add_filter( 'the_content', 'fisa_cookies_ytnocookie', 20 );
	}
} );

/* -------------------------------------------------------------------------
 * Admin: Ajustes → Cookies
 * ---------------------------------------------------------------------- */

add_action( 'admin_init', function () {
	register_setting( 'fisa_cookies', FISA_COOKIES_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'fisa_cookies_sanitize',
	) );
} );

function fisa_cookies_sanitize( $in ) {
	$in  = (array) $in;
	$d   = fisa_cookies_defaults();
	$out = array();
	foreach ( array( 'enabled', 'analytics', 'marketing', 'consent_mode', 'ytnocookie' ) as $k ) {
		$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
	}
	$out['layout']       = in_array( $in['layout'] ?? '', array( 'box-left', 'box-right', 'bar' ), true ) ? $in['layout'] : $d['layout'];
	$out['color']        = sanitize_hex_color( $in['color'] ?? '' ) ?: $d['color'];
	$out['color_text']   = sanitize_hex_color( $in['color_text'] ?? '' ) ?: $d['color_text'];
	$out['privacy_page'] = absint( $in['privacy_page'] ?? 0 );
	$out['revision']     = max( 1, absint( $in['revision'] ?? 1 ) );
	return $out;
}

add_action( 'admin_menu', function () {
	add_options_page( 'FISA · Cookies', 'Cookies', 'manage_options', 'fisa-cookies', 'fisa_cookies_admin_page' );
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=fisa-cookies' ) ) . '">Ajustes</a>' );
	return $links;
} );

function fisa_cookies_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = fisa_cookies_opt();
	$n = FISA_COOKIES_OPTION;
	?>
	<div class="wrap">
		<h1>FISA · Cookies</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'fisa_cookies' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Aviso</th>
					<td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enabled]" value="1" <?php checked( $o['enabled'] ); ?>> Mostrar aviso de cookies</label></td>
				</tr>
				<tr>
					<th scope="row">Categorías opcionales</th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[analytics]" value="1" <?php checked( $o['analytics'] ); ?>> Analítica (Google Analytics, Clarity, Hotjar…)</label><br>
						<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[marketing]" value="1" <?php checked( $o['marketing'] ); ?>> Marketing (Meta Pixel, Google Ads, LinkedIn…)</label>
						<p class="description">Las necesarias siempre están activas. Desmarca las que el sitio no use.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Google Consent Mode v2</th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[consent_mode]" value="1" <?php checked( $o['consent_mode'] ); ?>> Enviar estado de consentimiento a GTM / gtag</label>
						<p class="description">El snippet de GTM o gtag.js del theme debe imprimirse <strong>después</strong> de <code>wp_head()</code>, o enganchado a <code>wp_head</code> con prioridad mayor a 1.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">YouTube</th>
					<td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[ytnocookie]" value="1" <?php checked( $o['ytnocookie'] ); ?>> Usar youtube-nocookie.com en el contenido y embeds</label></td>
				</tr>
				<tr>
					<th scope="row"><label for="fisa-cc-privacy">Política de privacidad</label></th>
					<td>
						<?php
						wp_dropdown_pages( array(
							'name'              => esc_attr( $n ) . '[privacy_page]',
							'id'                => 'fisa-cc-privacy',
							'selected'          => (int) $o['privacy_page'],
							'show_option_none'  => '— Ninguna —',
							'option_none_value' => 0,
							'lang'              => function_exists( 'pll_default_language' ) ? pll_default_language() : '',
						) );
						?>
						<p class="description">Página en el idioma principal. Con Polylang se usa automáticamente su traducción.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Posición</th>
					<td>
						<select name="<?php echo esc_attr( $n ); ?>[layout]">
							<option value="box-left" <?php selected( $o['layout'], 'box-left' ); ?>>Caja abajo a la izquierda</option>
							<option value="box-right" <?php selected( $o['layout'], 'box-right' ); ?>>Caja abajo a la derecha</option>
							<option value="bar" <?php selected( $o['layout'], 'bar' ); ?>>Barra inferior</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">Colores</th>
					<td>
						<label>Botón principal <input type="color" name="<?php echo esc_attr( $n ); ?>[color]" value="<?php echo esc_attr( $o['color'] ); ?>"></label>
						&nbsp;&nbsp;
						<label>Texto del botón <input type="color" name="<?php echo esc_attr( $n ); ?>[color_text]" value="<?php echo esc_attr( $o['color_text'] ); ?>"></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fisa-cc-rev">Revisión</label></th>
					<td>
						<input type="number" min="1" id="fisa-cc-rev" class="small-text" name="<?php echo esc_attr( $n ); ?>[revision]" value="<?php echo (int) $o['revision']; ?>">
						<p class="description">Súbela (ej. 1 → 2) si cambias las cookies o la política: el aviso vuelve a aparecer a todos los visitantes.</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<hr>
		<h2>Uso</h2>
		<p><strong>Link “Configurar cookies” en el footer</strong> (obligatorio, permite cambiar o revocar la elección):</p>
		<p><code>&lt;?php if ( function_exists( 'fisa_cookies_link' ) ) fisa_cookies_link(); ?&gt;</code> &nbsp;o el shortcode&nbsp; <code>[fisa_cookies_link]</code>. En un menú, basta un enlace personalizado con URL <code>#cookies</code>.</p>
		<p><strong>Bloquear un script hasta que acepten</strong> (Pixel, Clarity, etc. puestos directo en el theme):</p>
		<p><code>&lt;script type="text/plain" data-category="marketing"&gt; … &lt;/script&gt;</code> &nbsp;·&nbsp; categorías: <code>analytics</code>, <code>marketing</code></p>
		<p><strong>En GTM</strong>: las etiquetas de Google (GA4, Ads) respetan Consent Mode solas. Para las de terceros, en “Configuración de consentimiento” exige <code>analytics_storage</code> o <code>ad_storage</code>, o dispáralas con el evento personalizado <code>fisa_cookies_update</code>.</p>
		<p><strong>Recuperación</strong>: <code>define( 'FISA_COOKIES_DISABLE', true );</code> en wp-config.php.</p>
	</div>
	<?php
}
