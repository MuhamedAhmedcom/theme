<?php
/**
 * Arabic chrome: translate theme/panel strings and menus on /ar/.
 *
 * Map lives in option `mah_ar_chrome_map` (EN → AR). The Codestar textarea
 * `ar_chrome_map` replaces that option when the owner pastes EN | AR lines.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end Arabic request (not wp-admin, not REST).
 *
 * @return bool
 */
function mahx_arc_is_ar() {
	static $busy = false;
	if ( $busy ) {
		return false;
	}
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	$busy = true;
	$on   = mahx_flag( 'ar_chrome', true );
	$busy = false;
	if ( ! $on ) {
		return false;
	}
	if ( ! did_action( 'wpml_loaded' ) && ! did_action( 'plugins_loaded' ) ) {
		return false;
	}
	return 'ar' === apply_filters( 'wpml_current_language', null );
}

/**
 * EN → AR map. Panel textarea wins when filled; otherwise the stored option.
 *
 * @return array<string,string>
 */
function mahx_arc_map() {
	static $m = null;
	if ( null !== $m ) {
		return $m;
	}
	$panel = get_option( 'muhamed_ahmed_panel' );
	$raw   = ( is_array( $panel ) && ! empty( $panel['ar_chrome_map'] ) ) ? $panel['ar_chrome_map'] : '';
	$from_panel = mahx_arc_parse_map( $raw );
	$stored     = (array) get_option( 'mah_ar_chrome_map', array() );
	$m          = $from_panel ? $from_panel : $stored;
	return $m;
}

/**
 * Parse a JSON object, PHP array, or "EN | AR" lines into a map.
 *
 * @param mixed $raw Raw value.
 * @return array<string,string>
 */
function mahx_arc_parse_map( $raw ) {
	if ( is_string( $raw ) ) {
		$trim = trim( $raw );
		if ( '' === $trim ) {
			return array();
		}
		if ( '{' === $trim[0] ) {
			$decoded = json_decode( $trim, true );
			$raw     = is_array( $decoded ) ? $decoded : $trim;
		}
	}
	if ( is_array( $raw ) ) {
		$out = array();
		foreach ( $raw as $k => $v ) {
			if ( is_string( $k ) && is_string( $v ) && '' !== $k && '' !== $v ) {
				$out[ $k ] = $v;
			}
		}
		return $out;
	}
	$out = array();
	foreach ( preg_split( '/\R/u', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line || false === strpos( $line, '|' ) ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' !== $parts[0] && isset( $parts[1] ) && '' !== $parts[1] ) {
			$out[ $parts[0] ] = $parts[1];
		}
	}
	return $out;
}

/**
 * Translate one string through the chrome map.
 *
 * @param mixed $v Value.
 * @return mixed
 */
function mahx_arc_tr( $v ) {
	if ( ! is_string( $v ) || '' === $v || ! preg_match( '/[A-Za-z]/', $v ) ) {
		return $v;
	}
	$map = mahx_arc_map();
	$k   = trim( $v );
	if ( isset( $map[ $k ] ) ) {
		return $map[ $k ];
	}
	if ( false !== strpos( $v, "\n" ) || false !== strpos( $v, ' | ' ) ) {
		$lines = preg_split( '/\r\n|\r|\n/', $v );
		$ch    = false;
		foreach ( $lines as &$line ) {
			$parts = array_map( 'trim', explode( '|', $line ) );
			foreach ( $parts as &$p ) {
				if ( isset( $map[ $p ] ) ) {
					$p  = $map[ $p ];
					$ch = true;
				}
			}
			unset( $p );
			$line = implode( ' | ', $parts );
		}
		unset( $line );
		if ( $ch ) {
			return implode( "\n", $lines );
		}
	}
	return $v;
}

/**
 * Walk arrays and translate string leaves.
 *
 * @param mixed $v Value.
 * @return mixed
 */
function mahx_arc_deep( $v ) {
	if ( is_array( $v ) ) {
		foreach ( $v as $k => $x ) {
			$v[ $k ] = mahx_arc_deep( $x );
		}
		return $v;
	}
	return mahx_arc_tr( $v );
}

