<?php
/**
 * Data access for the v1.8 views.
 *
 * The panel plugin owns the content (projects, services, reviews, identity,
 * stats, portrait). When it is active its own query helpers are used so the
 * Featured / Priority / hide-unnamed rules stay in one place. Plain WordPress
 * queries are the fallback so the theme never fatals without the plugin.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

/**
 * Projects for the device stage: featured first, then priority.
 *
 * @return WP_Post[]
 */
function mahx_projects() {
	$count = max( 1, min( 16, (int) mahx_opt( 'work_count' ) ) );
	if ( function_exists( 'muhamedahmed_projects_ordered' ) ) {
		$posts = muhamedahmed_projects_ordered( $count );
	} else {
		$posts = get_posts(
			array(
				'post_type'        => 'mah_project',
				'posts_per_page'   => $count,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'suppress_filters' => false,
			)
		);
	}
	return apply_filters( 'muhamed_ahmed_astran_projects', $posts );
}

/**
 * Services, ordered by menu order.
 *
 * @return WP_Post[]
 */
function mahx_services() {
	return apply_filters(
		'muhamed_ahmed_astran_services',
		get_posts(
			array(
				'post_type'        => 'mah_service',
				'posts_per_page'   => 12,
				'orderby'          => array(
					'menu_order' => 'ASC',
					'date'       => 'ASC',
				),
				'suppress_filters' => false,
			)
		)
	);
}

/**
 * Reviews; a review without a name or quote is never shown.
 *
 * @return WP_Post[]
 */
function mahx_reviews() {
	$order = (string) mahx_opt( 'rev_order' );
	$count = max( 1, min( 24, (int) mahx_opt( 'rev_count' ) ) );

	if ( function_exists( 'muhamedahmed_reviews_ordered' ) ) {
		$posts = muhamedahmed_reviews_ordered(
			array(
				'count' => $count,
				'order' => $order,
			)
		);
	} else {
		$posts = get_posts(
			array(
				'post_type'        => 'mah_review',
				'posts_per_page'   => $count,
				'suppress_filters' => false,
				'orderby'          => 'random' === $order ? 'rand' : array( 'date' => 'DESC' ),
			)
		);
	}

	$posts = array_filter(
		(array) $posts,
		static function ( $p ) {
			return '' !== mahx_review_name( $p ) && '' !== trim( wp_strip_all_tags( $p->post_content ) );
		}
	);
	return apply_filters( 'muhamed_ahmed_astran_reviews', array_values( $posts ) );
}

/**
 * Reviewer name: panel author field, else the post title.
 *
 * @param WP_Post $p Review.
 * @return string
 */
function mahx_review_name( $p ) {
	$name = trim( (string) get_post_meta( $p->ID, '_mah_author', true ) );
	return '' !== $name ? $name : trim( get_the_title( $p ) );
}

/**
 * Stats from the panel (Site Content > Stats).
 *
 * Numbers are claims, so they are public only after the owner ticks
 * "Stats verified". Until then editors see them flagged; visitors see nothing.
 *
 * @return array<int,array{value:string,label:string}>
 */
function mahx_stats() {
	$rows = function_exists( 'muhamedahmed_stats' ) ? (array) muhamedahmed_stats() : array();
	if ( ! mahx_opt( 'stats_verified' ) && ! current_user_can( 'edit_theme_options' ) ) {
		return array();
	}
	return array_values(
		array_filter(
			$rows,
			static function ( $r ) {
				return is_array( $r ) && '' !== trim( (string) ( $r['value'] ?? '' ) ) && '' !== trim( (string) ( $r['label'] ?? '' ) );
			}
		)
	);
}

/**
 * Identity field from the panel, if available.
 *
 * @param string $key Identity key (full_name, role, email, tagline...).
 * @return string
 */
function mahx_identity( $key ) {
	return function_exists( 'muhamedahmed_identity' ) ? trim( (string) muhamedahmed_identity( $key ) ) : '';
}

/**
 * Theme option, else the matching panel identity field.
 *
 * @param string $opt Theme option key.
 * @param string $id  Identity key.
 * @return string
 */
function mahx_text( $opt, $id = '' ) {
	$v = trim( (string) mahx_opt( $opt ) );
	return ( '' === $v && $id ) ? mahx_identity( $id ) : $v;
}

/**
 * Portrait attachment: theme option, panel hero image, panel portrait, site icon.
 *
 * @return int
 */
