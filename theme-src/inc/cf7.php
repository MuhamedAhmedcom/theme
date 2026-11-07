<?php
/**
 * Contact form delivery through Contact Form 7.
 *
 * The designed contact form (page-contact.php) keeps its markup; when CF7 is
 * active and the theme's CF7 form exists, the form is sent to CF7's REST
 * feedback endpoint so validation, mail and spam checks run inside CF7.
 * Recipient, subject, success text and spam limits stay in the panel
 * (cf_* options). The admin-post handler in contact.php is the no-JS fallback.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

/**
 * The CF7 form ID used by the designed contact form, or 0.
 *
 * @return int
 */
function mahx_cf7_form_id() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return 0;
	}
	$id = (int) get_option( 'mahx_cf7_form_id', 0 );
	return ( $id && 'wpcf7_contact_form' === get_post_type( $id ) ) ? $id : 0;
}

/**
 * Whether this CF7 submission belongs to the theme's form.
 *
 * @param mixed $form CF7 contact form.
 * @return bool
 */
function mahx_cf7_is_ours( $form ) {
	return $form && is_object( $form ) && method_exists( $form, 'id' ) && (int) $form->id() === mahx_cf7_form_id() && mahx_cf7_form_id() > 0;
}

/**
 * Hidden fields CF7 needs, printed inside the designed form.
 */
function mahx_cf7_hidden_fields() {
	$id = mahx_cf7_form_id();
	if ( ! $id ) {
		return;
	}
	$post          = (int) get_queried_object_id();
	$mahx_cf7_lang = apply_filters( 'wpml_current_language', null );
	$f             = array(
		'_wpcf7'                => $id,
		'_wpcf7_version'        => defined( 'WPCF7_VERSION' ) ? WPCF7_VERSION : '',
		'_wpcf7_locale'         => determine_locale(),
		'_wpcf7_unit_tag'       => 'wpcf7-f' . $id . '-p' . $post . '-o1',
		'_wpcf7_container_post' => $post,
		'mx_lang'               => is_string( $mahx_cf7_lang ) ? $mahx_cf7_lang : '',
	);
	foreach ( $f as $k => $v ) {
		printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $k ), esc_attr( (string) $v ) );
	}
}

/**
 * Strings and endpoint for contact-cf7.js (rendered in the page language).
 *
 * @return array
 */
function mahx_cf7_js_config() {
	$id = mahx_cf7_form_id();
	return array(
		'endpoint' => $id ? esc_url_raw( rest_url( 'contact-form-7/v1/contact-forms/' . $id . '/feedback' ) ) : '',
		'email'    => (string) mahx_email(),
		'i18n'     => array(
			'sending'    => __( 'Sending…', 'muhamed-ahmed-astran' ),
			'okTitle'    => (string) mahx_opt( 'cf_success_title' ),
			'okBody'     => (string) mahx_opt( 'cf_success' ),
			'done'       => __( 'Done', 'muhamed-ahmed-astran' ),
			'close'      => __( 'Close', 'muhamed-ahmed-astran' ),
			'errTitle'   => __( 'Message not sent', 'muhamed-ahmed-astran' ),
			'failed'     => __( 'Something went wrong. Please try again or email me directly.', 'muhamed-ahmed-astran' ),
			'mailFailed' => __( 'The message could not be sent. Please email me directly.', 'muhamed-ahmed-astran' ),
			'spam'       => __( 'We could not verify your message. Please wait a moment and try again.', 'muhamed-ahmed-astran' ),
			'invalid'    => __( 'Please check the highlighted fields.', 'muhamed-ahmed-astran' ),
			'emailMe'    => __( 'Email me directly', 'muhamed-ahmed-astran' ),
			'fields'     => array(
				'mx_name'    => __( 'Please enter your name.', 'muhamed-ahmed-astran' ),
				'mx_email'   => __( 'Please enter a valid email address.', 'muhamed-ahmed-astran' ),
				'mx_phone'   => __( 'Please enter a valid phone number or leave it empty.', 'muhamed-ahmed-astran' ),
				'mx_message' => __( 'Please write a short message (at least 10 characters).', 'muhamed-ahmed-astran' ),
				'mx_consent' => __( 'Please confirm the consent box.', 'muhamed-ahmed-astran' ),
			),
		),
	);
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! function_exists( 'mahx_designed_page' ) || 'contact' !== mahx_designed_page() || ! mahx_opt( 'cf_enable' ) || ! mahx_cf7_form_id() ) {
			return;
		}
		$rel = '/assets/v18/contact-cf7.js';
		$css = '/assets/v18/contact-modal.css';
		$ver = static function ( $r ) {
			$p = MUHAMEDAHMED_ASTRAN_DIR . $r;
			return file_exists( $p ) ? (string) filemtime( $p ) : MUHAMEDAHMED_ASTRAN_VERSION;
		};
		wp_enqueue_style( 'mx-contact-modal', MUHAMEDAHMED_ASTRAN_URI . $css, array(), $ver( $css ) );
		$deps = wp_script_is( 'mx-form-phone', 'enqueued' ) ? array( 'mx-form-phone' ) : array();
		wp_enqueue_script( 'mx-contact-cf7', MUHAMEDAHMED_ASTRAN_URI . $rel, $deps, $ver( $rel ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_add_inline_script( 'mx-contact-cf7', 'window.mxCf7 = ' . wp_json_encode( mahx_cf7_js_config() ) . ';', 'before' );
	},
	30
);

