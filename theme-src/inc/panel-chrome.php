<?php
/**
 * Header / fonts / post-hero options for the Muhamed Ahmed (Codestar) panel.
 * Fields live in the panel option "muhamed_ahmed_panel"; the theme reads them
 * here, so the owner edits the chrome without code.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

/** Panel value with default. */
function mahx_pc( $key, $default = '' ) {
	static $o = null;
	if ( null === $o ) {
		$o = get_option( 'muhamed_ahmed_panel' );
		$o = is_array( $o ) ? $o : array();
	}
	return ( isset( $o[ $key ] ) && '' !== $o[ $key ] && null !== $o[ $key ] ) ? $o[ $key ] : $default;
}

/** Current page language: ar|en. */
function mahx_pc_lang() {
	$l = apply_filters( 'wpml_current_language', null );
	if ( ! $l ) {
		$l = substr( get_locale(), 0, 2 );
	}
	return 'ar' === $l ? 'ar' : 'en';
}

/** Header CTA label from the panel, per language. */
function mahx_pc_cta_label() {
	return 'ar' === mahx_pc_lang()
		? (string) mahx_pc( 'mahx_cta_label_ar', 'اطلب عرض سعر' )
		: (string) mahx_pc( 'mahx_cta_label_en', 'Request a quote' );
}

/** Font choices: key => [label, css stack, google family or ''] */
function mahx_pc_fonts( $script ) {
	if ( 'ar' === $script ) {
		return array(
			'ibm-plex' => array( 'IBM Plex Sans Arabic (current, self-hosted)', '"IBM Plex Sans Arabic", "Noto Sans Arabic", "Segoe UI", tahoma, sans-serif', '' ),
			'system'   => array( 'System Arabic (fastest, no download)', '"Segoe UI", "SF Arabic", "Geeza Pro", tahoma, "Noto Sans Arabic", sans-serif', '' ),
			'cairo'    => array( 'Cairo (modern, Google, swap)', 'Cairo, "Segoe UI", tahoma, sans-serif', 'Cairo:wght@400;600;700' ),
			'tajawal'  => array( 'Tajawal (clean, Google, swap)', 'Tajawal, "Segoe UI", tahoma, sans-serif', 'Tajawal:wght@400;500;700' ),
			'noto-kufi'=> array( 'Noto Kufi Arabic (geometric, Google, swap)', '"Noto Kufi Arabic", "Segoe UI", tahoma, sans-serif', 'Noto+Kufi+Arabic:wght@400;600;700' ),
		);
	}
	return array(
		'inter'   => array( 'Inter + K2D headings (current)', '', '' ),
		'system'  => array( 'System UI (fastest, no download)', 'system-ui, -apple-system, "Segoe UI", roboto, sans-serif', '' ),
		'jakarta' => array( 'Plus Jakarta Sans (modern, Google, swap)', '"Plus Jakarta Sans", system-ui, sans-serif', 'Plus+Jakarta+Sans:wght@400;600;700' ),
		'dm-sans' => array( 'DM Sans (friendly, Google, swap)', '"DM Sans", system-ui, sans-serif', 'DM+Sans:wght@400;500;700' ),
		'manrope' => array( 'Manrope (tech, Google, swap)', 'Manrope, system-ui, sans-serif', 'Manrope:wght@400;600;700' ),
	);
}

