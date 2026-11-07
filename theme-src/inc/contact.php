<?php
/**
 * Built-in contact form: validation, spam checks, wp_mail(), optional
 * private storage. Settings: Customize > Muhamed Ahmed > Contact form.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Cloudflare Turnstile is configured.
 *
 * @return bool
 */
function mahx_turnstile_on() {
	return '' !== trim( (string) mahx_opt( 'cf_ts_site' ) ) && '' !== trim( (string) mahx_opt( 'cf_ts_secret' ) );
}

/**
 * Budget choices.
 *
 * @return string[]
 */
function mahx_budgets() {
	return mahx_opt( 'cf_budget' ) ? mahx_lines( 'cf_budgets' ) : array();
}

/**
 * Service choices: id => title.
 *
 * @return array<int,string>
 */
function mahx_service_choices() {
	$out = array();
	if ( mahx_opt( 'cf_service' ) ) {
		foreach ( mahx_services() as $s ) {
			$out[ $s->ID ] = get_the_title( $s );
		}
	}
	return $out;
}

/**
 * The visitor's IP address: Cloudflare first, then the web server.
 *
 * The raw address is never stored; it is only hashed for the rate limit and
 * shown in the notification mail because the owner asked for it.
 *
 * @return string Valid IP, or '' when there is none we can trust.
 */
function mahx_request_ip() {
	$ip = isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) : '';
	if ( '' === $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * Where the visitor came from (the HTTP Referer header).
 *
 * @return string Sanitised URL, or '' when the browser sent none.
 */
function mahx_request_referrer() {
	$ref = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	return '' !== $ref ? $ref : '';
}

/**
 * The visitor's country as an ISO 2 code, when the edge knows it.
 *
 * Cloudflare sends CF-IPCountry. It is never forced: no header, an unknown
 * code or "XX" means no country at all, so the phone field asks the visitor
 * instead of guessing one.
 *
 * @return string Two-letter code, or ''.
 */
function mahx_geo_country() {
	if ( ! mahx_opt( 'cf_phone_geo' ) ) {
		return '';
	}
	$code = isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ) : '';
	if ( ! preg_match( '/^[A-Z]{2}$/', $code ) || 'XX' === $code ) {
		$code = '';
	}
	/**
	 * Filters the country code used to pre-select the phone field.
	 *
	 * @param string $code Two-letter country code, or '' for no default.
	 */
	return (string) apply_filters( 'mahx_phone_geo_country', $code );
}

/**
 * The language of the current page: "ar" or "en".
 *
 * @return string
 */
function mahx_contact_lang() {
	$lang = function_exists( 'mahx_content_lang' ) ? mahx_content_lang( get_queried_object_id() ) : 'en';
	return 'ar' === $lang ? 'ar' : 'en';
}

/**
 * Strings for the phone country picker.
 *
 * The picker is the only part of the form that is not a panel option, so the
 * Arabic page gets the Arabic wording here.
 *
 * @return array<string,string>
 */
function mahx_phone_i18n() {
	if ( 'ar' === mahx_contact_lang() ) {
		$i18n = array(
			'selectedCountryAriaLabel' => 'الدولة المختارة',
			'noCountrySelected'         => 'لم يتم اختيار دولة',
			'countryListAriaLabel'      => 'قائمة الدول',
			'searchPlaceholder'         => 'ابحث عن دولة',
			'zeroSearchResults'         => 'لا توجد نتائج',
			'oneSearchResult'           => 'نتيجة واحدة',
			'multipleSearchResults'     => '${count} نتائج',
		);
	} else {
		$i18n = array(
			'selectedCountryAriaLabel' => __( 'Selected country', 'muhamed-ahmed-astran' ),
			'noCountrySelected'         => __( 'No country selected', 'muhamed-ahmed-astran' ),
			'countryListAriaLabel'      => __( 'List of countries', 'muhamed-ahmed-astran' ),
			'searchPlaceholder'         => __( 'Search', 'muhamed-ahmed-astran' ),
			'zeroSearchResults'         => __( 'No results found', 'muhamed-ahmed-astran' ),
			'oneSearchResult'           => __( '1 result found', 'muhamed-ahmed-astran' ),
			'multipleSearchResults'     => __( '${count} results found', 'muhamed-ahmed-astran' ),
		);
	}
	return (array) apply_filters( 'mahx_phone_i18n', $i18n );
}

