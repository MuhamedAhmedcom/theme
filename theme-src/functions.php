<?php
/**
 * Muhamed Ahmed Astra Child — theme bootstrap.
 *
 * The homepage is built from native Gutenberg blocks. This child theme wires
 * the parent, adds the accessibility affordances the blocks cannot, and stops
 * Astra's chrome from stacking on the designed pages.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

define( 'MUHAMEDAHMED_ASTRAN_VERSION', '2.0.1' );
define( 'MUHAMEDAHMED_ASTRAN_DIR', get_stylesheet_directory() );
define( 'MUHAMEDAHMED_ASTRAN_URI', get_stylesheet_directory_uri() );

require_once MUHAMEDAHMED_ASTRAN_DIR . '/fonts.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/behaviour.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/chrome.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/options.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/panel-chrome.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/design-controls.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/data.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/shipped.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/meta.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/blog.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/seo.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/assets.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/entity.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/contact.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/clients.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/watermark.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/wpml.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/i18n-chrome.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/projects.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/reviews.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/services-urls.php';
require_once MUHAMEDAHMED_ASTRAN_DIR . '/inc/whatsapp-redirect.php';

/**
 * Load the parent stylesheet, then this theme's front-end rules.
 *
 * The style.css file holds no rules so Astra stays the single source of its own
 * typography and layout and remains editable in the Customizer.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style(
			'muhamed-ahmed-astran-parent',
			get_template_directory_uri() . '/style.css',
			array(),
			defined( 'ASTRA_VERSION' ) ? ASTRA_VERSION : MUHAMEDAHMED_ASTRAN_VERSION
		);

		/*
		 * The design stylesheets are enqueued explicitly instead of through the
		 * theme.json "styles.css" list.
		 *
		 * theme.json named all four files, but WordPress only ever emitted
		 * chrome.css. That single omission caused every visible fault on the
		 * page: page.css carries the layout and overrides.css carries the
		 * container, contrast and button fixes, so neither ever reached the
		 * browser. The page rendered raw block markup with no design applied.
		 *
		 * An explicit enqueue cannot be silently dropped, and using filemtime
		 * for the version busts the cache whenever any sheet changes.
		 */
		$sheets = array(
			'design-base'     => '/style.css',
			'design-token'    => '/tokens.css',
			'design-page'     => '/page.css',
			'design-chrome'   => '/chrome.css',
			'design-override' => '/overrides.css',
		);

		foreach ( $sheets as $handle => $relative ) {
			$path = MUHAMEDAHMED_ASTRAN_DIR . $relative;

			wp_enqueue_style(
				$handle,
				MUHAMEDAHMED_ASTRAN_URI . $relative,
				array( 'muhamed-ahmed-astran-parent' ),
				file_exists( $path ) ? (string) filemtime( $path ) : MUHAMEDAHMED_ASTRAN_VERSION
			);
		}
	},
	10
);

/**
 * Report any missing design stylesheet in the markup.
 *
 * A stylesheet that 404s renders as an unstyled page, which is exactly the
 * failure that has already happened twice. Surfacing it in the DOM makes the
 * next occurrence obvious instead of silent.
 *
 * @return void
 */
add_action(
	'wp_footer',
	static function () {
		if ( ! muhamedahmed_astran_is_designed_page() ) {
			return;
		}

		$missing = array();

		foreach ( array( 'style.css', 'page.css', 'chrome.css', 'overrides.css' ) as $file ) {
			if ( ! file_exists( MUHAMEDAHMED_ASTRAN_DIR . '/' . $file ) ) {
				$missing[] = $file;
			}
		}

		if ( $missing ) {
			printf(
				'<div hidden data-mah-missing-css="%s"></div>',
				esc_attr( implode( ',', $missing ) )
			);
		}
	},
	99
);

/**
 * Editor stylesheet, so the canvas matches the front end.
 */
add_action(
	'enqueue_block_editor_assets',
	static function () {
		wp_enqueue_style(
			'muhamed-ahmed-astran-editor',
			MUHAMEDAHMED_ASTRAN_URI . '/editor.css',
			array(),
			MUHAMEDAHMED_ASTRAN_VERSION
		);
	}
);

/**
 * Is the current request a designed homepage?
 *
 * Used by the body-class filter and the chrome cleanup below so they cannot
 * drift apart.
 *
 * @return bool
 */
function muhamedahmed_astran_is_designed_page() {
	if ( is_front_page() && function_exists( 'muhamedahmed_render_home' ) ) {
		return true;
	}

	if ( ! is_page() ) {
		return false;
	}

	$post = get_post();

	if ( ! $post ) {
		return false;
	}

	return has_shortcode( (string) $post->post_content, 'muhamedahmed_home' )
		|| has_block( 'muhamedahmed/section', $post )
		|| has_block( 'muhamedahmed/hero', $post );
}

/**
 * Body class markers, so CSS can target designed pages specifically.
 *
 * @param array $classes Existing classes.
 * @return array
 */
add_filter(
	'body_class',
	static function ( $classes ) {
		$classes[] = 'muhamed-ahmed-astran';

		if ( muhamedahmed_astran_is_designed_page() ) {
			$classes[] = 'is-muhamedahmed-page';
		}

		return $classes;
	}
);

/**
 * Skip link for WCAG 2.4.1 bypass block.
 *
 * The nav has several items, so a keyboard user would otherwise tab through
 * all of them on every page before reaching the content.
 *
 * @return void
 */
add_action(
	'wp_head',
	static function () {
		/* Visitor's saved mode, applied before paint to avoid a flash. Skipped when the owner fixes the mode. */
		if ( function_exists( 'mahx_mode_toggle' ) && ! mahx_mode_toggle() ) {
			return;
		}
		wp_print_inline_script_tag( '(function(){var d=document.documentElement,t=null;try{t=localStorage.getItem("mah-theme");}catch(e){}if(t!=="light"&&t!=="dark"){t=d.getAttribute("data-theme");if(t!=="light"&&t!=="dark"){t=(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches)?"dark":"light";}}d.setAttribute("data-theme",t);})();' );
	},
	0
);

add_action(
	'wp_body_open',
	static function () {
		printf(
			'<a class="skip-link screen-reader-text" href="#content">%s</a>',
			esc_html__( 'Skip to content', 'muhamed-ahmed-astran' )
		);
	},
	1
);

/**
 * On a designed homepage, remove the duplicate chrome.
 *
 * The blocks render their own footer and the hero supplies the h1, so Astra's
 * default title and footer would be duplicates. Only applied to pages that
 * actually use the design, so every other page keeps stock Astra behaviour.
 *
 * @return void
 */
add_action(
	'wp',
	static function () {
		if ( ! muhamedahmed_astran_is_designed_page() ) {
			return;
		}

		add_filter( 'astra_show_default_page_title', '__return_false' );

		remove_action( 'astra_footer', 'astra_footer' );
	}
);

/**
 * Block patterns support.
 */
add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'muhamed-ahmed-astran', get_stylesheet_directory() . '/languages' );
		add_theme_support( 'block-patterns' );
	}
);

/**
 * Ensure html lang/dir come from WordPress, never a hardcoded lang="en".
 *
 * @param string $output Existing language attributes.
 * @return string
 */
function muhamedahmed_astran_language_attributes( $output ) {
	$output = is_string( $output ) ? $output : '';

	if ( false === strpos( $output, 'dir=' ) ) {
		$output = trim( $output . ' dir="' . ( is_rtl() ? 'rtl' : 'ltr' ) . '"' );
	}

	return $output;
}
add_filter( 'language_attributes', 'muhamedahmed_astran_language_attributes' );