/** Codestar section. */
function mahx_pc_section() {
	if ( ! class_exists( 'CSF' ) ) {
		return;
	}
	$en = array();
	foreach ( mahx_pc_fonts( 'en' ) as $k => $f ) {
		$en[ $k ] = $f[0];
	}
	$ar = array();
	foreach ( mahx_pc_fonts( 'ar' ) as $k => $f ) {
		$ar[ $k ] = $f[0];
	}
	CSF::createSection(
		'muhamed_ahmed_panel',
		array(
			'id'     => 'mah_chrome',
			'title'  => 'Header, Fonts & Post hero',
			'icon'   => 'fas fa-heading',
			'fields' => array(
				array( 'type' => 'subheading', 'content' => 'Header' ),
				array( 'id' => 'mahx_lang_pill', 'type' => 'switcher', 'title' => 'Language switcher pill (EN | AR)', 'desc' => 'Separate control next to the CTA, never inside the menu links.', 'default' => true ),
				array( 'id' => 'mahx_cta_show', 'type' => 'switcher', 'title' => 'Show header CTA button', 'default' => true ),
				array( 'id' => 'mahx_cta_label_en', 'type' => 'text', 'title' => 'Header CTA label (English)', 'default' => 'Request a quote' ),
				array( 'id' => 'mahx_cta_label_ar', 'type' => 'text', 'title' => 'Header CTA label (Arabic)', 'default' => 'اطلب عرض سعر' ),
				array( 'id' => 'mahx_nav_fx', 'type' => 'select', 'title' => 'Menu link hover effect', 'options' => array( 'line' => 'Bottom line grows 0 → 100% (from start)', 'center' => 'Bottom line grows from the centre', 'pill' => 'Soft pill background', 'none' => 'None' ), 'default' => 'line', 'desc' => 'The current page link always keeps its bottom line.' ),
				array( 'type' => 'subheading', 'content' => 'Fonts' ),
				array( 'id' => 'mahx_font_en', 'type' => 'select', 'title' => 'English font', 'options' => $en, 'default' => 'inter' ),
				array( 'id' => 'mahx_font_ar', 'type' => 'select', 'title' => 'Arabic font', 'options' => $ar, 'default' => 'ibm-plex' ),
				array( 'type' => 'subheading', 'content' => 'Single post hero (Mixed / Editorial presets)' ),
				array( 'id' => 'mahx_post_hero', 'type' => 'select', 'title' => 'Post hero style', 'options' => array( 'overlay' => 'Title on the featured image with dark gradient (recommended)', 'dark' => 'Classic: preset default (dark band / full-bleed cover)' ), 'default' => 'overlay', 'desc' => 'Light preset is never affected.' ),
				array( 'id' => 'mahx_post_overlay', 'type' => 'slider', 'title' => 'Overlay darkness (%)', 'min' => 55, 'max' => 95, 'step' => 5, 'default' => 80, 'desc' => '80+ keeps white text above 4.5:1 on bright photos.' ),
				array( 'type' => 'subheading', 'content' => 'Home review badges (plain text, never linked)' ),
				array( 'id' => 'mahx_rev_agg1_en', 'type' => 'text', 'title' => 'Badge 1 (English)', 'default' => '5 on Trustpilot', 'desc' => 'A ★ is added automatically. Empty hides it.' ),
				array( 'id' => 'mahx_rev_agg1_ar', 'type' => 'text', 'title' => 'Badge 1 (Arabic)', 'default' => '5 على Trustpilot' ),
				array( 'id' => 'mahx_rev_agg2_en', 'type' => 'text', 'title' => 'Badge 2 (English)', 'default' => '5.0 on Upwork' ),
				array( 'id' => 'mahx_rev_agg2_ar', 'type' => 'text', 'title' => 'Badge 2 (Arabic)', 'default' => '5.0 على Upwork' ),
			),
		)
	);
}

/* Review badges: text per language from the panel; profile links never output. */
add_filter(
	'muhamed_ahmed_astran_option',
	static function ( $value, $key ) {
		if ( 'rev_agg1_url' === $key || 'rev_agg2_url' === $key ) {
			return '';
		}
		if ( 'rev_agg1' === $key || 'rev_agg2' === $key ) {
			$d = array(
				'rev_agg1' => array( '5 on Trustpilot', '5 على Trustpilot' ),
				'rev_agg2' => array( '5.0 on Upwork', '5.0 على Upwork' ),
			);
			$ar = 'ar' === mahx_pc_lang();
			$o  = get_option( 'muhamed_ahmed_panel' );
			$id = 'mahx_' . $key . ( $ar ? '_ar' : '_en' );
			if ( is_array( $o ) && array_key_exists( $id, $o ) ) {
				return (string) $o[ $id ];
			}
			return $d[ $key ][ $ar ? 1 : 0 ];
		}
		return $value;
	},
	99,
	2
);
if ( class_exists( 'CSF' ) ) {
	mahx_pc_section();
} else {
	add_action( 'after_setup_theme', 'mahx_pc_section', 0 );
}

/* Header CTA label follows the panel. */
add_filter( 'mahx_cta_label', 'mahx_pc_cta_label' );

/* Assets + font variables. */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$ver = (string) @filemtime( MUHAMEDAHMED_ASTRAN_DIR . '/assets/v18/chrome-x.css' );
		wp_enqueue_style( 'mx-chrome-x', MUHAMEDAHMED_ASTRAN_URI . '/assets/v18/chrome-x.css', array(), $ver );
		$css = ':root{--mx-hero-ov:' . ( (int) mahx_pc( 'mahx_post_overlay', 80 ) / 100 ) . ';}';
		$fam = array();
		$enf = mahx_pc_fonts( 'en' );
		$k   = (string) mahx_pc( 'mahx_font_en', 'inter' );
		if ( isset( $enf[ $k ] ) && '' !== $enf[ $k ][1] ) {
			$css .= ':root{--f-body:' . $enf[ $k ][1] . ';--f-display:' . $enf[ $k ][1] . ';}';
			if ( $enf[ $k ][2] ) {
				$fam[] = $enf[ $k ][2];
			}
		}
		$arf = mahx_pc_fonts( 'ar' );
		$k   = (string) mahx_pc( 'mahx_font_ar', 'ibm-plex' );
		if ( isset( $arf[ $k ] ) && 'ibm-plex' !== $k ) {
			$css .= ':root{--f-arabic:' . $arf[ $k ][1] . ';}html[lang^="ar"] body,html[lang^="ar"] body .wp-block-heading{font-family:var(--f-arabic);}';
			if ( $arf[ $k ][2] && 'ar' === mahx_pc_lang() ) {
				$fam[] = $arf[ $k ][2];
			}
		}
		if ( $fam ) {
			wp_enqueue_style( 'mx-pc-fonts', 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $fam ) . '&display=swap', array(), null ); // phpcs:ignore
		}
		wp_add_inline_style( 'mx-chrome-x', $css );
	},
	30
);

