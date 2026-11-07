<?php
/**
 * Asset loading for the v1.8 views; every other
 * page keeps the 1.7 stylesheets untouched.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		register_nav_menus(
			array(
				'mx-primary'  => __( 'v1.8 header', 'muhamed-ahmed-astran' ),
				'mx-footer'   => __( 'Footer column 1', 'muhamed-ahmed-astran' ),
				'mx-footer-2' => __( 'Footer column 2', 'muhamed-ahmed-astran' ),
				'mx-footer-3' => __( 'Footer column 3', 'muhamed-ahmed-astran' ),
				'mx-legal'    => __( 'Footer bottom line (privacy, terms)', 'muhamed-ahmed-astran' ),
			)
		);
		/* Sharp device screenshots and portrait: proportional, never cropped or upscaled. */
		add_image_size( 'mx-shot-desktop', 1600, 0, false );
		add_image_size( 'mx-shot-mobile', 780, 1688, false );
		add_image_size( 'mx-portrait', 1200, 1500, false );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
		add_theme_support( 'post-thumbnails' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! mahx_is_view() ) {
			return;
		}
		$ver = static function ( $rel ) {
			$path = MUHAMEDAHMED_ASTRAN_DIR . $rel;
			return file_exists( $path ) ? (string) filemtime( $path ) : MUHAMEDAHMED_ASTRAN_VERSION;
		};
		/* The v1.8 views do not use Astra's chrome; drop the 1.7 sheets there. */
		foreach ( array( 'design-base', 'design-token', 'design-page', 'design-chrome', 'design-override', 'mah-chrome' ) as $h ) {
			wp_dequeue_style( $h );
			wp_dequeue_script( $h );
		}
		wp_enqueue_style( 'mx-tokens', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/tokens.css', array(), $ver( '/assets/v18/tokens.css' ) );
		wp_enqueue_style( 'mx-home', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/home.css', array( 'mx-tokens' ), $ver( '/assets/v18/home.css' ) );
		wp_add_inline_style(
			'mx-home',
			sprintf(
				':root{--mx-fade-min:%1$s;--mx-reveal-from:%2$s}',
				esc_attr( (string) ( max( 85, min( 100, (int) mahx_opt( 'motion_fade_min' ) ) ) / 100 ) ),
				esc_attr( (string) ( max( 0, min( 100, (int) mahx_opt( 'motion_reveal_from' ) ) ) / 100 ) )
			)
		);
		if ( mahx_designed_page() ) {
			if ( 'page' === mahx_designed_page() || 'about' === mahx_designed_page() ) {
				wp_enqueue_style( 'mx-blog', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/blog.css', array( 'mx-home' ), $ver( '/assets/v18/blog.css' ) );
			}
			wp_enqueue_style( 'mx-pages', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/pages.css', array( 'mx-home' ), $ver( '/assets/v18/pages.css' ) );
			if ( in_array( mahx_designed_page(), array( 'services', 'about' ), true ) ) {
				wp_enqueue_style( 'mx-editorial', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/editorial-pages.css', array( 'mx-pages' ), $ver( '/assets/v18/editorial-pages.css' ) );
			}
			if ( 'services' === mahx_designed_page() ) {
				wp_enqueue_script( 'mx-services-paths', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/services-paths.js', array(), $ver( '/assets/v18/services-paths.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
			}
			if ( 'contact' === mahx_designed_page() && mahx_turnstile_on() ) {
				wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'defer' ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- third-party loader is unversioned.
			}
			if ( 'contact' === mahx_designed_page() && mahx_opt( 'cf_enable' ) ) {
				/* The form rows and the country picker. */
				wp_enqueue_style( 'mx-form', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/form.css', array( 'mx-pages' ), $ver( '/assets/v18/form.css' ) );
				if ( mahx_opt( 'cf_phone' ) ) {
					$iti_css = '/assets/v18/vendor/intl-tel-input/css/intlTelInput.min.css';
					$iti_js  = '/assets/v18/vendor/intl-tel-input/js/intlTelInput.min.js';
					$form_js = '/assets/v18/form.js';
					$deps    = array( 'mx-intl-tel' );
					wp_enqueue_style( 'mx-intl-tel', MUHAMEDAHMED_ASTRAN_URI . $iti_css, array(), $ver( $iti_css ) );
					wp_enqueue_script( 'mx-intl-tel', MUHAMEDAHMED_ASTRAN_URI . $iti_js, array(), $ver( $iti_js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
					if ( 'ar' === mahx_contact_lang() ) {
						$ar_js = '/assets/v18/vendor/intl-tel-input/js/ar-countries.js';
						wp_enqueue_script( 'mx-intl-tel-ar', MUHAMEDAHMED_ASTRAN_URI . $ar_js, array(), $ver( $ar_js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
						$deps[] = 'mx-intl-tel-ar';
					}
					wp_enqueue_script( 'mx-form-phone', MUHAMEDAHMED_ASTRAN_URI . $form_js, $deps, $ver( $form_js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
					wp_add_inline_script(
						'mx-form-phone',
						'window.mxPhone = ' . wp_json_encode(
							array(
								'geo'  => mahx_geo_country(),
								'i18n' => mahx_phone_i18n(),
							)
						) . ';',
						'before'
					);
				}
			}
		} elseif ( ! is_front_page() ) {
			wp_enqueue_style( 'mx-blog', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/blog.css', array( 'mx-home' ), $ver( '/assets/v18/blog.css' ) );
			if ( ( is_home() || is_archive() || is_search() ) && in_array( mahx_blog_pagination_mode(), array( 'load_more', 'infinite' ), true ) ) {
				wp_enqueue_script( 'mx-blog-pager', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/blog-pager.js', array(), $ver( '/assets/v18/blog-pager.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
			}
			if ( is_singular( 'post' ) && mahx_opt( 'blog_toc' ) ) {
				wp_enqueue_script( 'mx-blog-toc', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/blog-toc.js', array(), $ver( '/assets/v18/blog-toc.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
			}
		}
		wp_enqueue_script(
			'mx-home',
			MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/home.js',
			array(),
			$ver( '/assets/v18/home.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		if ( is_front_page() ) {
			wp_enqueue_script( 'mx-home-blog-carousel', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/home-blog-carousel.js', array(), $ver( '/assets/v18/home-blog-carousel.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		}
		wp_localize_script(
			'mx-home',
			'mxI18n',
			array(
				'themeAria' => /* translators: %s: current colour mode. */ __( 'Colour mode: %s', 'muhamed-ahmed-astran' ),
				'modes'     => array(
					'system' => __( 'System', 'muhamed-ahmed-astran' ),
					'light'  => __( 'Light', 'muhamed-ahmed-astran' ),
					'dark'   => __( 'Dark', 'muhamed-ahmed-astran' ),
				),
				'switchTo'  => array(
					'light' => __( 'Switch to light mode', 'muhamed-ahmed-astran' ),
					'dark'  => __( 'Switch to dark mode', 'muhamed-ahmed-astran' ),
				),
				'openMenu'  => __( 'Open menu', 'muhamed-ahmed-astran' ),
				'closeMenu' => __( 'Close menu', 'muhamed-ahmed-astran' ),
				'sending'   => __( 'Sending…', 'muhamed-ahmed-astran' ),
				'failed'    => __( 'Something went wrong. Please try again or email me directly.', 'muhamed-ahmed-astran' ),
				'motion'    => mahx_motion(),
			)
		);
	},
	20
);

/* Astra must not print its own header/footer chrome around v1.8 views. */
add_filter(
	'body_class',
	static function ( $c ) {
		if ( ! mahx_is_view() ) {
			return $c;
		}
		/* Astra keys its container/article CSS on these classes; v1.8 views have their own layout. */
		$c   = array_diff( $c, array( 'ast-separate-container', 'ast-plain-container', 'ast-page-builder-template', 'ast-two-container', 'ast-right-sidebar', 'ast-left-sidebar', 'ast-no-sidebar', 'ast-blog-single-style-1', 'ast-single-post' ) );
		$c[] = 'mx-view';
		return array_values( $c );
	}
);

/* Astra's scroll-to-top button duplicates the v1.8 chrome. Uses Astra's own option filter. */
add_filter(
	'astra_get_option_scroll-to-top-enable',
	static function ( $v ) {
		return mahx_is_view() ? false : $v;
	}
);

add_filter(
	'post_class',
	static function ( $c ) {
		if ( ! mahx_is_view() ) {
			return $c;
		}
		return array_values(
			array_filter(
				$c,
				static function ( $cls ) {
					return 0 !== strpos( $cls, 'ast-' );
				}
			)
		);
	},
	99
);

/*
 * The v1.8 views render their own header, footer and sections, so Astra's
 * layout sheet and the panel plugin's 1.7 homepage sheets are dead weight
 * there (about 1.5 s of render-blocking CSS on a mobile Lighthouse run).
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! mahx_is_view() ) {
			return;
		}
		foreach ( (array) apply_filters( 'muhamed_ahmed_astran_v18_dequeue', array( 'astra-theme-css', 'muhamed-ahmed-astran-parent', 'mah-blocks', 'mah-home', 'mah-home-tokens' ) ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	},
	100
);

/* Live preview for palette, mode and look in the Customizer. */
add_action(
	'customize_preview_init',
	static function () {
		wp_enqueue_script( 'mahx-customize-preview', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/customize-preview.js', array( 'customize-preview' ), MUHAMEDAHMED_ASTRAN_VERSION, true );
	}
);

/*
 * Web fonts must not block first paint on v1.8 views: load the Google Fonts
 * sheet as a preload that swaps to a stylesheet, with a noscript fallback.
 * The system font stack in tokens.css renders until the faces arrive.
 */
add_filter(
	'style_loader_tag',
	static function ( $tag, $handle, $href ) {
		if ( 'muhamed-ahmed-fonts' !== $handle || is_admin() || ! mahx_is_view() ) {
			return $tag;
		}
		return sprintf(
			'<link rel="preload" as="style" id="muhamed-ahmed-fonts-css" href="%1$s" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n" . '<noscript><link rel="stylesheet" href="%1$s"></noscript>' . "\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- rewrites the enqueued tag.
			esc_url( $href )
		);
	},
	10,
	3
);

/*
 * Primary navigation on v1.8 views: drop Astra's injected arrow span and
 * toggle button (unstyled there) and add one accessible sub-menu toggle.
 */
add_filter(
	'wp_nav_menu',
	static function ( $html, $args ) {
		if ( empty( $args->mahx_nav ) ) {
			return $html;
		}
		$html = preg_replace( '#<span role="button" class="dropdown-menu-toggle[^"]*"[^>]*></span>#', '', $html );
		$html = preg_replace( '#<button class="ast-menu-toggle".*?</button>#s', '', $html );
		$html = preg_replace( '#(<a\b[^>]*?) aria-expanded="false"#', '$1', $html );
		$btn  = '<button type="button" class="mx-sub-toggle" aria-expanded="false" aria-label="' . esc_attr__( 'Show submenu', 'muhamed-ahmed-astran' ) . '"><svg width="12" height="12" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
		return str_replace( '<ul class="sub-menu">', $btn . '<ul class="sub-menu">', $html );
	},
	20,
	2
);