function mahx_portrait_id() {
	$ids   = array( (int) mahx_opt( 'portrait' ) );
	$ids[] = (int) get_option( 'mah_home_hero_image', 0 );
	$ids[] = function_exists( 'muhamedahmed_portrait_id' ) ? (int) muhamedahmed_portrait_id() : (int) get_option( 'mah_portrait_id', 0 );
	$ids[] = (int) get_option( 'site_icon' );
	foreach ( $ids as $id ) {
		if ( $id && wp_attachment_is_image( $id ) ) {
			return $id;
		}
	}
	return 0;
}

/**
 * Template part with args (WP 5.5+).
 *
 * @param string $slug Part.
 * @param array  $args Args.
 */
function mahx_part( $slug, $args = array() ) {
	get_template_part( 'template-parts/' . $slug, null, $args );
}

/**
 * Arrow icon (decorative).
 *
 * @return string
 */
function mahx_arrow() {
	return '<svg class="mx-btn__arrow" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M2 8h11M9 3.5 13.5 8 9 12.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Is this request rendered by the v1.8 templates?
 *
 * @return bool
 */
function mahx_is_view() {
	if ( is_front_page() ) {
		return 'plugin' !== mahx_opt( 'home_engine' ) || ! function_exists( 'muhamedahmed_render_home' );
	}
	if ( mahx_designed_page() ) {
		return true;
	}
	return is_home() || is_singular( 'post' ) || is_search() || is_404() || ( is_archive() && ! is_post_type_archive() && ! is_tax() );
}

/**
 * Screenshot for a project device.
 *
 * Desktop: the project's MacBook screenshot, else its featured image.
 * Mobile: the phone screenshot, else the desktop one, else the featured image.
 *
 * @param WP_Post $p      Project.
 * @param string  $device desktop|mobile.
 * @return int Attachment ID or 0.
 */
function mahx_project_shot( $p, $device = 'desktop' ) {
	$keys = array( '_thumbnail_id', '_mah_desktop_shot' );
	if ( 'mobile' === $device ) {
		$keys = array_merge( array( '_mah_image_mobile', '_mah_mobile_shot' ), $keys );
	}
	foreach ( $keys as $k ) {
		$id = (int) get_post_meta( $p->ID, $k, true );
		if ( $id && wp_attachment_is_image( $id ) ) {
			return $id;
		}
	}
	return 0;
}

/**
 * Focal point (CSS object-position) for a project's screenshots.
 *
 * @param WP_Post $p Project.
 * @return string
 */
function mahx_project_focus( $p ) {
	$f = trim( (string) get_post_meta( $p->ID, '_mah_shot_focus', true ) );
	return preg_match( '/^\d{1,3}(\.\d+)?%\s+\d{1,3}(\.\d+)?%$/', $f ) ? $f : '50% 0%';
}

/**
 * Alt text for a project screenshot: field, then media library alt, then title.
 *
 * @param WP_Post $p      Project.
 * @param string  $device desktop|mobile.
 * @return string
 */
function mahx_project_alt( $p, $device = 'desktop' ) {
	$alt = trim( (string) get_post_meta( $p->ID, '_mah_' . $device . '_alt', true ) );
	if ( '' === $alt ) {
		$alt = trim( (string) get_post_meta( mahx_project_shot( $p, $device ), '_wp_attachment_image_alt', true ) );
	}
	return '' !== $alt ? $alt : get_the_title( $p );
}

/**
 * Which designed page this request is: 'services', 'contact' or ''.
 *
 * A page gets the design when its template is "MX Services"/"MX Contact",
 * or its slug matches the option, and the page design is switched on.
 * The page content in the database is never touched, so switching the
 * option off shows the original editor content again.
 *
 * @return string
 */
function mahx_designed_page() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = '';
	if ( ! is_page() ) {
		return $cache;
	}
	$id   = get_queried_object_id();
	$tpl  = (string) get_page_template_slug( $id );
	$slug = (string) get_post_field( 'post_name', $id );
	foreach ( array(
		'services' => array( 'svcp', 'page-services.php' ),
		'contact'  => array( 'ctp', 'page-contact.php' ),
		'about'    => array( 'abp', 'page-about.php' ),
		'faq'      => array( 'faqp', 'page-faq.php' ),
	) as $kind => $d ) {
		if ( ! mahx_opt( $d[0] . '_enable' ) ) {
			continue;
		}
		$want = sanitize_title( (string) mahx_opt( $d[0] . '_slug' ) );
		if ( $d[1] === $tpl || ( '' !== $want && $want === $slug && ( '' === $tpl || 'default' === $tpl ) ) ) {
			$cache = $kind;
			break;
		}
	}
	if ( '' === $cache && mahx_opt( 'pages_mx' ) && ! is_front_page() && ( '' === $tpl || 'default' === $tpl ) && ! muhamedahmed_astran_is_designed_page() ) {
		$cache = 'page';
	}
	return $cache;
}