/**
 * The phone number that was sent, preferring the E.164 value the browser adds.
 *
 * @return string Sanitised number, or ''.
 */
function mahx_posted_phone() {
	if ( ! mahx_opt( 'cf_phone' ) ) {
		return '';
	}
	$e164 = isset( $_POST['mx_phone_e164'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['mx_phone_e164'] ) ) ) : '';
	if ( '' !== $e164 && preg_match( '/^\+[1-9]\d{6,14}$/', $e164 ) ) {
		return $e164;
	}
	return isset( $_POST['mx_phone'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['mx_phone'] ) ) ) : '';
}

/**
 * The page the form was sent from, checked against this site.
 *
 * A form posted from somewhere else is spam, so the URL has to be ours.
 *
 * @param string $posted Raw posted value.
 * @return string|false Sanitised URL, '' when none was sent, false when the host is not ours.
 */
function mahx_posted_page( $posted ) {
	$posted = trim( (string) $posted );
	if ( '' === $posted ) {
		return '';
	}
	$url = esc_url_raw( $posted );
	if ( '' === $url ) {
		return false;
	}
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$site = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	/* The www variant is the same site. */
	$host = preg_replace( '/^www\./', '', $host );
	$site = preg_replace( '/^www\./', '', $site );
	if ( '' === $host || '' === $site || $host !== $site ) {
		return false;
	}
	return $url;
}

/**
 * The notification body: the fields, then the visitor's message.
 *
 * The IP and the referrer are always listed, even when they are unknown, so
 * the owner can always tell where an enquiry came from.
 *
 * @param array $data Fields: name, email, phone, service, budget, page, ip, referer, message.
 * @return string
 */
function mahx_contact_body( array $data ) {
	$lines = array(
		__( 'Name', 'muhamed-ahmed-astran' )     => (string) ( $data['name'] ?? '' ),
		__( 'Email', 'muhamed-ahmed-astran' )    => (string) ( $data['email'] ?? '' ),
		__( 'Phone', 'muhamed-ahmed-astran' )    => (string) ( $data['phone'] ?? '' ),
		__( 'Service', 'muhamed-ahmed-astran' )  => (string) ( $data['service'] ?? '' ),
		__( 'Budget', 'muhamed-ahmed-astran' )   => (string) ( $data['budget'] ?? '' ),
		__( 'Page', 'muhamed-ahmed-astran' )     => (string) ( $data['page'] ?? '' ),
		__( 'IP', 'muhamed-ahmed-astran' )       => (string) ( $data['ip'] ?? '' ) ?: '(unknown)',
		__( 'Referrer', 'muhamed-ahmed-astran' ) => (string) ( $data['referer'] ?? '' ) ?: '(none)',
	);
	$body = '';
	foreach ( array_filter( $lines ) as $label => $value ) {
		$body .= $label . ': ' . $value . "\n";
	}
	$body .= "\n" . (string) ( $data['message'] ?? '' ) . "\n";
	return $body;
}

/**
 * Visitor IP hash for rate limiting (raw IP is never stored).
 *
 * @return string
 */
function mahx_visitor_key() {
	return 'mahx_cf_' . substr( wp_hash( mahx_request_ip() ), 0, 20 );
}