/** Language pill markup. */
function mahx_pc_lang_pill() {
	if ( ! mahx_pc( 'mahx_lang_pill', true ) || ! get_option( 'mah_lang_switcher', 1 ) || 'footer' === get_option( 'mah_lang_switcher_position', 'header' ) || ! function_exists( 'icl_get_languages' ) ) {
		return '';
	}
	$langs = (array) icl_get_languages( 'skip_missing=0&orderby=code&order=desc' );
	if ( count( $langs ) < 2 ) {
		return '';
	}
	/* Show only the alternate language (the one you switch TO). */
	$target = null;
	foreach ( array( 'en', 'ar' ) as $code ) {
		if ( ! empty( $langs[ $code ] ) && empty( $langs[ $code ]['active'] ) ) {
			$target = $code;
			break;
		}
	}
	if ( ! $target ) {
		return '';
	}
	$l     = $langs[ $target ];
	$txt   = 'ar' === $target ? 'AR' : 'EN';
	$flags = array(
		'en' => '<svg class="mx-langsw__flag" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><defs><clipPath id="mxf-us"><circle cx="10" cy="10" r="10"/></clipPath></defs><g clip-path="url(#mxf-us)"><rect width="20" height="20" fill="#fff"/><g fill="#b22234"><rect y="0" width="20" height="1.54"/><rect y="3.08" width="20" height="1.54"/><rect y="6.15" width="20" height="1.54"/><rect y="9.23" width="20" height="1.54"/><rect y="12.31" width="20" height="1.54"/><rect y="15.38" width="20" height="1.54"/><rect y="18.46" width="20" height="1.54"/></g><rect width="9.5" height="10.77" fill="#3c3b6e"/><g fill="#fff"><circle cx="2.2" cy="2.2" r=".6"/><circle cx="4.7" cy="2.2" r=".6"/><circle cx="7.2" cy="2.2" r=".6"/><circle cx="3.45" cy="4.4" r=".6"/><circle cx="5.95" cy="4.4" r=".6"/><circle cx="2.2" cy="6.6" r=".6"/><circle cx="4.7" cy="6.6" r=".6"/><circle cx="7.2" cy="6.6" r=".6"/><circle cx="3.45" cy="8.8" r=".6"/><circle cx="5.95" cy="8.8" r=".6"/></g></g></svg>',
		'ar' => '<svg class="mx-langsw__flag" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><defs><clipPath id="mxf-eg"><circle cx="10" cy="10" r="10"/></clipPath></defs><g clip-path="url(#mxf-eg)"><rect width="20" height="6.67" fill="#ce1126"/><rect y="6.67" width="20" height="6.67" fill="#fff"/><rect y="13.33" width="20" height="6.67" fill="#000"/><circle cx="10" cy="10" r="1.6" fill="#c09300"/></g></svg>',
	);
	return '<div class="mx-langsw mx-langsw--one"><a class="mx-langsw__i" href="' . esc_url( $l['url'] ) . '" hreflang="' . esc_attr( $target ) . '" lang="' . esc_attr( $target ) . '" title="' . esc_attr( $l['native_name'] ) . '" aria-label="' . esc_attr( $l['native_name'] ) . '">' . $flags[ $target ] . '<span class="mx-langsw__t">' . $txt . '</span></a></div>';
}

/** Is the overlay hero on for this post. */
function mahx_pc_overlay_hero() {
	return 'overlay' === mahx_pc( 'mahx_post_hero', 'overlay' ) && has_post_thumbnail();
}

/* Custom-link menu items: mark the current page so the active underline shows. */
add_filter(
	'nav_menu_css_class',
	static function ( $classes, $item ) {
		if ( is_admin() || in_array( 'current-menu-item', (array) $classes, true ) || empty( $item->url ) ) {
			return $classes;
		}
		$req  = trailingslashit( (string) wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ) );
		$path = trailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
		$host = wp_parse_url( $item->url, PHP_URL_HOST );
		if ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) {
			return $classes;
		}
		$home = in_array( $path, array( '/', '/ar/' ), true );
		if ( $req === $path || ( ! $home && 0 === strpos( $req, $path ) ) || ( is_singular( 'post' ) && preg_match( '#/blog/$#', $path ) ) ) {
			$classes[] = 'current-menu-item';
		}
		return $classes;
	},
	10,
	2
);
