<?php
/**
 * Template Name: MX Contact
 *
 * Designed Contact page with a built-in form. Applied automatically to the
 * page whose slug is set in Customize > Muhamed Ahmed > Contact page. With
 * the design off, the page's own editor content is shown through Astra.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

if ( 'contact' !== mahx_designed_page() ) {
	require get_template_directory() . '/page.php';
	return;
}

get_header( 'mx' );

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display state after a redirect.
$mahx_state = array();
if ( isset( $_GET['mx_cf'] ) ) {
	$mahx_state = get_transient( 'mahx_cf_err_' . sanitize_key( wp_unslash( $_GET['mx_cf'] ) ) );
	$mahx_state = is_array( $mahx_state ) ? $mahx_state : array();
}
$mahx_sent = isset( $_GET['mx_sent'] );
$mahx_pick = isset( $_GET['service'] ) ? absint( $_GET['service'] ) : 0;
// phpcs:enable

$mahx_old  = isset( $mahx_state['old'] ) ? (array) $mahx_state['old'] : array();
$mahx_errs = isset( $mahx_state['errors'] ) ? (array) $mahx_state['errors'] : array();
$mahx_val  = static function ( $k ) use ( $mahx_old ) {
	return isset( $mahx_old[ $k ] ) ? (string) $mahx_old[ $k ] : '';
};
$mahx_pick = $mahx_val( 'mx_service' ) ? (int) $mahx_val( 'mx_service' ) : $mahx_pick;

$mahx_email = mahx_email();
$mahx_phone = mahx_identity( 'phone' );
$mahx_wa    = mahx_wa_url();
$mahx_loc   = mahx_identity( 'location' );
$mahx_hours = mahx_text( 'ctp_hours', 'response_time' );
$mahx_book  = (string) mahx_opt( 'ctp_booking' );
$mahx_map   = (string) mahx_opt( 'ctp_map' );
$mahx_same  = mahx_same_as();

/**
 * Print one field's error slot.
 *
 * @param string $k    Field name.
 * @param array  $errs Errors.
 */
