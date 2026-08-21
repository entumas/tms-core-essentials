<?php
/**
 * Includes -> Settings -> Content -> Security
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_security_render_security_panel(): void {
	$settings = tcres_settings_get();
	$security = isset( $settings['security'] ) && is_array( $settings['security'] )
		? $settings['security']
		: array();
	$option   = TCRES_OPTION_NAME;
	$items    = array(
		array(
			'id'          => 'tcres-security-hide-login-errors',
			'key'         => 'hide_login_errors',
			'title'       => __( 'Hide login error messages', 'tms-core-essentials' ),
			'description' => __( 'Replaces detailed login errors with a generic message.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-security-block-user-enumeration',
			'key'         => 'block_user_enumeration',
			'title'       => __( 'Block user enumeration', 'tms-core-essentials' ),
			'description' => __( 'Blocks ?author= requests, canonical redirects, and public REST user endpoints that expose user IDs.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-security-block-author-archives',
			'key'         => 'block_author_archives',
			'title'       => __( 'Block author archives', 'tms-core-essentials' ),
			'description' => __( 'Redirects anonymous visitors away from /author/ archive pages.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-security-disable-xmlrpc',
			'key'         => 'disable_xmlrpc',
			'title'       => __( 'Disable XML-RPC', 'tms-core-essentials' ),
			'description' => __( 'Disables the XML-RPC API if your site does not rely on remote publishing or integrations.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-security-block-proxy-visits',
			'key'         => 'block_proxy_visits',
			'title'       => __( 'Block proxy visits', 'tms-core-essentials' ),
			'description' => __( 'Blocks anonymous visitors when common proxy headers are present.', 'tms-core-essentials' ),
		),
	);
	$total          = count( $items );
	$active_count   = tcres_settings_cards_count_active( $items, $security );
	$inactive_count = $total - $active_count;
	$card_filter    = tcres_settings_get_current_card_filter( 'security' );
	?>

		<?php
		tcres_settings_switch_render_field(
			'tcres-security-enable',
			$option . '[security][enable]',
			! empty( $security['enable'] ),
			array(
				'label'       => __( 'Security', 'tms-core-essentials' ),
				'heading_tag' => 'h2',
				'text_first'  => true,
				'is_title'    => true,
			)
		);
		?>
		<?php
		tcres_settings_cards_panel_render_start( 'security' );
		tcres_settings_cards_render_filter(
			'security',
			'security',
			$card_filter,
			$total,
			$active_count,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Security options', 'tms-core-essentials' ) );
		foreach ( $items as $item ) :
			tcres_settings_card_render_field(
				$item['id'],
				$option . '[security][' . $item['key'] . ']',
				! empty( $security[ $item['key'] ] ),
				$item['title'],
				$item['description']
			);
		endforeach;
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


function tcres_settings_security_render_performance_panel(): void {
	$settings    = tcres_settings_get();
	$performance = isset( $settings['performance'] ) && is_array( $settings['performance'] )
		? $settings['performance']
		: array();
	$option      = TCRES_OPTION_NAME;
	$items       = array(
		array(
			'id'          => 'tcres-performance-remove-wp-version',
			'key'         => 'remove_wp_version',
			'title'       => __( 'Remove WordPress version', 'tms-core-essentials' ),
			'description' => __( 'Removes the generator meta tag and strips ?ver= only when it matches the WordPress version.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-resource-hints',
			'key'         => 'remove_resource_hints',
			'title'       => __( 'Remove DNS prefetch', 'tms-core-essentials' ),
			'description' => __( 'Removes dns-prefetch links from the head. Preconnect and preload hints are kept.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-rsd',
			'key'         => 'remove_rsd',
			'title'       => __( 'Remove RSD', 'tms-core-essentials' ),
			'description' => __( 'Removes Really Simple Discovery links used for automatic pingbacks.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-wlwmanifest',
			'key'         => 'remove_wlwmanifest',
			'title'       => __( 'Remove WLW manifest', 'tms-core-essentials' ),
			'description' => __( 'Removes the wlwmanifest.xml link used by Windows Live Writer.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-shortlink',
			'key'         => 'remove_shortlink',
			'title'       => __( 'Remove shortlink', 'tms-core-essentials' ),
			'description' => __( 'Removes the shortlink URL from the document head and the shortlink HTTP header.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-rest-api-link',
			'key'         => 'remove_rest_api_link',
			'title'       => __( 'Remove REST API link', 'tms-core-essentials' ),
			'description' => __( 'Removes the REST API link from the document head and the REST HTTP header.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-oembed-discovery',
			'key'         => 'remove_oembed_discovery',
			'title'       => __( 'Remove oEmbed discovery', 'tms-core-essentials' ),
			'description' => __( 'Removes oEmbed discovery links from the document head.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-remove-emoji',
			'key'         => 'remove_emoji',
			'title'       => __( 'Remove emoji scripts', 'tms-core-essentials' ),
			'description' => __( 'Disables emoji scripts and styles in the frontend, admin, block editor, RSS, email, and the classic editor.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-performance-disable-wp-embed',
			'key'         => 'disable_wp_embed',
			'title'       => __( 'Disable wp-embed script', 'tms-core-essentials' ),
			'description' => __( 'Stops loading the wp-embed script on the frontend when you do not need WordPress embeds.', 'tms-core-essentials' ),
		),
	);
	$total          = count( $items );
	$active_count   = tcres_settings_cards_count_active( $items, $performance );
	$inactive_count = $total - $active_count;
	$card_filter    = tcres_settings_get_current_card_filter( 'performance' );
	?>

		<?php
		tcres_settings_switch_render_field(
			'tcres-performance-enable',
			$option . '[performance][enable]',
			! empty( $performance['enable'] ),
			array(
				'label'       => __( 'Performance', 'tms-core-essentials' ),
				'heading_tag' => 'h2',
				'text_first'  => true,
				'is_title'    => true,
			)
		);
		?>
		<?php
		tcres_settings_cards_panel_render_start( 'performance' );
		tcres_settings_cards_render_filter(
			'security',
			'performance',
			$card_filter,
			$total,
			$active_count,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Performance options', 'tms-core-essentials' ) );
		foreach ( $items as $item ) :
			tcres_settings_card_render_field(
				$item['id'],
				$option . '[performance][' . $item['key'] . ']',
				! empty( $performance[ $item['key'] ] ),
				$item['title'],
				$item['description']
			);
		endforeach;
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


function tcres_settings_security_render_tab_fields(): void {
	tcres_settings_security_render_security_panel();
	tcres_settings_security_render_performance_panel();
}
