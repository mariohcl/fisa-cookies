<?php
/**
 * Actualizaciones del plugin desde GitHub (sin plugins extra ni token).
 *
 * WordPress compara la versión instalada (Version: en fisa-cookies.php) con la
 * del fisa-cookies.php de la rama main del repositorio público. Si la de GitHub
 * es mayor, muestra la actualización en Escritorio → Actualizaciones y en
 * Plugins, y al actualizar descarga el zip de main.
 *
 * @package FISA_Cookies
 */

defined( 'ABSPATH' ) || exit;

define( 'FISA_COOKIES_GH_REPO', 'mariohcl/fisa-cookies' );
define( 'FISA_COOKIES_GH_BRANCH', 'main' );

/**
 * Archivo principal del plugin (carpeta/archivo.php), p. ej. fisa-cookies/fisa-cookies.php.
 */
function fisa_cookies_gh_basename() {
	return plugin_basename( FISA_COOKIES_FILE );
}

/**
 * URL del zip de la rama main.
 */
function fisa_cookies_gh_package_url() {
	return 'https://github.com/' . FISA_COOKIES_GH_REPO . '/archive/refs/heads/' . FISA_COOKIES_GH_BRANCH . '.zip';
}

/**
 * Versión publicada en GitHub (Version: del fisa-cookies.php de main).
 * Se guarda 3 horas; "Vuelve a comprobarlo" en Actualizaciones la vuelve a pedir.
 *
 * @return string Versión o '' si no se pudo leer.
 */
function fisa_cookies_gh_remote_version() {
	$force  = isset( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cached = get_site_transient( 'fisa_cookies_gh_version' );
	if ( false !== $cached && ! $force ) {
		return $cached;
	}

	$url      = 'https://raw.githubusercontent.com/' . FISA_COOKIES_GH_REPO . '/' . FISA_COOKIES_GH_BRANCH . '/fisa-cookies.php';
	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 15,
			'headers' => array( 'User-Agent' => 'FISA-Cookies-WP-Plugin' ),
		)
	);

	$version = '';
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		if ( preg_match( '/^[ \t\/*#@]*Version:\s*(\S+)/mi', wp_remote_retrieve_body( $response ), $m ) ) {
			$version = trim( $m[1] );
		}
	} else {
		$code = is_wp_error( $response ) ? $response->get_error_code() : wp_remote_retrieve_response_code( $response );
		error_log( 'FISA COOKIES UPDATER: no se pudo leer la versión en GitHub (' . $code . ').' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	// Si falla, se reintenta en 30 minutos en vez de 3 horas.
	set_site_transient( 'fisa_cookies_gh_version', $version, $version ? 3 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS );

	return $version;
}

/**
 * Agrega la actualización al listado de WordPress.
 */
function fisa_cookies_gh_check_update( $transient ) {
	if ( empty( $transient ) || ! is_object( $transient ) ) {
		return $transient;
	}

	$basename = fisa_cookies_gh_basename();
	$local    = FISA_COOKIES_VERSION;
	$remote   = fisa_cookies_gh_remote_version();

	$item = (object) array(
		'id'           => 'github.com/' . FISA_COOKIES_GH_REPO,
		'slug'         => dirname( $basename ),
		'plugin'       => $basename,
		'new_version'  => $remote ? $remote : $local,
		'url'          => 'https://github.com/' . FISA_COOKIES_GH_REPO,
		'package'      => fisa_cookies_gh_package_url(),
		'requires'     => '5.8',
		'requires_php' => '7.4',
		'icons'        => array(),
		'banners'      => array(),
	);

	if ( $remote && version_compare( $remote, $local, '>' ) ) {
		$transient->response[ $basename ] = $item;
		unset( $transient->no_update[ $basename ] );
	} else {
		$transient->no_update[ $basename ] = $item;
		unset( $transient->response[ $basename ] );
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_plugins', 'fisa_cookies_gh_check_update' );

/**
 * Ventana "Ver detalles" del plugin (sin esto WordPress muestra "Plugin no encontrado").
 */
function fisa_cookies_gh_info( $result, $action, $args ) {
	if ( 'plugin_information' !== $action || empty( $args->slug ) || dirname( fisa_cookies_gh_basename() ) !== $args->slug ) {
		return $result;
	}

	$remote = fisa_cookies_gh_remote_version();
	$repo   = 'https://github.com/' . FISA_COOKIES_GH_REPO;

	return (object) array(
		'name'          => 'FISA · Cookies',
		'slug'          => $args->slug,
		'version'       => $remote ? $remote : FISA_COOKIES_VERSION,
		'author'        => 'FISA',
		'homepage'      => $repo,
		'requires'      => '5.8',
		'requires_php'  => '7.4',
		'download_link' => fisa_cookies_gh_package_url(),
		'sections'      => array(
			'description' => '<p>Aviso y gestión de consentimiento de cookies (Ley 21.719) con Google Consent Mode v2.</p>',
			'changelog'   => '<p>Historial de versiones en el <a href="' . esc_url( $repo . '#historial-de-versiones' ) . '" target="_blank" rel="noopener">README del repositorio</a>.</p>',
		),
	);
}
add_filter( 'plugins_api', 'fisa_cookies_gh_info', 20, 3 );

/**
 * El zip de GitHub trae la carpeta "fisa-cookies-main": se renombra a la
 * carpeta actual del plugin para que WordPress lo reemplace en su lugar.
 */
function fisa_cookies_gh_source_folder( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;

	$basename = fisa_cookies_gh_basename();
	if ( empty( $hook_extra['plugin'] ) || $basename !== $hook_extra['plugin'] ) {
		return $source;
	}

	$folder = dirname( $basename );
	if ( basename( untrailingslashit( $source ) ) === $folder ) {
		return $source;
	}

	$target = trailingslashit( $remote_source ) . $folder . '/';
	if ( $wp_filesystem && $wp_filesystem->move( $source, $target, true ) ) {
		return $target;
	}

	return new WP_Error( 'fisa_cookies_gh', 'No se pudo preparar la carpeta del plugin.' );
}
add_filter( 'upgrader_source_selection', 'fisa_cookies_gh_source_folder', 10, 4 );

/**
 * Después de actualizar se borra la versión guardada.
 */
function fisa_cookies_gh_after_update( $upgrader, $options ) {
	if ( isset( $options['type'] ) && 'plugin' === $options['type'] ) {
		delete_site_transient( 'fisa_cookies_gh_version' );
	}
}
add_action( 'upgrader_process_complete', 'fisa_cookies_gh_after_update', 10, 2 );
