<?php
/**
 * WPML REST helpers used by publish, plus persistent hreflang settings.
 *
 * Route contract: POST mah/v1/wpml-link with JSON `{src, tr}` (EN id, AR id).
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'rest_api_init',
	static function () {
		$perm = 'mahx_rest_can_manage';
		register_rest_route(
			'mah/v1',
			'/wpml-info',
			array(
				'methods'             => 'GET',
				'permission_callback' => $perm,
				'callback'            => 'mahx_wpml_info',
				'args'                => array(
					'ids' => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);
		register_rest_route(
			'mah/v1',
			'/wpml-link',
			array(
				'methods'             => 'POST',
				'permission_callback' => $perm,
				'callback'            => 'mahx_wpml_link',
				'args'                => array(
					'src' => array(
						'required' => true,
						'type'     => 'integer',
					),
					'tr'  => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);
		register_rest_route(
			'mah/v1',
			'/flush',
			array(
				'methods'             => 'POST',
				'permission_callback' => $perm,
				'callback'            => static function () {
					flush_rewrite_rules( false );
					return 'ok';
				},
			)
		);
		register_rest_route(
			'mah/v1',
			'/st',
			array(
				'methods'             => 'POST',
				'permission_callback' => $perm,
				'callback'            => 'mahx_wpml_st',
			)
		);
		register_rest_route(
			'mah/v1',
			'/seo-harden',
			array(
				'methods'             => 'POST',
				'permission_callback' => $perm,
				'callback'            => 'mahx_seo_harden',
			)
		);
	}
);

/**
 * Translation rows for a comma-separated list of post IDs.
 *
 * @param WP_REST_Request $request Request.
 * @return array
 */
function mahx_wpml_info( $request ) {
	global $wpdb;
	$out = array();
	$ids = (string) $request['ids'];
	foreach ( explode( ',', $ids ) as $id ) {
		$id  = (int) $id;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type LIKE 'post_%%'",
				$id
			),
			ARRAY_A
		);
		$sib = array();
		if ( $row && ! empty( $row['trid'] ) ) {
			$sib = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT element_id, language_code, source_language_code FROM {$wpdb->prefix}icl_translations WHERE trid=%d",
					$row['trid']
				),
				ARRAY_A
			);
		}
		$out[ $id ] = array(
			'row'      => $row,
			'siblings' => $sib,
			'link'     => get_permalink( $id ),
		);
	}
	return $out;
}

/**
 * Link an Arabic translation (`tr`) to an English source (`src`) on the same trid.
 *
 * @param WP_REST_Request $request Request with src + tr.
 * @return array
 */
function mahx_wpml_link( $request ) {
	$src  = (int) $request['src'];
	$tr   = (int) $request['tr'];
	$type = 'post_' . get_post_type( $src );
	$trid = apply_filters( 'wpml_element_trid', null, $src, $type );
	do_action(
		'wpml_set_element_language_details',
		array(
			'element_id'           => $tr,
			'element_type'         => $type,
			'trid'                 => $trid,
			'language_code'        => 'ar',
			'source_language_code' => 'en',
		)
	);
	clean_post_cache( $tr );
	return array(
		'trid' => $trid,
		'link' => get_permalink( $tr ),
	);
}

/**
 * List or write WPML admin-text string translations.
 *
 * @param WP_REST_Request $request Request.
 * @return array|int
 */
function mahx_wpml_st( $request ) {
	global $wpdb;
	$table = $wpdb->prefix . 'icl_strings';
	if ( $request['list'] ) {
		return $wpdb->get_results(
			"SELECT id, context, name, value FROM {$table} WHERE context LIKE 'admin_texts_m%'",
			ARRAY_A
		);
	}
	$n = 0;
	if ( ! function_exists( 'icl_add_string_translation' ) ) {
		return $n;
	}
	foreach ( (array) $request['items'] as $it ) {
		if ( ! is_array( $it ) || empty( $it['id'] ) ) {
			continue;
		}
		icl_add_string_translation( (int) $it['id'], 'ar', (string) $it['v'], 10 );
		++$n;
	}
	return $n;
}

/**
 * Turn WPML hreflang output on and flush caches. Idempotent.
 *
 * @return array
 */
function mahx_seo_harden() {
	global $sitepress;
	$report   = array();
	$settings = get_option( 'icl_sitepress_settings' );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	if ( empty( $settings['seo'] ) || ! is_array( $settings['seo'] ) ) {
		$settings['seo'] = array();
	}
	$report['seo_before']                      = $settings['seo'];
	$settings['seo']['head_langs']             = 1;
	$settings['seo']['canonicalization_duplicates'] = 1;
	$settings['seo']['head_langs_priority']    = 1;
	update_option( 'icl_sitepress_settings', $settings );
	if ( isset( $sitepress ) && method_exists( $sitepress, 'set_setting' ) ) {
		$sitepress->set_setting( 'seo', $settings['seo'], true );
		if ( method_exists( $sitepress, 'save_settings' ) ) {
			$sitepress->save_settings();
		}
	}
	$after = get_option( 'icl_sitepress_settings' );
	$report['seo_after'] = is_array( $after ) && isset( $after['seo'] ) ? $after['seo'] : null;
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
	if ( class_exists( 'FlyingPress\\Purge' ) && method_exists( 'FlyingPress\\Purge', 'purge_everything' ) ) {
		\FlyingPress\Purge::purge_everything();
		$report['cache'] = 'flyingpress_purged';
	}
	update_option( 'mah_seo_harden_report', $report, false );
	return rest_ensure_response( $report );
}

/**
 * Keep WPML hreflang flags on without a one-shot REST call.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! mahx_flag( 'seo_hreflang', true ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = get_option( 'icl_sitepress_settings' );
		if ( ! is_array( $settings ) ) {
			return;
		}
		if ( empty( $settings['seo'] ) || ! is_array( $settings['seo'] ) ) {
			$settings['seo'] = array();
		}
		$need = array(
			'head_langs'                  => 1,
			'canonicalization_duplicates' => 1,
			'head_langs_priority'         => 1,
		);
		$dirty = false;
		foreach ( $need as $k => $v ) {
			if ( ! isset( $settings['seo'][ $k ] ) || (int) $settings['seo'][ $k ] !== $v ) {
				$settings['seo'][ $k ] = $v;
				$dirty                 = true;
			}
		}
		if ( ! $dirty ) {
			return;
		}
		update_option( 'icl_sitepress_settings', $settings );
		global $sitepress;
		if ( isset( $sitepress ) && method_exists( $sitepress, 'set_setting' ) ) {
			$sitepress->set_setting( 'seo', $settings['seo'], true );
		}
	},
	20
);