/**
 * Page content without blocks or shortcodes whose plugin is not active
 * (old Spectra layouts, WooCommerce shortcodes), so nothing renders raw.
 *
 * @param int $id Page ID.
 * @return string Safe HTML, or '' when nothing usable is left.
 */
function mahx_page_content( $id ) {
	$raw = (string) get_post_field( 'post_content', $id );
	$raw = preg_replace_callback(
		'/\[\/?([a-z0-9_\-]+)[^\]]*\]/i',
		static function ( $m ) {
			return shortcode_exists( $m[1] ) ? $m[0] : '';
		},
		$raw
	);
	if ( function_exists( 'parse_blocks' ) && false !== strpos( $raw, '<!-- wp:' ) ) {
		$keep = '';
		foreach ( parse_blocks( $raw ) as $b ) {
			if ( ! $b['blockName'] || WP_Block_Type_Registry::get_instance()->is_registered( $b['blockName'] ) ) {
				$keep .= serialize_block( $b );
			}
		}
		$raw = $keep;
	}
	$html = apply_filters( 'the_content', $raw ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core hook.
	return '' === trim( wp_strip_all_tags( (string) $html ) ) ? '' : (string) $html;
}

/**
 * Split "Left | right" lines from a textarea option.
 *
 * @param string $key Option key.
 * @return array<int,array{0:string,1:string}>
 */
function mahx_pairs( $key ) {
	$out = array();
	foreach ( mahx_lines( $key ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' !== $parts[0] ) {
			$out[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
		}
	}
	return $out;
}

/**
 * Lines "A | B | C" → array of [a,b,c] (missing parts become '').
 *
 * @param string $key Option key.
 * @return array<int, array{0:string,1:string,2:string}>
 */
function mahx_triples( $key ) {
	$out = array();
	foreach ( mahx_lines( $key ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 3 ) );
		if ( '' === $parts[0] ) {
			continue;
		}
		$out[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '', isset( $parts[2] ) ? $parts[2] : '' );
	}
	return $out;
}


/**
 * One logo image tag with intrinsic width/height and srcset.
 *
 * @param int    $id    Attachment ID.
 * @param string $extra Extra class.
 * @param bool   $eager Header logo: eager + high priority.
 * @return string
 */
function mahx_logo_img( $id, $extra, $eager ) {
	$attrs = array(
		'class'    => 'mx-logo ' . $extra,
		'alt'      => '',
		'loading'  => $eager ? 'eager' : 'lazy',
		'decoding' => 'async',
	);
	if ( $eager ) {
		$attrs['fetchpriority'] = 'high';
	}
	$html = wp_get_attachment_image( $id, 'full', false, $attrs );
	if ( '' === $html && 'image/svg+xml' === get_post_mime_type( $id ) ) {
		/* SVG uploaded through an SVG-sanitising plugin: no intrinsic size in metadata. */
		$html = sprintf( '<img class="%1$s" src="%2$s" alt="" height="%3$d" loading="%4$s" decoding="async">', esc_attr( 'mx-logo ' . $extra ), esc_url( (string) wp_get_attachment_url( $id ) ), (int) mahx_opt( 'logo_h' ), $eager ? 'eager' : 'lazy' );
	}
	return $html;
}

/**
 * Brand link: light/dark logos or a wordmark. Alt text lives on the link.
 *
 * @param bool $eager Header (true) or footer (false).
 * @return string Safe HTML.
 */
function mahx_brand( $eager = true ) {
	$light = (int) mahx_opt( 'logo_light' );
	$light = $light ? $light : (int) get_theme_mod( 'custom_logo' );
	$dark  = (int) mahx_opt( 'logo_dark' );
	$name  = get_bloginfo( 'name' );
	$inner = '';
	if ( $light && ( wp_attachment_is_image( $light ) || 'image/svg+xml' === get_post_mime_type( $light ) ) ) {
		$inner = mahx_logo_img( $light, $dark ? 'mx-logo--light' : '', $eager );
		if ( $dark ) {
			$inner .= mahx_logo_img( $dark, 'mx-logo--dark', $eager );
		}
	}
	if ( '' === $inner ) {
		$inner = '<span class="mx-brand__dot" aria-hidden="true"></span><span class="mx-brand__name">' . esc_html( $name ) . '</span>';
	}
	return sprintf(
		'<a class="mx-brand mx-brand--fix-' . ( $dark ? 'none' : sanitize_html_class( (string) mahx_opt( 'logo_dark_fix' ) ) ) . '" href="%1$s" rel="home" aria-label="%2$s" style="--logo-h:%3$dpx;--logo-h-m:%4$dpx">%5$s</a>',
		esc_url( home_url( '/' ) ),
		esc_attr( $name ),
		max( 16, (int) mahx_opt( 'logo_h' ) ),
		max( 16, (int) mahx_opt( 'logo_h_m' ) ),
		$inner
	);
}

/**
 * Resolved main CTA link. Empty or "#contact" = the designed Contact page
 * (falls back to the homepage contact section); other "#anchors" are
 * made absolute so the header button works from every page.
 *
 * @return string
 */
function mahx_cta_href() {
	$v = trim( (string) mahx_opt( 'cta_url' ) );
	if ( '' === $v || '#contact' === $v ) {
		$pg = get_page_by_path( sanitize_title( (string) mahx_opt( 'ctp_slug' ) ) );
		if ( $pg && 'publish' === $pg->post_status ) {
			return (string) get_permalink( $pg );
		}
		return home_url( '/#contact' );
	}
	if ( 0 === strpos( $v, '#' ) ) {
		return home_url( '/' . $v );
	}
	return $v;
}

/**
 * Public contact email: Contact option, else panel identity.
 *
 * @return string
 */
function mahx_email() {
	if ( ! mahx_opt( 'email_show' ) ) {
		return '';
	}
	return (string) sanitize_email( mahx_text( 'cta_email', 'email' ) );
}

/**
 * Real WhatsApp destination (wa.me). Empty when off or no number.
 *
 * @return string
 */
function mahx_wa_dest_url() {
	if ( ! mahx_opt( 'wa_enable' ) ) {
		return '';
	}
	$n = preg_replace( '/\D+/', '', (string) mahx_opt( 'wa_number' ) );
	$n = '' !== $n ? $n : preg_replace( '/\D+/', '', mahx_identity( 'whatsapp' ) );
	$n = ltrim( (string) $n, '0' );
	if ( strlen( $n ) < 8 || strlen( $n ) > 15 ) {
		return '';
	}
	$t = trim( (string) mahx_opt( 'wa_text' ) );
	return 'https://wa.me/' . $n . ( '' !== $t ? '?text=' . rawurlencode( $t ) : '' );
}

/**
 * Public WhatsApp slug for the vanity path (digits/letters/hyphen only).
 *
 * @return string
 */
function mahx_wa_slug() {
	$slug = sanitize_title( (string) mahx_opt( 'wa_slug' ) );
	return '' !== $slug ? $slug : 'whatsapp';
}

/**
 * WhatsApp chat link for buttons, or '' when off or no number.
 * With vanity on (default), returns /whatsapp so the number stays out of HTML.
 *
 * @return string
 */
function mahx_wa_url() {
	$dest = mahx_wa_dest_url();
	if ( '' === $dest ) {
		return '';
	}
	if ( ! mahx_opt( 'wa_vanity' ) ) {
		return $dest;
	}
	return home_url( user_trailingslashit( mahx_wa_slug() ) );
}

/**
 * Inline WhatsApp glyph (no external request).
 *
 * @return string
 */
function mahx_wa_icon() {
	return '<svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2c0 1.3.9 2.5 1.1 2.7.1.2 1.8 2.8 4.4 3.9 1.6.7 2.3.8 3.1.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3Z"/></svg>';
}

/**
 * Star rating for a review: explicit 1-5, else the default of 5.
 *
 * @param WP_Post $r Review.
 * @return int
 */
function mahx_review_rating( $r ) {
	$v = (int) get_post_meta( $r->ID, '_mah_rating', true );
	return $v >= 1 && $v <= 5 ? $v : 5;
}

/**
 * Stars markup.
 *
 * @param int $n Rating 1-5.
 * @return string
 */
function mahx_stars( $n ) {
	/* translators: %d: rating from 1 to 5. */
	$out = '<span class="mx-stars" role="img" aria-label="' . esc_attr( sprintf( __( 'Rated %d out of 5', 'muhamed-ahmed-astran' ), $n ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$out .= '<svg class="' . ( $i <= $n ? 'is-on' : 'is-off' ) . '" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m12 2.5 2.9 6.1 6.6.8-4.9 4.5 1.3 6.6L12 17.3l-5.9 3.2 1.3-6.6-4.9-4.5 6.6-.8Z"/></svg>';
	}
	return $out . '</span>';
}

/*
 * Route designed About, FAQ and generic pages to the theme's v1.8 templates.
 */
add_filter(
	'template_include',
	static function ( $template ) {
		$map = array(
			'about' => 'page-about.php',
			'faq'   => 'page-faq.php',
			'page'  => 'page-mx.php',
		);
		$k   = is_page() ? mahx_designed_page() : '';
		if ( isset( $map[ $k ] ) ) {
			$t = locate_template( $map[ $k ] );
			return $t ? $t : $template;
		}
		return $template;
	},
	99
);