/* The theme renders its own form, so CF7's front-end JS/CSS are not needed. */
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

/* Normalise posted values: service ID to its title, E.164 phone, known project types only. */
add_filter(
	'wpcf7_posted_data',
	static function ( $data ) {
		$form = class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_current() : null;
		if ( ! mahx_cf7_is_ours( $form ) || ! is_array( $data ) ) {
			return $data;
		}
		$svcs               = mahx_service_choices();
		$sid                = isset( $data['mx_service'] ) ? absint( $data['mx_service'] ) : 0;
		$data['mx_service'] = isset( $svcs[ $sid ] ) ? $svcs[ $sid ] : '';
		$budget             = isset( $data['mx_budget'] ) ? (string) $data['mx_budget'] : '';
		$data['mx_budget']  = in_array( $budget, mahx_budgets(), true ) ? $budget : '';
		$e164               = isset( $data['mx_phone_e164'] ) ? preg_replace( '/[^\d+]/', '', (string) $data['mx_phone_e164'] ) : '';
		if ( $e164 && preg_match( '/^\+\d{6,18}$/', $e164 ) ) {
			$data['mx_phone'] = $e164;
		}
		return $data;
	}
);

/* Consent is required only while the panel asks for it. */
add_filter(
	'wpcf7_validate_acceptance',
	static function ( $result, $tag ) {
		$form = WPCF7_ContactForm::get_current();
		if ( mahx_cf7_is_ours( $form ) && 'mx_consent' === $tag->name && mahx_opt( 'cf_consent' ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- CF7 handles the request.
			if ( empty( $_POST['mx_consent'] ) ) {
				$result->invalidate( $tag, __( 'Please confirm the consent box.', 'muhamed-ahmed-astran' ) );
			}
		}
		return $result;
	},
	20,
	2
);

/* Spam layer: honeypot, minimum fill time, same-site page, per-visitor rate, optional Turnstile. */
add_filter(
	'wpcf7_spam',
	static function ( $spam, $submission = null ) {
		if ( $spam ) {
			return $spam;
		}
		$form = class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_current() : null;
		if ( ! mahx_cf7_is_ours( $form ) ) {
			return $spam;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- CF7 REST submission; checks below are the guard.
		$reason = '';
		if ( ! empty( $_POST['mx_website'] ) ) {
			$reason = 'honeypot';
		}
		$started = isset( $_POST['mx_t'] ) ? absint( $_POST['mx_t'] ) : 0;
		if ( ! $reason && ( time() - $started ) < (int) mahx_opt( 'cf_min_secs' ) ) {
			$reason = 'too fast';
		}
		if ( ! $reason && false === mahx_posted_page( isset( $_POST['mx_page'] ) ? wp_unslash( $_POST['mx_page'] ) : '' ) ) {
			$reason = 'foreign page';
		}
		if ( ! $reason && (int) get_transient( mahx_visitor_key() ) >= max( 1, (int) mahx_opt( 'cf_rate' ) ) ) {
			$reason = 'rate limit';
		}
		if ( ! $reason && mahx_turnstile_on() ) {
			$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
			$res   = wp_remote_post(
				'https://challenges.cloudflare.com/turnstile/v0/siteverify',
				array(
					'timeout' => 8,
					'body'    => array(
						'secret'   => (string) mahx_opt( 'cf_ts_secret' ),
						'response' => $token,
						'remoteip' => mahx_request_ip(),
					),
				)
			);
			$ok    = ! is_wp_error( $res ) && ! empty( json_decode( (string) wp_remote_retrieve_body( $res ), true )['success'] );
			if ( ! $ok ) {
				$reason = 'turnstile';
			}
		}
		// phpcs:enable
		if ( $reason && $submission && method_exists( $submission, 'add_spam_log' ) ) {
			$submission->add_spam_log(
				array(
					'agent'  => 'muhamed-ahmed-astran',
					'reason' => $reason,
				)
			);
		}
		return (bool) $reason;
	},
	20,
	2
);

/* Recipient, subject and body come from the panel and the theme's mail layout. */
add_filter(
	'wpcf7_mail_components',
	static function ( $components, $form = null, $mail = null ) {
		if ( ! mahx_cf7_is_ours( $form ) || ( $mail && method_exists( $mail, 'name' ) && 'mail' !== $mail->name() ) ) {
			return $components;
		}
		$sub = WPCF7_Submission::get_instance();
		$p   = $sub ? (array) $sub->get_posted_data() : array();
		$g   = static function ( $k ) use ( $p ) {
			$v = isset( $p[ $k ] ) ? $p[ $k ] : '';
			return is_array( $v ) ? implode( ', ', $v ) : (string) $v;
		};
		$to    = sanitize_email( (string) mahx_opt( 'cf_to' ) );
		$to    = is_email( $to ) ? $to : get_option( 'admin_email' );
		$name  = sanitize_text_field( $g( 'mx_name' ) );
		$email = sanitize_email( $g( 'mx_email' ) );
		$svc   = sanitize_text_field( $g( 'mx_service' ) );
		$page  = $sub ? (string) $sub->get_meta( 'url' ) : '';
		$components['recipient'] = $to;
		$components['subject']   = wp_specialchars_decode( trim( (string) mahx_opt( 'cf_subject' ) . ': ' . $name . ( $svc ? ' · ' . $svc : '' ) ), ENT_QUOTES );
		$components['body']      = mahx_cf7_html_mail(
			array(
				'name'    => $name,
				'email'   => $email,
				'phone'   => sanitize_text_field( $g( 'mx_phone' ) ),
				'service' => $svc,
				'budget'  => sanitize_text_field( $g( 'mx_budget' ) ),
				'page'    => esc_url_raw( $page ),
				'ip'      => mahx_request_ip(),
				'referer' => mahx_request_referrer(),
				'message' => sanitize_textarea_field( $g( 'mx_message' ) ),
			)
		);
		if ( is_email( $email ) ) {
			$components['additional_headers'] = 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>', ',' ), '', $name ) . ' <' . $email . '>';
		}
		return $components;
	},
	20,
	3
);

/* Count the send for the rate limit; optionally keep a private copy. */
add_action(
	'wpcf7_mail_sent',
	static function ( $form ) {
		if ( ! mahx_cf7_is_ours( $form ) ) {
			return;
		}
		$k = mahx_visitor_key();
		set_transient( $k, (int) get_transient( $k ) + 1, HOUR_IN_SECONDS );
		if ( mahx_opt( 'cf_save' ) ) {
			$sub = WPCF7_Submission::get_instance();
			$p   = $sub ? (array) $sub->get_posted_data() : array();
			wp_insert_post(
				array(
					'post_type'    => 'mx_enquiry',
					'post_status'  => 'private',
					'post_title'   => sanitize_text_field( (string) mahx_opt( 'cf_subject' ) . ': ' . ( isset( $p['mx_name'] ) ? (string) $p['mx_name'] : '' ) ),
					'post_content' => sanitize_textarea_field( isset( $p['mx_message'] ) ? (string) $p['mx_message'] : '' ),
				)
			);
		}
	}
);

/**
 * Responsive, table-based HTML notification (inline CSS for email clients).
 *
 * @param array $d Values: name, email, phone, service, budget, page, ip, referer, message.
 * @return string
 */
function mahx_cf7_html_mail( array $d ) {
	$e    = static function ( $v ) {
		return esc_html( (string) $v );
	};
	$rows = array(
		__( 'Name', 'muhamed-ahmed-astran' )         => $e( $d['name'] ),
		__( 'Email', 'muhamed-ahmed-astran' )        => is_email( $d['email'] ) ? '<a href="mailto:' . esc_attr( $d['email'] ) . '" style="color:#2563eb;text-decoration:none;">' . $e( $d['email'] ) . '</a>' : $e( $d['email'] ),
		__( 'Phone', 'muhamed-ahmed-astran' )        => $d['phone'] ? '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $d['phone'] ) ) . '" style="color:#2563eb;text-decoration:none;">' . $e( $d['phone'] ) . '</a>' : '',
		__( 'Project type', 'muhamed-ahmed-astran' ) => $e( $d['budget'] ),
		__( 'Service', 'muhamed-ahmed-astran' )      => $e( $d['service'] ),
	);
	$tr = '';
	foreach ( $rows as $k => $v ) {
		if ( '' === $v ) {
			continue;
		}
		$tr .= '<tr><td style="padding:10px 0;border-bottom:1px solid #eef0f4;color:#667085;font-size:13px;width:130px;vertical-align:top;">' . $e( $k ) . '</td><td style="padding:10px 0;border-bottom:1px solid #eef0f4;color:#101828;font-size:15px;font-weight:600;word-break:break-word;">' . $v . '</td></tr>';
	}
	$msg   = nl2br( $e( $d['message'] ) );
	$reply = is_email( $d['email'] ) ? '<a href="mailto:' . esc_attr( $d['email'] ) . '?subject=' . rawurlencode( 'Re: ' . (string) mahx_opt( 'cf_subject' ) ) . '" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 26px;border-radius:999px;">' . $e( __( 'Reply to', 'muhamed-ahmed-astran' ) . ' ' . $d['name'] ) . '</a>' : '';
	$meta  = array_filter( array( $d['page'] ? 'Page: ' . $e( $d['page'] ) : '', $d['referer'] ? 'Referrer: ' . $e( $d['referer'] ) : '', $d['ip'] ? 'IP: ' . $e( $d['ip'] ) : '', $e( wp_date( 'Y-m-d H:i T' ) ) ) );
	$site  = $e( wp_parse_url( home_url(), PHP_URL_HOST ) );
	$rtl   = preg_match( '/\p{Arabic}/u', (string) $d['message'] . $d['name'] ) ? 'rtl' : 'ltr';
	return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>' . $e( mahx_opt( 'cf_subject' ) ) . '</title>'
		. '<style>@media (max-width:600px){.mx-w{width:100%!important}.mx-p{padding:22px 18px!important}}</style></head>'
		. '<body style="margin:0;padding:0;background:#f2f4f7;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,Tahoma,sans-serif;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7;"><tr><td align="center" style="padding:28px 12px;">'
		. '<table role="presentation" class="mx-w" width="600" cellpadding="0" cellspacing="0" style="width:600px;max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e4e7ec;">'
		. '<tr><td style="background:#0b1220;padding:22px 32px;" class="mx-p"><span style="color:#ffffff;font-size:18px;font-weight:700;letter-spacing:.2px;">Muhamed Ahmed</span><span style="color:#98a2b3;font-size:13px;"> &nbsp;·&nbsp; ' . $site . '</span></td></tr>'
		. '<tr><td class="mx-p" style="padding:30px 32px 8px;"><p style="margin:0 0 4px;color:#2563eb;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">' . $e( mahx_opt( 'cf_subject' ) ) . '</p><h1 style="margin:0 0 18px;color:#101828;font-size:22px;line-height:1.3;">' . $e( $d['name'] ) . '</h1>'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $tr . '</table></td></tr>'
		. '<tr><td class="mx-p" style="padding:18px 32px 6px;"><p style="margin:0 0 8px;color:#667085;font-size:13px;">' . $e( __( 'Message', 'muhamed-ahmed-astran' ) ) . '</p><div dir="' . $rtl . '" style="background:#f8fafc;border:1px solid #eef0f4;border-radius:12px;padding:16px 18px;color:#101828;font-size:15px;line-height:1.7;">' . $msg . '</div></td></tr>'
		. ( $reply ? '<tr><td align="center" style="padding:24px 32px 28px;">' . $reply . '</td></tr>' : '' )
		. '<tr><td class="mx-p" style="padding:16px 32px;background:#f8fafc;border-top:1px solid #eef0f4;color:#98a2b3;font-size:12px;line-height:1.6;">' . implode( '<br>', $meta ) . '</td></tr>'
		. '</table></td></tr></table></body></html>';
}