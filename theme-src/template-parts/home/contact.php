<?php
/**
 * Final call to action, optional form shortcode.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

$mahx_title = (string) mahx_opt( 'cta_title' );
$mahx_email = mahx_email();
$mahx_form  = trim( (string) mahx_opt( 'cta_form' ) );
?>
<section class="mx-sec mx-sec--noband" id="contact" aria-labelledby="mx-cta-title">
	<div class="mx-shell">
		<div class="mx-cta" data-reveal>
			<?php if ( mahx_opt( 'cta_eyebrow' ) ) : ?>
				<p class="mx-eyebrow"><?php echo esc_html( (string) mahx_opt( 'cta_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2 id="mx-cta-title"><?php echo $mahx_title ? mahx_highlight( $mahx_title ) : esc_html( mahx_cta_label() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapes. ?></h2>
			<?php if ( mahx_opt( 'cta_text' ) ) : ?>
				<p class="mx-lead"><?php echo esc_html( (string) mahx_opt( 'cta_text' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $mahx_form && preg_match( '/^\[[a-z0-9_\-]+[^\]]*\]$/i', $mahx_form ) ) : ?>
				<div class="mx-cta__form"><?php echo do_shortcode( $mahx_form ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output. ?></div>
			<?php endif; ?>
			<div class="mx-cta__row">
				<?php
				$mahx_url   = mahx_cta_href();
				$mahx_label = trim( (string) mahx_opt( 'cta_primary_label' ) );
				$mahx_label = '' !== $mahx_label ? $mahx_label : mahx_cta_label();
				$mahx_mlbl  = trim( (string) mahx_opt( 'cta_mail_label' ) );
				?>
				<?php if ( $mahx_url ) : ?>
					<a class="mx-btn mx-btn--primary mx-btn--lg" href="<?php echo esc_url( $mahx_url ); ?>"><?php echo esc_html( $mahx_label ); ?><?php echo mahx_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></a>
				<?php endif; ?>
				<?php if ( $mahx_email ) : ?>
					<a class="mx-btn mx-btn--ghost mx-btn--lg mx-cta__mailbtn" href="<?php echo esc_url( 'mailto:' . $mahx_email ); ?>"><?php echo esc_html( '' !== $mahx_mlbl ? $mahx_mlbl : $mahx_email ); ?></a>
					<?php if ( '' !== $mahx_mlbl ) : ?>
						<span class="mx-cta__mail"><?php echo esc_html( $mahx_email ); ?></span>
					<?php endif; ?>
				<?php endif; ?>
				<?php
				$mahx_contact = home_url( '/contact/' );
				if ( ! $mahx_email && ! mahx_wa_url() && untrailingslashit( $mahx_url ) !== untrailingslashit( $mahx_contact ) && get_page_by_path( 'contact' ) ) :
					?>
					<a class="mx-btn mx-btn--ghost mx-btn--lg" href="<?php echo esc_url( $mahx_contact ); ?>"><?php esc_html_e( 'Contact page', 'muhamed-ahmed-astran' ); ?></a>
				<?php endif; ?>
				<?php if ( mahx_wa_url() ) : ?>
					<a class="mx-btn mx-wa-btn mx-btn--lg" href="<?php echo esc_url( mahx_wa_url() ); ?>" rel="noopener" target="_blank"><?php echo mahx_wa_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( (string) mahx_opt( 'wa_label' ) ); ?><span class="mx-sr"> <?php esc_html_e( '(opens in a new tab)', 'muhamed-ahmed-astran' ); ?></span></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