add_action(
	'init',
	static function () {
		register_post_type(
			'mx_enquiry',
			array(
				'labels'              => array(
					'name'          => __( 'Enquiries', 'muhamed-ahmed-astran' ),
					'singular_name' => __( 'Enquiry', 'muhamed-ahmed-astran' ),
				),
				'public'              => false,
				'show_ui'             => (bool) mahx_opt( 'cf_save' ),
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => array( 'title', 'editor' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);
	}
);

if ( ! function_exists( 'mahx_contact_stop' ) ) {
	/**
	 * Terminate admin-post handling.
	 *
	 * wp_send_json_* already ends the request in WordPress. The explicit stop
	 * covers the redirect path. Shutdown still runs, so language state is
	 * restored before this is called.
	 */
	function mahx_contact_stop() {
		exit;
	}
}

/**
 * Posted language code, or '' when the field is missing or not a string.
 *
 * Arrays and other types are rejected before unslash or sanitize.
 *
 * @return string
 */
function mahx_contact_posted_language() {
	if ( ! isset( $_POST['mx_lang'] ) || ! is_string( $_POST['mx_lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- language selector, checked against active languages.
		return '';
	}
	$code = sanitize_key( wp_unslash( $_POST['mx_lang'] ) );
	if ( '' === $code || strlen( $code ) > 20 ) {
		return '';
	}
	return $code;
}

/**
 * Whether WPML has registered its language API on this request.
 *
 * @return bool
 */
function mahx_contact_wpml_present() {
	return function_exists( 'has_filter' ) && false !== has_filter( 'wpml_language_is_active' );
}

/**
 * Whether a language code is one of the site's active WPML languages.
 *
 * wpml_language_is_active calls SitePress::is_active_language(), which reads
 * the active language list directly. It does not need the front-end `wp`
 * action. wpml_active_languages does, and admin-post.php never fires `wp`,
 * so that hook is not used here.
 *
 * @param string $code Language code.
 * @return bool
 */
function mahx_contact_language_is_active( $code ) {
	if ( '' === $code ) {
		return false;
	}
	return true === apply_filters( 'wpml_language_is_active', null, $code );
}

/**
 * Active submitted language, otherwise the site default, otherwise current.
 *
 * An empty result means "do not switch": WPML is absent, or neither the
 * submitted code nor the API fallbacks are active.
 *
 * @return string
 */
function mahx_contact_resolve_language() {
	if ( ! mahx_contact_wpml_present() ) {
		return '';
	}
	$requested = mahx_contact_posted_language();
	if ( mahx_contact_language_is_active( $requested ) ) {
		return $requested;
	}
	$candidates = array(
		apply_filters( 'wpml_default_language', null ),
		apply_filters( 'wpml_current_language', null ),
	);
	foreach ( $candidates as $candidate ) {
		if ( ! is_string( $candidate ) ) {
			continue;
		}
		$candidate = sanitize_key( $candidate );
		if ( mahx_contact_language_is_active( $candidate ) ) {
			return $candidate;
		}
	}
	return '';
}

/**
 * WordPress locale WPML has stored for a language code.
 *
 * The locale comes from SitePress::get_locale(), which applies the locale map
 * and icl_languages.default_locale. The client never supplies this value.
 *
 * @param string $code Active language code.
 * @return string Locale such as the value WPML stores, or '' when unknown.
 */
function mahx_contact_locale_for_language( $code ) {
	global $sitepress;
	$locale = '';
	if ( isset( $sitepress ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_locale' ) ) {
		$locale = $sitepress->get_locale( $code );
	}
	if ( ! is_string( $locale ) || ! preg_match( '/^[A-Za-z]{2,3}(?:[_-][A-Za-z0-9]+)*$/', $locale ) ) {
		return '';
	}
	return $locale;
}

/**
 * Reload this theme's catalog for whatever locale is current.
 *
 * switch_to_locale() changes the locale. The theme catalog loaded at
 * after_setup_theme stays in memory until it is unloaded and loaded again.
 */
function mahx_contact_reload_theme_translations() {
	if ( ! function_exists( 'unload_textdomain' ) || ! function_exists( 'load_theme_textdomain' ) || ! function_exists( 'get_stylesheet_directory' ) ) {
		return;
	}
	unload_textdomain( 'muhamed-ahmed-astran' );
	load_theme_textdomain( 'muhamed-ahmed-astran', get_stylesheet_directory() . '/languages' );
}

/**
 * Switch WPML and WordPress locale to the form language before any translation.
 *
 * On admin-post.php, WPML's locale callback keeps the logged-in user's admin
 * locale (is_admin() and not AJAX). wpml_switch_language changes WPML's
 * current language for options and string translation. switch_to_locale()
 * plus a theme catalog reload is what makes __() follow the form.
 *
 * @return array{wpml:bool,locale:bool}
 */
function mahx_contact_begin_language() {
	$state = array(
		'wpml'   => false,
		'locale' => false,
	);
	$code  = mahx_contact_resolve_language();
	if ( '' === $code ) {
		return $state;
	}
	do_action( 'wpml_switch_language', $code );
	$state['wpml'] = true;
	$locale        = mahx_contact_locale_for_language( $code );
	if ( '' !== $locale && function_exists( 'get_locale' ) && $locale !== get_locale() && function_exists( 'switch_to_locale' ) ) {
		if ( switch_to_locale( $locale ) ) {
			$state['locale'] = true;
		}
	}
	mahx_contact_reload_theme_translations();
	return $state;
}

/**
 * Close the WPML scope and undo the WordPress locale switch once.
 *
 * Passing null is the documented restore: WPML 4.x switch_lang(null) returns
 * to the language captured on the first switch, and WPML 5 closes the scope
 * that this request opened. A language code would open another scope.
 *
 * @param array{wpml:bool,locale:bool} $state Switch state.
 */
function mahx_contact_end_language( array &$state ) {
	$restore = ! empty( $state['locale'] ) || ! empty( $state['wpml'] );
	if ( ! empty( $state['locale'] ) && function_exists( 'restore_previous_locale' ) ) {
		restore_previous_locale();
	}
	if ( ! empty( $state['wpml'] ) ) {
		do_action( 'wpml_switch_language', null );
	}
	$state['locale'] = false;
	$state['wpml']   = false;
	if ( $restore ) {
		mahx_contact_reload_theme_translations();
	}
}

/**
 * Handle a submission (admin-post, JS or no-JS).
 */
function mahx_contact_submit() {
	$lang_state = mahx_contact_begin_language();

	$ajax   = ! empty( $_POST['mx_ajax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- only selects the response format.
	$back   = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$errors = array();
	$fail   = static function ( $message, $errors = array() ) use ( $ajax, $back, &$lang_state ) {
		mahx_contact_end_language( $lang_state );
		if ( $ajax ) {
			wp_send_json_error(
				array(
					'message' => $message,
					'errors'  => $errors,
				)
			);
			mahx_contact_stop();
		}
		$key = wp_generate_password( 12, false );
		set_transient(
			'mahx_cf_err_' . $key,
			array(
				'message' => $message,
				'errors'  => $errors,
				'old'     => array_map( 'sanitize_textarea_field', wp_unslash( array_intersect_key( $_POST, array_flip( array( 'mx_name', 'mx_email', 'mx_phone', 'mx_phone_e164', 'mx_service', 'mx_budget', 'mx_message' ) ) ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing -- echoed back to the same visitor, sanitized.
			),
			10 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( 'mx_cf', $key, remove_query_arg( array( 'mx_cf', 'mx_sent' ), $back ) ) . '#mx-form' );
		mahx_contact_stop();
	};

	if ( ! isset( $_POST['mx_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mx_nonce'] ) ), 'mahx_contact' ) ) {
		$fail( __( 'Your session expired. Please reload the page and send again.', 'muhamed-ahmed-astran' ) );
	}
	/* Honeypot and minimum fill time. */
	$started = isset( $_POST['mx_t'] ) ? absint( $_POST['mx_t'] ) : 0;
	if ( ! empty( $_POST['mx_website'] ) || ( time() - $started ) < (int) mahx_opt( 'cf_min_secs' ) ) {
		$fail( __( 'Please wait a moment and send again.', 'muhamed-ahmed-astran' ) );
	}
	$rkey = mahx_visitor_key();
	$hits = (int) get_transient( $rkey );
	if ( $hits >= max( 1, (int) mahx_opt( 'cf_rate' ) ) ) {
		$fail( __( 'Too many messages in a short time. Please try again later or email me directly.', 'muhamed-ahmed-astran' ) );
	}
	if ( mahx_turnstile_on() ) {
		$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
		$res   = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout' => 8,
				'body'    => array(
					'secret'   => (string) mahx_opt( 'cf_ts_secret' ),
					'response' => $token,
				),
			)
		);
		$ok    = ! is_wp_error( $res ) && ! empty( json_decode( (string) wp_remote_retrieve_body( $res ), true )['success'] );
		if ( ! $ok ) {
			$fail( __( 'The spam check failed. Please try again.', 'muhamed-ahmed-astran' ) );
		}
	}

	$name    = isset( $_POST['mx_name'] ) ? sanitize_text_field( wp_unslash( $_POST['mx_name'] ) ) : '';
	$email   = isset( $_POST['mx_email'] ) ? sanitize_email( wp_unslash( $_POST['mx_email'] ) ) : '';
	$phone   = mahx_posted_phone();
	$svc_id  = isset( $_POST['mx_service'] ) ? absint( $_POST['mx_service'] ) : 0;
	$budget  = isset( $_POST['mx_budget'] ) ? sanitize_text_field( wp_unslash( $_POST['mx_budget'] ) ) : '';
	$message = isset( $_POST['mx_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mx_message'] ) ) : '';
	$svcs    = mahx_service_choices();
	$service = isset( $svcs[ $svc_id ] ) ? $svcs[ $svc_id ] : '';
	$budget  = in_array( $budget, mahx_budgets(), true ) ? $budget : '';

	/* The form must be sent from this site: the hidden page URL has to match our host. */
	$page = mahx_posted_page( isset( $_POST['mx_page'] ) ? wp_unslash( $_POST['mx_page'] ) : '' );
	if ( false === $page ) {
		$fail( __( 'This form can only be sent from our own website. Please reload the page and send again.', 'muhamed-ahmed-astran' ) );
	}

	if ( '' === $name || mb_strlen( $name ) > 120 ) {
		$errors['mx_name'] = __( 'Please enter your name.', 'muhamed-ahmed-astran' );
	}
	if ( ! is_email( $email ) ) {
		$errors['mx_email'] = __( 'Please enter a valid email address.', 'muhamed-ahmed-astran' );
	}
	if ( '' !== $phone && ! preg_match( '/^[\d\s+().-]{6,25}$/', $phone ) ) {
		$errors['mx_phone'] = __( 'Please enter a valid phone number or leave it empty.', 'muhamed-ahmed-astran' );
	}
	if ( mb_strlen( $message ) < 10 || mb_strlen( $message ) > 5000 ) {
		$errors['mx_message'] = __( 'Please write a short message (at least 10 characters).', 'muhamed-ahmed-astran' );
	}
	if ( mahx_opt( 'cf_consent' ) && empty( $_POST['mx_consent'] ) ) {
		$errors['mx_consent'] = __( 'Please confirm the consent box.', 'muhamed-ahmed-astran' );
	}
	if ( $errors ) {
		$fail( __( 'Please check the highlighted fields.', 'muhamed-ahmed-astran' ), $errors );
	}

	$to    = sanitize_email( (string) mahx_opt( 'cf_to' ) );
	$to    = is_email( $to ) ? $to : get_option( 'admin_email' );
	$ip    = mahx_request_ip();
	$refer = mahx_request_referrer();
	$body  = mahx_contact_body(
		array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'service' => $service,
			'budget'  => $budget,
			'page'    => $page ? $page : esc_url_raw( $back ),
			'ip'      => $ip,
			'referer' => $refer,
			'message' => $message,
		)
	);
	$subject = trim( (string) mahx_opt( 'cf_subject' ) . ': ' . $name . ( $service ? ' · ' . $service : '' ) );
	$headers = array( 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>' ), '', $name ) . ' <' . $email . '>' );
	$sent    = wp_mail( $to, wp_specialchars_decode( $subject, ENT_QUOTES ), $body, $headers );

	if ( mahx_opt( 'cf_save' ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'mx_enquiry',
				'post_status'  => 'private',
				'post_title'   => $subject,
				'post_content' => $body,
			)
		);
	}
	if ( ! $sent && ! mahx_opt( 'cf_save' ) ) {
		$fail( __( 'The message could not be sent. Please email me directly.', 'muhamed-ahmed-astran' ) );
	}
	set_transient( $rkey, $hits + 1, HOUR_IN_SECONDS );

	$ok = (string) mahx_opt( 'cf_success' );
	mahx_contact_end_language( $lang_state );

	if ( $ajax ) {
		wp_send_json_success( array( 'message' => $ok ) );
		mahx_contact_stop();
	}
	wp_safe_redirect( add_query_arg( 'mx_sent', '1', remove_query_arg( 'mx_cf', $back ) ) . '#mx-form' );
	mahx_contact_stop();
}
add_action( 'admin_post_nopriv_mahx_contact', 'mahx_contact_submit' );
add_action( 'admin_post_mahx_contact', 'mahx_contact_submit' );