$mahx_err = static function ( $k, $errs ) {
	printf( '<span class="mx-field__err" id="%1$s">%2$s</span>', esc_attr( 'mx-err-' . $k ), isset( $errs[ $k ] ) ? esc_html( $errs[ $k ] ) : '' );
};
$mahx_inv = static function ( $k, $errs ) {
	echo isset( $errs[ $k ] ) ? ' aria-invalid="true"' : '';
	echo ' aria-describedby="' . esc_attr( 'mx-err-' . $k ) . '"';
};
?>
<section class="mx-sec mx-sec--noband mx-page-hero" aria-labelledby="mx-page-title">
	<div class="mx-shell">
		<p class="mx-eyebrow"><?php echo esc_html( (string) mahx_opt( 'ctp_eyebrow' ) ); ?></p>
		<h1 class="mx-page-hero__title" id="mx-page-title"><?php echo mahx_highlight( (string) mahx_opt( 'ctp_title' ) ? (string) mahx_opt( 'ctp_title' ) : get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- mahx_highlight escapes. ?></h1>
		<?php if ( mahx_opt( 'ctp_lead' ) ) : ?>
			<p class="mx-lead"><?php echo esc_html( (string) mahx_opt( 'ctp_lead' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="mx-sec mx-sec--tight mx-sec--band">
	<div class="mx-shell mx-contact">
		<aside class="mx-contact__info" aria-label="<?php esc_attr_e( 'Contact details', 'muhamed-ahmed-astran' ); ?>">
			<dl class="mx-contact__list">
				<?php if ( $mahx_email ) : ?>
					<div><dt><?php esc_html_e( 'Email', 'muhamed-ahmed-astran' ); ?></dt><dd><a href="<?php echo esc_url( 'mailto:' . $mahx_email ); ?>"><?php echo esc_html( $mahx_email ); ?></a></dd></div>
				<?php endif; ?>
				<?php if ( $mahx_phone ) : ?>
					<div><dt><?php esc_html_e( 'Phone', 'muhamed-ahmed-astran' ); ?></dt><dd><a dir="ltr" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $mahx_phone ) ); ?>"><?php echo esc_html( $mahx_phone ); ?></a></dd></div>
				<?php endif; ?>
				<?php if ( $mahx_wa ) : ?>
					<div><dt><?php esc_html_e( 'WhatsApp', 'muhamed-ahmed-astran' ); ?></dt><dd><a href="<?php echo esc_url( $mahx_wa ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Chat on WhatsApp', 'muhamed-ahmed-astran' ); ?><span class="mx-sr"> <?php esc_html_e( '(opens in a new tab)', 'muhamed-ahmed-astran' ); ?></span></a></dd></div>
				<?php endif; ?>
				<?php if ( $mahx_loc ) : ?>
					<div><dt><?php esc_html_e( 'Location', 'muhamed-ahmed-astran' ); ?></dt><dd><?php echo esc_html( $mahx_loc ); ?></dd></div>
				<?php endif; ?>
				<?php if ( $mahx_hours ) : ?>
					<div><dt><?php esc_html_e( 'Availability', 'muhamed-ahmed-astran' ); ?></dt><dd><?php echo esc_html( $mahx_hours ); ?></dd></div>
				<?php endif; ?>
			</dl>
			<?php if ( $mahx_book ) : ?>
				<a class="mx-btn mx-btn--ghost mx-btn--lg" href="<?php echo esc_url( $mahx_book ); ?>" rel="noopener" target="_blank"><?php echo esc_html( (string) mahx_opt( 'ctp_booking_label' ) ); ?><span class="mx-sr"> <?php esc_html_e( '(opens in a new tab)', 'muhamed-ahmed-astran' ); ?></span></a>
			<?php endif; ?>
			<?php if ( $mahx_same ) : ?>
				<ul class="mx-foot__social" aria-label="<?php esc_attr_e( 'Profiles', 'muhamed-ahmed-astran' ); ?>">
					<?php foreach ( $mahx_same as $mahx_u ) : ?>
						<li><a href="<?php echo esc_url( $mahx_u ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( mahx_profile_label( $mahx_u ) ); ?><span class="mx-sr"> <?php esc_html_e( '(opens in a new tab)', 'muhamed-ahmed-astran' ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</aside>

<?php if ( mahx_opt( 'cf_enable' ) ) : ?>
		<?php
		/*
		 * Row plan: name | email, phone | project type, then the service list.
		 * The project type is the budget field (mx_budget / cf_budgets); the
		 * service list moves to its own row when both are switched on.
		 */
		$mahx_services    = mahx_service_choices();
		$mahx_budgets     = mahx_budgets();
		$mahx_phone_on    = (bool) mahx_opt( 'cf_phone' );
		$mahx_second      = $mahx_budgets ? 'budget' : ( $mahx_services ? 'service' : '' );
		$mahx_service_row = ( 'service' !== $mahx_second ) && (bool) $mahx_services;
		$mahx_phone_val   = $mahx_val( 'mx_phone_e164' ) ? $mahx_val( 'mx_phone_e164' ) : $mahx_val( 'mx_phone' );
		?>
		<form class="mx-form" id="mx-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" <?php echo ( function_exists( 'mahx_cf7_form_id' ) && mahx_cf7_form_id() ) ? ' data-mx-cf7' : ''; ?> data-mx-form novalidate>
			<p class="mx-form__status<?php echo $mahx_sent ? ' is-ok' : ( $mahx_state ? ' is-err' : '' ); ?>" data-mx-status role="status" aria-live="polite" tabindex="-1">
				<?php
				if ( $mahx_sent ) {
					echo esc_html( (string) mahx_opt( 'cf_success' ) );
				} elseif ( isset( $mahx_state['message'] ) ) {
					echo esc_html( (string) $mahx_state['message'] );
				}
				?>
			</p>
			<input type="hidden" name="action" value="mahx_contact">
			<input type="hidden" name="mx_t" value="<?php echo esc_attr( (string) time() ); ?>">
			<input type="hidden" name="mx_page" value="<?php echo esc_url( (string) get_permalink() ); ?>">
			<?php
			$mahx_form_lang = apply_filters( 'wpml_current_language', null );
			if ( is_string( $mahx_form_lang ) && '' !== $mahx_form_lang ) :
				?>
			<input type="hidden" name="mx_lang" value="<?php echo esc_attr( $mahx_form_lang ); ?>">
				<?php
			endif;
			?>
			<?php wp_nonce_field( 'mahx_contact', 'mx_nonce' ); ?>
			<?php if ( function_exists( 'mahx_cf7_hidden_fields' ) ) { mahx_cf7_hidden_fields(); } ?>
			<div class="mx-hp" aria-hidden="true"><label for="mx-website"><?php esc_html_e( 'Leave this empty', 'muhamed-ahmed-astran' ); ?></label><input type="text" id="mx-website" name="mx_website" tabindex="-1" autocomplete="off"></div>
			<div class="mx-form__row">
				<div class="mx-field">
					<label for="mx-name"><?php echo esc_html( (string) mahx_opt( 'cf_l_name' ) ); ?> <span aria-hidden="true">*</span></label>
					<input type="text" id="mx-name" name="mx_name" required autocomplete="name" maxlength="120" value="<?php echo esc_attr( $mahx_val( 'mx_name' ) ); ?>"<?php $mahx_inv( 'mx_name', $mahx_errs ); ?>>
					<?php $mahx_err( 'mx_name', $mahx_errs ); ?>
				</div>
				<div class="mx-field">
					<label for="mx-email"><?php echo esc_html( (string) mahx_opt( 'cf_l_email' ) ); ?> <span aria-hidden="true">*</span></label>
					<input type="email" id="mx-email" name="mx_email" required autocomplete="email" dir="ltr" value="<?php echo esc_attr( $mahx_val( 'mx_email' ) ); ?>"<?php $mahx_inv( 'mx_email', $mahx_errs ); ?>>
					<?php $mahx_err( 'mx_email', $mahx_errs ); ?>
				</div>
			</div>
			<?php if ( $mahx_phone_on || $mahx_second ) : ?>
			<div class="mx-form__row">
				<?php if ( $mahx_phone_on ) : ?>
					<div class="mx-field mx-field--phone">
						<label for="mx-phone"><?php echo esc_html( (string) mahx_opt( 'cf_l_phone' ) ); ?></label>
						<input type="tel" id="mx-phone" name="mx_phone" autocomplete="tel" dir="ltr" value="<?php echo esc_attr( $mahx_phone_val ); ?>"<?php $mahx_inv( 'mx_phone', $mahx_errs ); ?>>
						<input type="hidden" name="mx_phone_e164" id="mx-phone-e164" value="<?php echo esc_attr( $mahx_val( 'mx_phone_e164' ) ); ?>">
						<?php $mahx_err( 'mx_phone', $mahx_errs ); ?>
					</div>
				<?php endif; ?>
				<?php if ( 'budget' === $mahx_second ) : ?>
					<div class="mx-field">
						<label for="mx-budget"><?php echo esc_html( (string) mahx_opt( 'cf_l_budget' ) ); ?></label>
						<select id="mx-budget" name="mx_budget">
							<option value=""><?php esc_html_e( 'Choose…', 'muhamed-ahmed-astran' ); ?></option>
							<?php foreach ( $mahx_budgets as $mahx_b ) : ?>
								<option value="<?php echo esc_attr( (string) $mahx_b ); ?>"<?php selected( $mahx_val( 'mx_budget' ), $mahx_b ); ?>><?php echo esc_html( $mahx_b ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php elseif ( 'service' === $mahx_second ) : ?>
					<div class="mx-field">
						<label for="mx-service"><?php echo esc_html( (string) mahx_opt( 'cf_l_service' ) ); ?></label>
						<select id="mx-service" name="mx_service">
							<option value="0"><?php esc_html_e( 'Choose…', 'muhamed-ahmed-astran' ); ?></option>
							<?php foreach ( $mahx_services as $mahx_id => $mahx_t ) : ?>
								<option value="<?php echo esc_attr( (string) $mahx_id ); ?>"<?php selected( $mahx_pick, $mahx_id ); ?>><?php echo esc_html( $mahx_t ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if ( $mahx_service_row ) : ?>
			<div class="mx-form__row">
				<div class="mx-field">
					<label for="mx-service"><?php echo esc_html( (string) mahx_opt( 'cf_l_service' ) ); ?></label>
					<select id="mx-service" name="mx_service">
						<option value="0"><?php esc_html_e( 'Choose…', 'muhamed-ahmed-astran' ); ?></option>
						<?php foreach ( $mahx_services as $mahx_id => $mahx_t ) : ?>
							<option value="<?php echo esc_attr( (string) $mahx_id ); ?>"<?php selected( $mahx_pick, $mahx_id ); ?>><?php echo esc_html( $mahx_t ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<?php endif; ?>
			<div class="mx-field">
				<label for="mx-message"><?php echo esc_html( (string) mahx_opt( 'cf_l_message' ) ); ?> <span aria-hidden="true">*</span></label>
				<textarea id="mx-message" name="mx_message" rows="6" required minlength="10" maxlength="5000"<?php $mahx_inv( 'mx_message', $mahx_errs ); ?>><?php echo esc_textarea( $mahx_val( 'mx_message' ) ); ?></textarea>
				<?php $mahx_err( 'mx_message', $mahx_errs ); ?>
			</div>
			<?php if ( mahx_opt( 'cf_consent' ) ) : ?>
				<div class="mx-field mx-field--check">
					<input type="checkbox" id="mx-consent" name="mx_consent" value="1" required<?php $mahx_inv( 'mx_consent', $mahx_errs ); ?>>
					<label for="mx-consent"><?php echo esc_html( (string) mahx_opt( 'cf_l_consent' ) ); ?>
					<?php
					if ( mahx_opt( 'cf_privacy_link' ) && get_privacy_policy_url() ) {
						echo ' ' . get_the_privacy_policy_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core escapes.
					}
					?>
					</label>
					<?php $mahx_err( 'mx_consent', $mahx_errs ); ?>
				</div>
			<?php endif; ?>
			<?php if ( mahx_turnstile_on() ) : ?>
				<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( (string) mahx_opt( 'cf_ts_site' ) ); ?>"></div>
			<?php endif; ?>
			<button type="submit" class="mx-btn mx-btn--primary mx-btn--lg"><?php echo esc_html( (string) mahx_opt( 'cf_l_submit' ) ); ?><?php echo mahx_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></button>
		</form>
		<?php endif; ?>
	</div>
	<?php if ( $mahx_map && 0 === strpos( $mahx_map, 'https://' ) ) : ?>
		<div class="mx-shell mx-map">
			<iframe src="<?php echo esc_url( $mahx_map ); ?>" title="<?php esc_attr_e( 'Map', 'muhamed-ahmed-astran' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
		</div>
	<?php endif; ?>
</section>
<?php
get_footer( 'mx' );