add_filter(
	'muhamed_ahmed_astran_option',
	static function ( $v ) {
		return mahx_arc_is_ar() ? mahx_arc_tr( $v ) : $v;
	},
	20
);

add_filter(
	'gettext',
	static function ( $t, $o, $d ) {
		static $busy = false;
		if ( $busy || 'muhamed-ahmed-astran' !== $d ) {
			return $t;
		}
		$busy = true;
		$out  = mahx_arc_is_ar() ? mahx_arc_tr( $t ) : $t;
		$busy = false;
		return $out;
	},
	20,
	3
);

add_filter(
	'ngettext',
	static function ( $t, $s, $p, $n, $d ) {
		static $busy = false;
		if ( $busy || 'muhamed-ahmed-astran' !== $d ) {
			return $t;
		}
		$busy = true;
		$out  = mahx_arc_is_ar() ? mahx_arc_tr( $s ) : $t;
		$busy = false;
		return $out;
	},
	20,
	5
);

foreach ( array(
	'mah_identity',
	'mah_marquee_phrases',
	'mah_process',
	'mah_stats',
	'mah_section_links',
	'mah_nav_about',
	'mah_nav_process',
	'mah_nav_reviews',
	'mah_nav_services',
	'mah_nav_work',
	'mah_home_work_case_label',
) as $mahx_arc_opt ) {
	add_filter(
		'option_' . $mahx_arc_opt,
		static function ( $v ) {
			return mahx_arc_is_ar() ? mahx_arc_deep( $v ) : $v;
		},
		20
	);
}

add_filter(
	'nav_menu_item_title',
	static function ( $t ) {
		return mahx_arc_is_ar() ? mahx_arc_tr( $t ) : $t;
	},
	20
);

add_action(
	'csf_muhamed_ahmed_panel_saved',
	static function ( $data ) {
		if ( ! is_array( $data ) || empty( $data['ar_chrome_map'] ) ) {
			return;
		}
		$parsed = mahx_arc_parse_map( $data['ar_chrome_map'] );
		if ( $parsed ) {
			update_option( 'mah_ar_chrome_map', $parsed, true );
		}
	}
);

add_action(
	'rest_api_init',
	static function () {
		$perm = 'mahx_rest_can_manage';
		register_rest_route(
			'mah/v1',
			'/ar-map',
			array(
				'methods'             => 'POST',
				'permission_callback' => $perm,
				'callback'            => static function ( $request ) {
					$params = $request->get_json_params();
					$map    = isset( $params['map'] ) ? mahx_arc_parse_map( $params['map'] ) : array();
					update_option( 'mah_ar_chrome_map', $map, true );
					return count( $map );
				},
			)
		);
		register_rest_route(
			'mah/v1',
			'/ar-menus',
			array(
				'methods'             => 'POST',
				'permission_callback' => $perm,
				'callback'            => 'mahx_arc_clone_menus',
			)
		);
		register_rest_route(
			'mah/v1',
			'/menu-items',
			array(
				'methods'             => 'GET',
				'permission_callback' => $perm,
				'callback'            => static function () {
					$out = array();
					foreach ( wp_get_nav_menus() as $menu ) {
						$lang  = apply_filters(
							'wpml_element_language_code',
							null,
							array(
								'element_id'   => $menu->term_taxonomy_id,
								'element_type' => 'nav_menu',
							)
						);
						$items = array_map(
							static function ( $i ) {
								return array( $i->title, $i->type, $i->object, $i->object_id, $i->url );
							},
							(array) wp_get_nav_menu_items( $menu->term_id )
						);
						$out[] = array(
							'id'    => $menu->term_id,
							'name'  => $menu->name,
							'lang'  => $lang,
							'items' => $items,
						);
					}
					return $out;
				},
			)
		);
	}
);

/**
 * Clone EN menus into AR, translating titles and rewriting URLs.
 *
 * @param WP_REST_Request $request JSON `{menus:{id:name}, pages:{enId:arId}}`.
 * @return array
 */
