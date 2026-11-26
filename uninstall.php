<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
delete_option( 'fisa_cookies' );
delete_site_transient( 'fisa_cookies_gh_version' );
