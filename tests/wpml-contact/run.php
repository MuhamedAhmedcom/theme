<?php
/**
 * Isolated handler tests for contact language context.
 *
 * These stubs are not WPML and not WordPress. They fail the suite when
 * gettext follows only a WPML action, when a literal language is used as
 * the fallback, or when the client locale is trusted. Passing does not
 * prove a live WPML/WordPress request.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wp/' );
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

final class MahxStop extends RuntimeException {}

function mahx_contact_stop(): void {
	throw new MahxStop( 'stop' );
}

$mahx = array(
	'wpml'             => true,
	'locale'           => 'en_US',
	'locale_stack'     => array(),
	'wpml_stack'       => array(),
	'request_lang'     => 'ar',
	'default_lang'     => 'ar',
	'active'           => array(
		'ar' => 'ar',
		'en' => 'en_US',
		'fr' => 'fr_FR',
	),
	'catalog'          => 'en_US',
	'locale_switches'  => array(),
	'wpml_switches'    => array(),
	'loads'            => array(),
	'mail_body'        => '',
	'mail_catalog'     => '',
	'mail_wpml'        => '',
	'success_seen'     => '',
	'response'         => null,
	'transient'        => null,
	'warnings'         => array(),
);

function mahx_state(): array {
	global $mahx;
	return $mahx;
}

function mahx_wpml_current(): string {
	global $mahx;
	if ( ! $mahx['wpml_stack'] ) {
		return (string) $mahx['request_lang'];
	}
	return (string) $mahx['wpml_stack'][ count( $mahx['wpml_stack'] ) - 1 ];
}

function __( $text, $domain = 'default' ) {
	global $mahx;
	$catalog = $mahx['catalog'];
	$table   = array(
		'en_US' => array(
			'Your session expired. Please reload the page and send again.' => 'EN session',
			'Please confirm the consent box.'                               => 'EN consent',
			'Please check the highlighted fields.'                          => 'EN check',
			'Name'                                                          => 'EN Name',
		),
		'ar'    => array(
			'Your session expired. Please reload the page and send again.' => 'AR session',
			'Please confirm the consent box.'                               => 'AR consent',
			'Please check the highlighted fields.'                          => 'AR check',
			'Name'                                                          => 'AR Name',
		),
		'fr_FR' => array(
			'Your session expired. Please reload the page and send again.' => 'FR session',
			'Please confirm the consent box.'                               => 'FR consent',
			'Please check the highlighted fields.'                          => 'FR check',
			'Name'                                                          => 'FR Name',
		),
	);
	if ( isset( $table[ $catalog ][ $text ] ) ) {
		return $table[ $catalog ][ $text ];
	}
	return $text;
}

function esc_html__( $text, $domain = 'default' ) {
	return __( $text, $domain );
}

function has_filter( $hook ) {
	global $mahx;
	if ( 'wpml_language_is_active' === $hook ) {
		return $mahx['wpml'] ? 10 : false;
	}
	return false;
}

function apply_filters( $hook, $value, ...$args ) {
	global $mahx;
	if ( ! $mahx['wpml'] ) {
		return $value;
	}
	if ( 'wpml_default_language' === $hook ) {
		return $mahx['default_lang'];
	}
	if ( 'wpml_current_language' === $hook ) {
		return mahx_wpml_current();
	}
	if ( 'wpml_language_is_active' === $hook ) {
		$code = isset( $args[0] ) ? (string) $args[0] : '';
		return isset( $mahx['active'][ $code ] );
	}
	return $value;
}

function do_action( $hook, ...$args ): void {
	global $mahx;
	if ( 'wpml_switch_language' !== $hook ) {
		return;
	}
	$code = array_key_exists( 0, $args ) ? $args[0] : null;
	$mahx['wpml_switches'][] = $code;
	if ( null === $code ) {
		array_pop( $mahx['wpml_stack'] );
		return;
	}
	if ( isset( $mahx['active'][ (string) $code ] ) ) {
		$mahx['wpml_stack'][] = (string) $code;
	}
}

function get_locale() {
	global $mahx;
	return $mahx['locale'];
}

function switch_to_locale( $locale ) {
	global $mahx;
	$mahx['locale_switches'][] = $locale;
	if ( $locale === $mahx['locale'] ) {
		return false;
	}
	$mahx['locale_stack'][] = $mahx['locale'];
	$mahx['locale']         = $locale;
	return true;
}

function restore_previous_locale() {
	global $mahx;
	if ( $mahx['locale_stack'] ) {
		$mahx['locale'] = array_pop( $mahx['locale_stack'] );
	}
	return $mahx['locale'];
}

function unload_textdomain( $domain ) {
	return true;
}

function load_theme_textdomain( $domain, $path ) {
	global $mahx;
	$mahx['catalog'] = $mahx['locale'];
	$mahx['loads'][] = $mahx['locale'];
	return true;
}

function get_stylesheet_directory() {
	return '/theme';
}

function sanitize_key( $key ) {
	$key = strtolower( (string) $key );
	return preg_replace( '/[^a-z0-9_\-]/', '', $key );
}

function wp_unslash( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'wp_unslash', $value );
	}
	if ( ! is_string( $value ) ) {
		throw new RuntimeException( 'wp_unslash received ' . gettype( $value ) );
	}
	return stripslashes( $value );
}

function sanitize_text_field( $text ) {
	return is_string( $text ) ? trim( $text ) : '';
}

function sanitize_email( $email ) {
	return is_string( $email ) ? trim( $email ) : '';
}

function sanitize_textarea_field( $text ) {
	return is_string( $text ) ? trim( $text ) : '';
}

function esc_url_raw( $url ) {
	return is_string( $url ) ? $url : '';
}

function esc_url( $url ) {
	return esc_url_raw( $url );
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( (string) $url, $component );
}

function home_url( $path = '' ) {
	return 'https://muhamedahmed.com' . $path;
}

function wp_get_referer() {
	return 'https://muhamedahmed.com/fr/contact/';
}

function wp_verify_nonce( $nonce, $action ) {
	return 'valid' === $nonce && 'mahx_contact' === $action;
}

function absint( $value ) {
	return abs( (int) $value );
}

function is_email( $email ) {
	return is_string( $email ) && false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
}

function wp_hash( $data ) {
	return md5( (string) $data );
}

function get_transient( $key ) {
	return 0;
}

function set_transient( $key, $value, $expiration ) {
	global $mahx;
	$mahx['transient'] = $value;
	return true;
}

function get_option( $key, $default = false ) {
	return 'owner@example.com';
}

function wp_generate_password( $length = 12, $special = true ) {
	return 'transientkey';
}

function add_query_arg( ...$args ) {
	return 'https://muhamedahmed.com/fr/contact/?mx=1';
}

function remove_query_arg( $key, $url ) {
	return (string) $url;
}

function wp_safe_redirect( $url ) {
	global $mahx;
	$mahx['response'] = array( 'redirect', $url );
	return true;
}

function wp_send_json_error( $data ) {
	global $mahx;
	$mahx['response'] = array( 'error', $data, 'locale' => $mahx['locale'], 'catalog' => $mahx['catalog'], 'wpml' => mahx_wpml_current() );
	throw new MahxStop( 'json-error' );
}

function wp_send_json_success( $data ) {
	global $mahx;
	$mahx['response'] = array( 'success', $data, 'locale' => $mahx['locale'], 'catalog' => $mahx['catalog'], 'wpml' => mahx_wpml_current() );
	throw new MahxStop( 'json-success' );
}

function wp_mail( $to, $subject, $body, $headers = array() ) {
	global $mahx;
	$mahx['mail_body']    = (string) $body;
	$mahx['mail_catalog'] = $mahx['catalog'];
	$mahx['mail_wpml']    = mahx_wpml_current();
	return true;
}

function wp_specialchars_decode( $text, $quote_style = ENT_NOQUOTES ) {
	return (string) $text;
}

function wp_insert_post( $args ) {
	return 1;
}

function wp_remote_post( $url, $args = array() ) {
	throw new RuntimeException( 'network post attempted' );
}

function is_wp_error( $value ) {
	return false;
}

function wp_remote_retrieve_body( $response ) {
	return '';
}

function add_action( $hook, $callback, $priority = 10, $args = 1 ) {
	return true;
}

function mahx_opt( $key ) {
	global $mahx;
	if ( 'cf_success' === $key ) {
		$mahx['success_seen'] = mahx_wpml_current();
		$map                  = array(
			'ar' => 'AR success',
			'en' => 'EN success',
			'fr' => 'FR success',
		);
		$lang = mahx_wpml_current();
		return $map[ $lang ] ?? 'UNTRANSLATED success';
	}
	$flags = array(
		'cf_min_secs'  => 0,
		'cf_rate'      => 5,
		'cf_consent'   => 1,
		'cf_to'        => 'owner@example.com',
		'cf_subject'   => 'Enquiry',
		'cf_save'      => 0,
		'cf_phone'     => 0,
		'cf_service'   => 0,
		'cf_budget'    => 0,
		'cf_ts_site'   => '',
		'cf_ts_secret' => '',
	);
	return $flags[ $key ] ?? '';
}

class Mahx_SitePress {
	public function get_locale( $code ) {
		global $mahx;
		return $mahx['active'][ $code ] ?? '';
	}
}

$sitepress = new Mahx_SitePress();

require dirname( __DIR__, 2 ) . '/theme-src/inc/contact.php';

function mahx_reset( array $over = array() ): void {
	global $mahx, $sitepress;
	$mahx = array_merge(
		$mahx,
		array(
			'wpml'            => true,
			'locale'          => 'en_US',
			'locale_stack'    => array(),
			'wpml_stack'      => array(),
			'request_lang'    => 'ar',
			'default_lang'    => 'ar',
			'catalog'         => 'en_US',
			'locale_switches' => array(),
			'wpml_switches'   => array(),
			'loads'           => array(),
			'mail_body'       => '',
			'mail_catalog'    => '',
			'mail_wpml'       => '',
			'success_seen'    => '',
			'response'        => null,
			'transient'       => null,
			'warnings'        => array(),
		),
		$over
	);
	if ( array_key_exists( 'catalog', $over ) ) {
		$mahx['catalog'] = $over['catalog'];
	}
	$sitepress = $mahx['wpml'] ? new Mahx_SitePress() : null;
	$_POST     = array();
	$_SERVER   = array();
}

function mahx_run(): void {
	try {
		mahx_contact_submit();
	} catch ( MahxStop $stop ) {
		return;
	}
}

function mahx_base_post( array $extra = array() ): void {
	$_POST = array_merge(
		array(
			'action'     => 'mahx_contact',
			'mx_ajax'    => '1',
			'mx_nonce'   => 'valid',
			'mx_t'       => (string) time(),
			'mx_page'    => 'https://muhamedahmed.com/fr/contact/',
			'mx_name'    => 'Ada Lovelace',
			'mx_email'   => 'ada@example.com',
			'mx_message' => 'This message is long enough.',
			'mx_consent' => '1',
			'mx_lang'    => 'fr',
		),
		$extra
	);
}

function mahx_assert( bool $cond, string $message ): void {
	if ( ! $cond ) {
		fwrite( STDERR, "FAIL: $message\n" );
		exit( 1 );
	}
	echo "ok  $message\n";
}

function mahx_function_source( string $name ): string {
	$fn    = new ReflectionFunction( $name );
	$file  = file( $fn->getFileName() );
	$lines = array_slice( $file, $fn->getStartLine() - 1, $fn->getEndLine() - $fn->getStartLine() + 1 );
	return implode( '', $lines );
}

set_error_handler(
	static function ( int $severity, string $message ): bool {
		global $mahx;
		$mahx['warnings'][] = $message;
		throw new ErrorException( $message, 0, $severity );
	}
);

$watched = array(
	'mahx_contact_posted_language',
	'mahx_contact_wpml_present',
	'mahx_contact_language_is_active',
	'mahx_contact_resolve_language',
	'mahx_contact_locale_for_language',
	'mahx_contact_reload_theme_translations',
	'mahx_contact_begin_language',
	'mahx_contact_end_language',
	'mahx_contact_submit',
);
$source = '';
foreach ( $watched as $name ) {
	$source .= mahx_function_source( $name );
}
mahx_assert( ! preg_match( "/['\"](?:en|ar|fr|de|en_US|fr_FR|ar_EG)['\"]/", $source ), 'language functions contain no literal language or locale' );
mahx_assert( ! preg_match( "/apply_filters\\(\\s*'wpml_active_languages'/", $source ), 'admin-post path does not call wpml_active_languages' );
mahx_assert( false !== strpos( $source, "do_action( 'wpml_switch_language', null )" ), 'WPML scope closes with null' );
mahx_assert( false !== strpos( $source, 'switch_to_locale' ), 'WordPress locale is switched explicitly' );
mahx_assert( false !== strpos( $source, 'load_theme_textdomain' ), 'theme catalog is reloaded' );

$page = file_get_contents( dirname( __DIR__, 2 ) . '/theme-src/page-contact.php' );
mahx_assert( false !== strpos( $page, "apply_filters( 'wpml_current_language', null )" ), 'contact page reads the current WPML language' );
mahx_assert( ! preg_match( '/name="mx_lang"[^>]*value="(?:en|ar|fr)"/', $page ), 'contact page does not hardcode mx_lang' );

$cf7 = file_get_contents( dirname( __DIR__, 2 ) . '/theme-src/inc/cf7.php' );
mahx_assert( ! preg_match( "/mx_lang'\\s*=>\\s*'ar'/", $cf7 ), 'CF7 hidden language is not a fixed code' );

$home = file_get_contents( dirname( __DIR__, 2 ) . '/theme-src/template-parts/home/contact.php' );
mahx_assert( false === strpos( $home, '<form' ), 'homepage contact template has no form' );

echo "\n-- runtime cases (mocked WPML/gettext, not a live runtime) --\n";

mahx_reset();
mahx_base_post( array( 'mx_nonce' => 'nope' ) );
mahx_run();
mahx_assert( 'EN session' !== ( $mahx['response'][1]['message'] ?? '' ), 'user locale en_US is not the nonce error' );
mahx_assert( 'AR session' !== ( $mahx['response'][1]['message'] ?? '' ), 'site language ar is not the nonce error' );
mahx_assert( 'FR session' === ( $mahx['response'][1]['message'] ?? '' ), 'early nonce error uses form locale fr_FR' );
mahx_assert( 'en_US' === $mahx['response']['locale'] && 'en_US' === $mahx['response']['catalog'] && 'ar' === $mahx['response']['wpml'], 'nonce exit restored user locale, catalog, and WPML language' );
mahx_assert( array( 'fr_FR', 'en_US' ) === $mahx['loads'], 'catalog reloaded for the form locale and again on restore' );
mahx_assert( array( 'fr', null ) === $mahx['wpml_switches'], 'WPML opened fr and closed with null' );
mahx_assert( array( 'fr_FR' ) === $mahx['locale_switches'], 'switch_to_locale received the mapped locale, not the language code' );
mahx_assert( 'mahx_contact' === ( $_POST['action'] ?? '' ), 'WordPress action field is still mahx_contact' );

mahx_reset( array( 'locale' => 'ar', 'catalog' => 'ar', 'request_lang' => 'ar' ) );
mahx_base_post( array( 'mx_nonce' => 'nope', 'mx_ajax' => '' ) );
unset( $_POST['mx_ajax'] );
mahx_run();
mahx_assert( 'redirect' === ( $mahx['response'][0] ?? '' ), 'no-JS nonce failure redirects' );
mahx_assert( 'FR session' === ( is_array( $mahx['transient'] ) ? ( $mahx['transient']['message'] ?? '' ) : '' ), 'no-JS nonce message was translated before restore' );
mahx_assert( 'ar' === $mahx['locale'] && 'ar' === $mahx['catalog'] && 'ar' === mahx_wpml_current(), 'no-JS exit restored the guest request locale' );
mahx_assert( in_array( 'fr_FR', $mahx['loads'], true ), 'guest request still loaded the form catalog before the error' );

mahx_reset();
mahx_base_post( array( 'mx_consent' => '' ) );
mahx_run();
mahx_assert( 'FR consent' === ( $mahx['response'][1]['errors']['mx_consent'] ?? '' ), 'consent error is French while user locale is en_US and site default is ar' );
mahx_assert( 'FR check' === ( $mahx['response'][1]['message'] ?? '' ), 'general validation error is French' );
mahx_assert( 'en_US' === $mahx['response']['locale'] && 'ar' === $mahx['response']['wpml'], 'validation exit restored user locale and site WPML language' );

mahx_reset();
mahx_base_post();
mahx_run();
mahx_assert( 'FR success' === ( $mahx['response'][1]['message'] ?? '' ), 'success text was read in the form WPML language' );
mahx_assert( 'fr' === $mahx['success_seen'], 'cf_success saw WPML language fr, not the user or site language' );
mahx_assert( false !== strpos( $mahx['mail_body'], 'FR Name' ), 'mail labels used the form gettext catalog' );
mahx_assert( 'fr_FR' === $mahx['mail_catalog'] && 'fr' === $mahx['mail_wpml'], 'mail ran while both locale and WPML context were the form' );
mahx_assert( 'en_US' === $mahx['response']['locale'] && 'ar' === $mahx['response']['wpml'], 'success exit restored both contexts' );
mahx_assert( 'EN Name' === __( 'Name' ), 'gettext after the request is the user catalog again' );

mahx_reset();
unset( $_POST['mx_lang'] );
mahx_base_post();
unset( $_POST['mx_lang'] );
$_POST['mx_consent'] = '';
mahx_run();
mahx_assert( 'AR consent' === ( $mahx['response'][1]['errors']['mx_consent'] ?? '' ), 'missing language falls back to the site default locale' );
mahx_assert( array( 'ar' ) === $mahx['locale_switches'], 'missing language does not invent en_US' );

mahx_reset();
mahx_base_post( array( 'mx_lang' => 'de', 'mx_consent' => '' ) );
mahx_run();
mahx_assert( 'AR consent' === ( $mahx['response'][1]['errors']['mx_consent'] ?? '' ), 'disabled language falls back to the site default' );
mahx_assert( ! in_array( 'de', $mahx['wpml_switches'], true ), 'disabled language is not switched' );

mahx_reset();
mahx_base_post( array( 'mx_consent' => '' ) );
$_POST['mx_lang'] = array( 'fr' );
try {
	mahx_run();
	mahx_assert( array() === $mahx['warnings'], 'malformed language array produced no warning' );
} catch ( ErrorException $e ) {
	mahx_assert( false, 'malformed language array raised ' . $e->getMessage() );
}
mahx_assert( 'AR consent' === ( $mahx['response'][1]['errors']['mx_consent'] ?? '' ), 'malformed language uses the site default' );

mahx_reset();
mahx_base_post( array( 'mx_lang' => 'fr_FR', 'mx_consent' => '' ) );
mahx_run();
mahx_assert( array( 'ar' ) === $mahx['locale_switches'], 'client locale string is not passed to switch_to_locale' );
mahx_assert( 'AR consent' === ( $mahx['response'][1]['errors']['mx_consent'] ?? '' ), 'unrecognized code falls back through the site default' );

mahx_reset( array( 'wpml' => false, 'locale' => 'en_US', 'catalog' => 'en_US', 'request_lang' => '' ) );
mahx_base_post( array( 'mx_nonce' => 'nope' ) );
mahx_run();
mahx_assert( 'EN session' === ( $mahx['response'][1]['message'] ?? '' ), 'without WPML the existing WordPress locale is kept' );
mahx_assert( array() === $mahx['locale_switches'] && array() === $mahx['wpml_switches'], 'without WPML nothing is switched' );
mahx_assert( 'en_US' === $mahx['locale'] && 'en_US' === $mahx['catalog'], 'without WPML catalog and locale stay put' );

echo "\nMocked cases passed. This does not execute WPML or WordPress.\n";