function mahx_arc_clone_menus( $request ) {
	$p     = $request->get_json_params();
	$pmap  = isset( $p['pages'] ) ? (array) $p['pages'] : array();
	$out   = array();
	$menus = isset( $p['menus'] ) ? (array) $p['menus'] : array();
	foreach ( $menus as $mid => $arname ) {
		$mid = (int) $mid;
		$src = wp_get_nav_menu_object( $mid );
		if ( ! $src ) {
			$out[ $mid ] = 'nomenu';
			continue;
		}
		$trid = apply_filters( 'wpml_element_trid', null, $src->term_taxonomy_id, 'tax_nav_menu' );
		$tr   = apply_filters( 'wpml_get_element_translations', null, $trid, 'tax_nav_menu' );
		if ( isset( $tr['ar'] ) ) {
			$arid = (int) $tr['ar']->term_id;
			foreach ( (array) wp_get_nav_menu_items( $arid ) as $it ) {
				wp_delete_post( $it->ID, true );
			}
		} else {
			$ex   = wp_get_nav_menu_object( $arname );
			$arid = $ex ? $ex->term_id : wp_create_nav_menu( $arname );
			if ( is_wp_error( $arid ) ) {
				$out[ $mid ] = $arid->get_error_message();
				continue;
			}
		}
		$art = get_term( $arid, 'nav_menu' );
		if ( ! $art || is_wp_error( $art ) ) {
			$out[ $mid ] = 'noterm';
			continue;
		}
		do_action(
			'wpml_set_element_language_details',
			array(
				'element_id'           => $art->term_taxonomy_id,
				'element_type'         => 'tax_nav_menu',
				'trid'                 => $trid,
				'language_code'        => 'ar',
				'source_language_code' => 'en',
			)
		);
		$idmap = array();
		$items = wp_get_nav_menu_items( $mid, array( 'post_status' => 'any' ) );
		foreach ( (array) $items as $it ) {
			$title = '' !== $it->post_title ? $it->post_title : $it->title;
			$args  = array(
				'menu-item-title'     => mahx_arc_tr( $title ),
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => isset( $idmap[ $it->menu_item_parent ] ) ? $idmap[ $it->menu_item_parent ] : 0,
				'menu-item-position'  => $it->menu_order,
				'menu-item-type'      => $it->type,
				'menu-item-object'    => $it->object,
				'menu-item-classes'   => implode( ' ', (array) $it->classes ),
				'menu-item-target'    => $it->target,
				'menu-item-attr-title'=> $it->attr_title,
				'menu-item-xfn'       => $it->xfn,
			);
			if ( 'post_type' === $it->type ) {
				$oid = (int) $it->object_id;
				$ar  = apply_filters( 'wpml_object_id', $oid, $it->object, false, 'ar' );
				if ( isset( $pmap[ $oid ] ) ) {
					$ar = (int) $pmap[ $oid ];
				}
				$args['menu-item-object-id'] = $ar ? $ar : $oid;
				if ( $ar ) {
					$args['menu-item-title'] = mahx_arc_tr( $title ) !== $title ? mahx_arc_tr( $title ) : ( '' !== $it->post_title ? mahx_arc_tr( $title ) : get_the_title( $ar ) );
				}
			} elseif ( 'custom' === $it->type ) {
				$u = $it->url;
				$u = preg_replace( '#^(https?://[^/]+)/(?!ar/)#', '$1/ar/', $u );
				if ( $u === $it->url && 0 === strpos( $u, '/' ) && false === strpos( $u, '/ar/' ) ) {
					$u = '/ar' . $u;
				}
				$args['menu-item-url'] = $u;
			} else {
				$args['menu-item-object-id'] = $it->object_id;
			}
			$nid = wp_update_nav_menu_item( $arid, 0, $args );
			if ( ! is_wp_error( $nid ) ) {
				$idmap[ $it->ID ] = $nid;
				$otrid            = apply_filters( 'wpml_element_trid', null, $it->ID, 'post_nav_menu_item' );
				do_action(
					'wpml_set_element_language_details',
					array(
						'element_id'           => $nid,
						'element_type'         => 'post_nav_menu_item',
						'trid'                 => $otrid,
						'language_code'        => 'ar',
						'source_language_code' => 'en',
					)
				);
			}
		}
		$out[ $mid ] = array(
			'ar_menu' => $arid,
			'items'   => count( $idmap ),
		);
	}
	return $out;
}
