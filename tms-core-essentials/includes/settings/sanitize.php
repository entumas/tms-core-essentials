<?php
/**
 * Includes -> Settings -> Sanitize
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Saving and sanitization of the options array
 */
function tcres_settings_sanitize( $value ): array {
	$defaults = tcres_settings_get_defaults();
	$old      = tcres_storage_option_get( TCRES_OPTION_NAME, array() );

	if ( ! is_array( $old ) ) $old = array();

	$out = tcres_settings_merge_defaults( $defaults, $old );
	if ( ! is_array( $value ) ) return $out;

	$submit_tab = tcres_settings_get_submit_tab_from_request();

	if ( $submit_tab === 'security' ) :
		$security = isset( $value['security'] ) && is_array( $value['security'] )
			? $value['security']
			: array();
		$out['security']['enable']                 = ! empty( $security['enable'] );
		$out['security']['hide_login_errors']      = ! empty( $security['hide_login_errors'] );
		$out['security']['block_proxy_visits']     = ! empty( $security['block_proxy_visits'] );
		$out['security']['block_user_enumeration'] = ! empty( $security['block_user_enumeration'] );
		$out['security']['block_author_archives']  = ! empty( $security['block_author_archives'] );
		$out['security']['disable_xmlrpc']         = ! empty( $security['disable_xmlrpc'] );

		$performance = isset( $value['performance'] ) && is_array( $value['performance'] )
			? $value['performance']
			: array();
		$out['performance']['enable']                  = ! empty( $performance['enable'] );
		$out['performance']['remove_wp_version']       = ! empty( $performance['remove_wp_version'] );
		$out['performance']['remove_resource_hints']   = ! empty( $performance['remove_resource_hints'] );
		$out['performance']['remove_rsd']              = ! empty( $performance['remove_rsd'] );
		$out['performance']['remove_wlwmanifest']      = ! empty( $performance['remove_wlwmanifest'] );
		$out['performance']['remove_shortlink']        = ! empty( $performance['remove_shortlink'] );
		$out['performance']['remove_emoji']            = ! empty( $performance['remove_emoji'] );
		$out['performance']['remove_rest_api_link']    = ! empty( $performance['remove_rest_api_link'] );
		$out['performance']['remove_oembed_discovery'] = ! empty( $performance['remove_oembed_discovery'] );
		$out['performance']['disable_wp_embed']        = ! empty( $performance['disable_wp_embed'] );
	endif;

	if ( $submit_tab === 'admin' ) :
		$disable_gutenberg = isset( $value['disable_gutenberg'] ) && is_array( $value['disable_gutenberg'] )
			? $value['disable_gutenberg']
			: array();
		foreach ( tcres_settings_disable_gutenberg_get_post_type_defaults() as $key => $default ) :
			$out['disable_gutenberg'][ $key ] = ! empty( $disable_gutenberg[ $key ] );
		endforeach;

		$disable_features = isset( $value['disable_features'] ) && is_array( $value['disable_features'] )
			? $value['disable_features']
			: array();
		$out['disable_features']['disable_post_types'] = ! empty( $disable_features['disable_post_types'] );
		$out['disable_features']['disable_taxonomies'] = ! empty( $disable_features['disable_taxonomies'] );
		$out['disable_features']['disable_comments']   = ! empty( $disable_features['disable_comments'] );
		$out['disable_features']['clean_admin_bar']    = ! empty( $disable_features['clean_admin_bar'] );

		$disable_feature_post_types = isset( $disable_features['post_types'] ) && is_array( $disable_features['post_types'] )
			? $disable_features['post_types']
			: array();
		$out['disable_features']['post_types'] = array();
		foreach ( tcres_settings_module_post_type_toggles_defaults() as $post_type => $default ) :
			$out['disable_features']['post_types'][ $post_type ] = ! empty( $disable_feature_post_types[ $post_type ] );
		endforeach;

		$disable_feature_taxonomies = isset( $disable_features['taxonomies'] ) && is_array( $disable_features['taxonomies'] )
			? $disable_features['taxonomies']
			: array();
		$out['disable_features']['taxonomies'] = array();
		foreach ( tcres_settings_module_taxonomy_toggles_defaults( '' ) as $taxonomy => $default ) :
			$out['disable_features']['taxonomies'][ $taxonomy ] = ! empty( $disable_feature_taxonomies[ $taxonomy ] );
		endforeach;

		$admin_bar_nodes = isset( $disable_features['admin_bar_nodes'] ) && is_array( $disable_features['admin_bar_nodes'] )
			? $disable_features['admin_bar_nodes']
			: array();
		$out['disable_features']['admin_bar_nodes'] = array();
		foreach ( tcres_settings_admin_bar_role_node_defaults() as $role_key => $node_defaults ) :
			$submitted_nodes = isset( $admin_bar_nodes[ $role_key ] ) && is_array( $admin_bar_nodes[ $role_key ] )
				? $admin_bar_nodes[ $role_key ]
				: array();
			$out['disable_features']['admin_bar_nodes'][ $role_key ] = array();
			foreach ( $node_defaults as $node_id => $default ) :
				$out['disable_features']['admin_bar_nodes'][ $role_key ][ $node_id ] = ! empty( $submitted_nodes[ $node_id ] );
			endforeach;
		endforeach;

		$admin_bar_custom = isset( $disable_features['admin_bar_custom'] ) && is_array( $disable_features['admin_bar_custom'] )
			? $disable_features['admin_bar_custom']
			: array();
		$out['disable_features']['admin_bar_custom'] = array();
		foreach ( tcres_settings_admin_bar_role_custom_defaults() as $role_key => $default ) :
			$raw = isset( $admin_bar_custom[ $role_key ] )
				? (string) $admin_bar_custom[ $role_key ]
				: '';
			$ids = preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
			$ids = is_array( $ids )
				? $ids
				: array();
			$ids = array_values(
				array_unique(
					array_filter(
						array_map(
							static function ( string $id ): string {
								return sanitize_key( $id );
							},
							$ids
						),
						static function ( string $id ): bool {
							return $id !== '';
						}
					)
				)
			);
			$out['disable_features']['admin_bar_custom'][ $role_key ] = implode( "\n", $ids );
		endforeach;

		$posts_list = isset( $value['posts_list'] ) && is_array( $value['posts_list'] )
			? $value['posts_list']
			: array();
		$out['posts_list']['add_thumbnail_column'] = ! empty( $posts_list['add_thumbnail_column'] );
		$out['posts_list']['sortable_columns']     = ! empty( $posts_list['sortable_columns'] );
		$out['posts_list']['drag_order']           = ! empty( $posts_list['drag_order'] );

		$thumbnail_post_types = isset( $posts_list['thumbnail_post_types'] ) && is_array( $posts_list['thumbnail_post_types'] )
			? $posts_list['thumbnail_post_types']
			: array();
		$out['posts_list']['thumbnail_post_types'] = array();
		foreach ( tcres_settings_posts_list_thumbnail_post_type_defaults() as $post_type => $default ) :
			$out['posts_list']['thumbnail_post_types'][ $post_type ] = ! empty( $thumbnail_post_types[ $post_type ] );
		endforeach;

		$sortable_config = isset( $posts_list['sortable_columns_config'] ) && is_array( $posts_list['sortable_columns_config'] )
			? $posts_list['sortable_columns_config']
			: array();
		$out['posts_list']['sortable_columns_config'] = array();
		foreach ( tcres_settings_posts_list_sortable_columns_config_defaults() as $post_type => $columns ) :
			$out['posts_list']['sortable_columns_config'][ $post_type ] = array();
			$submitted = isset( $sortable_config[ $post_type ] ) && is_array( $sortable_config[ $post_type ] )
				? $sortable_config[ $post_type ]
				: array();
			foreach ( array_keys( $columns ) as $column_key ) :
				$out['posts_list']['sortable_columns_config'][ $post_type ][ $column_key ] = ! empty( $submitted[ $column_key ] );
			endforeach;
		endforeach;

		$drag_order_post_types = isset( $posts_list['drag_order_post_types'] ) && is_array( $posts_list['drag_order_post_types'] )
			? $posts_list['drag_order_post_types']
			: array();
		$out['posts_list']['drag_order_post_types'] = array();
		foreach ( tcres_settings_posts_list_drag_order_post_type_defaults() as $post_type => $default ) :
			$out['posts_list']['drag_order_post_types'][ $post_type ] = ! empty( $drag_order_post_types[ $post_type ] );
		endforeach;

		$duplicate = isset( $value['duplicate_posts'] ) && is_array( $value['duplicate_posts'] )
			? $value['duplicate_posts']
			: array();
		$out['duplicate_posts']['enable']              = ! empty( $duplicate['enable'] );
		$out['duplicate_posts']['copy_taxonomies']     = ! empty( $duplicate['copy_taxonomies'] );
		$out['duplicate_posts']['copy_meta']           = ! empty( $duplicate['copy_meta'] );
		$out['duplicate_posts']['copy_featured_image'] = ! empty( $duplicate['copy_featured_image'] );

		$status = isset( $duplicate['status'] )
			? sanitize_key( (string) $duplicate['status'] )
			: 'draft';
		$allowed_statuses = array( 'draft', 'pending', 'private', 'publish' );
		$out['duplicate_posts']['status'] = in_array( $status, $allowed_statuses, true )
			? $status
			: 'draft';

		$dup_post_types = isset( $duplicate['post_types'] ) && is_array( $duplicate['post_types'] )
			? $duplicate['post_types']
			: array();
		$out['duplicate_posts']['post_types'] = array();
		foreach ( tcres_settings_duplicate_posts_post_type_defaults() as $post_type => $default ) :
			$out['duplicate_posts']['post_types'][ $post_type ] = ! empty( $dup_post_types[ $post_type ] );
		endforeach;

		$terms_list = isset( $value['terms_list'] ) && is_array( $value['terms_list'] )
			? $value['terms_list']
			: array();
		$out['terms_list']['drag_order'] = ! empty( $terms_list['drag_order'] );

		$drag_order_taxonomies = isset( $terms_list['drag_order_taxonomies'] ) && is_array( $terms_list['drag_order_taxonomies'] )
			? $terms_list['drag_order_taxonomies']
			: array();
		$out['terms_list']['drag_order_taxonomies'] = array();
		foreach ( tcres_settings_terms_list_drag_order_taxonomy_defaults() as $taxonomy => $default ) :
			$out['terms_list']['drag_order_taxonomies'][ $taxonomy ] = ! empty( $drag_order_taxonomies[ $taxonomy ] );
		endforeach;

		$svg_uploads = isset( $value['svg_uploads'] ) && is_array( $value['svg_uploads'] )
			? $value['svg_uploads']
			: array();
		$out['svg_uploads']['enable'] = ! empty( $svg_uploads['enable'] );

		$roles = isset( $svg_uploads['roles'] ) && is_array( $svg_uploads['roles'] )
			? $svg_uploads['roles']
			: array();
		$out['svg_uploads']['roles'] = array();
		foreach ( tcres_settings_svg_uploads_role_defaults() as $role_key => $default ) :
			$out['svg_uploads']['roles'][ $role_key ] = ! empty( $roles[ $role_key ] );
		endforeach;

	endif;

	if ( $submit_tab === 'login' ) :
		$login_customization = isset( $value['login_customization'] ) && is_array( $value['login_customization'] )
			? $value['login_customization']
			: array();
		$out['login_customization']['enable'] = ! empty( $login_customization['enable'] );

		$logout = isset( $value['logout_redirect'] ) && is_array( $value['logout_redirect'] )
			? $value['logout_redirect']
			: array();
		$out['logout_redirect']['enable'] = ! empty( $logout['enable'] );

		$destination = isset( $logout['destination'] )
			? sanitize_key( (string) $logout['destination'] )
			: 'home';
		$allowed_destinations = tcres_settings_logout_redirect_get_allowed_destinations();
		$out['logout_redirect']['destination'] = in_array( $destination, $allowed_destinations, true )
			? $destination
			: 'home';

		$out['logout_redirect']['custom_url'] = isset( $logout['custom_url'] )
			? esc_url_raw( trim( (string) $logout['custom_url'] ) )
			: '';
	endif;

	if ( $submit_tab === 'content' ) :
		$search = isset( $value['extend_search'] ) && is_array( $value['extend_search'] )
			? $value['extend_search']
			: array();
		$out['extend_search']['enable'] = ! empty( $search['enable'] );

		$post_types = isset( $search['post_types'] ) && is_array( $search['post_types'] )
			? $search['post_types']
			: array();
		$out['extend_search']['post_types'] = array();
		foreach ( tcres_settings_extend_search_post_type_defaults() as $post_type => $default ) :
			$out['extend_search']['post_types'][ $post_type ] = ! empty( $post_types[ $post_type ] );
		endforeach;

		$out['extend_search']['search_taxonomies'] = ! empty( $search['search_taxonomies'] );

		$meta_mode = isset( $search['meta_mode'] )
			? sanitize_key( (string) $search['meta_mode'] )
			: 'public';
		$out['extend_search']['meta_mode'] = in_array( $meta_mode, array( 'public', 'keys', 'none' ), true )
			? $meta_mode
			: 'public';

		$meta_keys_raw = isset( $search['meta_keys'] )
			? (string) $search['meta_keys']
			: '';
		$meta_keys     = preg_split( '/[\s,]+/', $meta_keys_raw, -1, PREG_SPLIT_NO_EMPTY );
		$meta_keys     = is_array( $meta_keys )
			? $meta_keys
			: array();
		$meta_keys     = array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( string $key ): string {
							return sanitize_text_field( $key );
						},
						$meta_keys
					),
					static function ( string $key ): bool {
						return $key !== '';
					}
				)
			)
		);
		$out['extend_search']['meta_keys'] = implode( "\n", $meta_keys );

		$out['extend_search']['prevent_empty_search'] = ! empty( $search['prevent_empty_search'] );

		$crumbs = isset( $value['breadcrumbs'] ) && is_array( $value['breadcrumbs'] )
			? $value['breadcrumbs']
			: array();
		$out['breadcrumbs']['enable']    = ! empty( $crumbs['enable'] );
		$out['breadcrumbs']['show_home'] = ! empty( $crumbs['show_home'] );

		$output_pair = tcres_output_location_sanitize_pair(
			$crumbs,
			tcres_output_location_keys_breadcrumbs()
		);
		$out['breadcrumbs']['output_location'] = $output_pair['output_location'];
		$out['breadcrumbs']['output_target']   = $output_pair['output_target'];

		$separator = isset( $crumbs['separator'] )
			? sanitize_text_field( (string) $crumbs['separator'] )
			: '/';
		$out['breadcrumbs']['separator'] = $separator !== ''
			? $separator
			: '/';

		$home_label = isset( $crumbs['home_label'] )
			? sanitize_text_field( (string) $crumbs['home_label'] )
			: '';

		$home_display = isset( $crumbs['home_display'] )
			? sanitize_key( (string) $crumbs['home_display'] )
			: 'icon_label';
		$allowed_home_display = array( 'icon_label', 'label', 'icon' );
		$out['breadcrumbs']['home_display'] = in_array( $home_display, $allowed_home_display, true )
			? $home_display
			: 'icon_label';

		// 0 = automatic (Posts page), -1 = none, >0 = page ID.
		$blog_page_raw = isset( $crumbs['blog_page'] )
			? (int) $crumbs['blog_page']
			: 0;
		$out['breadcrumbs']['blog_page'] = $blog_page_raw === -1
			? -1
			: max( 0, $blog_page_raw );

		$out['breadcrumbs']['blog_taxonomy'] = isset( $crumbs['blog_taxonomy'] )
			? sanitize_key( (string) $crumbs['blog_taxonomy'] )
			: 'category';

		$cpt_submitted = isset( $crumbs['cpt'] ) && is_array( $crumbs['cpt'] )
			? $crumbs['cpt']
			: array();
		$out['breadcrumbs']['cpt'] = array();
		foreach ( tcres_settings_breadcrumbs_cpt_defaults() as $post_type => $cpt_default ) :
			$row = isset( $cpt_submitted[ $post_type ] ) && is_array( $cpt_submitted[ $post_type ] )
				? $cpt_submitted[ $post_type ]
				: array();
			$out['breadcrumbs']['cpt'][ $post_type ] = array(
				'page'     => isset( $row['page'] ) ? absint( $row['page'] ) : 0,
				'taxonomy' => isset( $row['taxonomy'] )
					? sanitize_key( (string) $row['taxonomy'] )
					: '',
			);
		endforeach;

		$sitemap = isset( $value['sitemap'] ) && is_array( $value['sitemap'] )
			? $value['sitemap']
			: array();
		$out['sitemap']['enable']             = ! empty( $sitemap['enable'] );
		$out['sitemap']['hide_empty']         = ! empty( $sitemap['hide_empty'] );
		$out['sitemap']['show_list_bullets']  = ! empty( $sitemap['show_list_bullets'] );
		$out['sitemap']['blog']               = ! empty( $sitemap['blog'] );
		$out['sitemap']['blog_show_taxonomy'] = ! empty( $sitemap['blog_show_taxonomy'] );
		$out['sitemap']['blog_show_posts']    = ! empty( $sitemap['blog_show_posts'] );

		$max_depth = isset( $sitemap['max_depth'] )
			? absint( $sitemap['max_depth'] )
			: 3;
		$out['sitemap']['max_depth'] = $max_depth >= 1
			? $max_depth
			: 3;

		$page_sort = isset( $sitemap['page_sort'] )
			? sanitize_key( (string) $sitemap['page_sort'] )
			: 'menu_order';
		$out['sitemap']['page_sort'] = in_array( $page_sort, array( 'menu_order', 'alphabetical' ), true )
			? $page_sort
			: 'menu_order';

		$out['sitemap']['blog_parent_page_id'] = isset( $sitemap['blog_parent_page_id'] )
			? absint( $sitemap['blog_parent_page_id'] )
			: 0;

		$out['sitemap']['blog_taxonomy'] = isset( $sitemap['blog_taxonomy'] )
			? sanitize_key( (string) $sitemap['blog_taxonomy'] )
			: 'category';

		$blog_max_depth_raw = isset( $sitemap['blog_max_depth'] )
			? trim( (string) $sitemap['blog_max_depth'] )
			: '';
		if ( $blog_max_depth_raw === '' ) :
			$out['sitemap']['blog_max_depth'] = '';
		else :
			$blog_max_depth = absint( $blog_max_depth_raw );
			$out['sitemap']['blog_max_depth'] = $blog_max_depth >= 1
				? (string) $blog_max_depth
				: '';
		endif;

		$sitemap_cpt_submitted = isset( $sitemap['cpt'] ) && is_array( $sitemap['cpt'] )
			? $sitemap['cpt']
			: array();
		$out['sitemap']['cpt'] = array();
		foreach ( tcres_settings_sitemap_cpt_defaults() as $post_type => $cpt_default ) :
			$row = isset( $sitemap_cpt_submitted[ $post_type ] ) && is_array( $sitemap_cpt_submitted[ $post_type ] )
				? $sitemap_cpt_submitted[ $post_type ]
				: array();
			$cpt_max_raw = isset( $row['max_depth'] ) ? trim( (string) $row['max_depth'] ) : '';
			if ( $cpt_max_raw === '' ) :
				$cpt_max_depth = '';
			else :
				$cpt_max_int = absint( $cpt_max_raw );
				$cpt_max_depth = $cpt_max_int >= 1 ? (string) $cpt_max_int : '';
			endif;
			$out['sitemap']['cpt'][ $post_type ] = array(
				'enable'         => ! empty( $row['enable'] ),
				'parent_page_id' => isset( $row['parent_page_id'] ) ? absint( $row['parent_page_id'] ) : 0,
				'taxonomy'       => isset( $row['taxonomy'] )
					? sanitize_key( (string) $row['taxonomy'] )
					: '',
				'show_taxonomy'  => ! empty( $row['show_taxonomy'] ),
				'show_posts'     => ! empty( $row['show_posts'] ),
				'max_depth'      => $cpt_max_depth,
			);
		endforeach;

		$related = isset( $value['related_content'] ) && is_array( $value['related_content'] )
			? $value['related_content']
			: array();
		$out['related_content']['enable']               = ! empty( $related['enable'] );
		$out['related_content']['show_excerpt']         = ! empty( $related['show_excerpt'] );
		$out['related_content']['show_button']          = ! empty( $related['show_button'] );
		$out['related_content']['show_taxonomies']      = ! empty( $related['show_taxonomies'] );
		$out['related_content']['show_taxonomy_labels'] = ! empty( $related['show_taxonomy_labels'] );

		$related_output = tcres_output_location_sanitize_pair(
			$related,
			tcres_output_location_keys_related_content()
		);
		$out['related_content']['output_location'] = $related_output['output_location'];
		$out['related_content']['output_target']   = $related_output['output_target'];

		$list = isset( $related['post_type_list'] )
			? sanitize_key( (string) $related['post_type_list'] )
			: 'current';
		$out['related_content']['post_type_list'] = in_array( $list, array( 'all', 'current', 'others' ), true )
			? $list
			: 'current';

		$ppp = isset( $related['posts_per_page'] )
			? absint( $related['posts_per_page'] )
			: 3;
		if ( $ppp < 1 ) $ppp = 3;
		if ( $ppp > 12 ) $ppp = 12;
		$out['related_content']['posts_per_page'] = $ppp;

		$image_size = isset( $related['image_size'] )
			? sanitize_key( (string) $related['image_size'] )
			: 'medium';
		$out['related_content']['image_size'] = $image_size !== ''
			? $image_size
			: 'medium';

		$title_tag = isset( $related['title_tag'] )
			? strtolower( sanitize_key( (string) $related['title_tag'] ) )
			: 'h3';
		$out['related_content']['title_tag'] = in_array( $title_tag, array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' ), true )
			? $title_tag
			: 'h3';

		$button_label = isset( $related['button_label'] )
			? sanitize_text_field( (string) $related['button_label'] )
			: '';

		$i18n_lang = tcres_multilingual_is_active()
			? tcres_multilingual_get_language_from_request()
			: null;
		if ( is_string( $i18n_lang ) && $i18n_lang !== '' ) :
			tcres_multilingual_save_string_overrides(
				$i18n_lang,
				array(
					'breadcrumbs'      => array( 'home_label' => $home_label ),
					'related_content'  => array( 'button_label' => $button_label ),
				)
			);
			$default_lang = tcres_multilingual_get_default_language();
			if ( $default_lang === null || $i18n_lang === $default_lang ) :
				$out['breadcrumbs']['home_label']           = $home_label;
				$out['related_content']['button_label']     = $button_label;
			else :
				$out['breadcrumbs']['home_label'] = isset( $old['breadcrumbs']['home_label'] )
					? sanitize_text_field( (string) $old['breadcrumbs']['home_label'] )
					: '';
				$out['related_content']['button_label'] = isset( $old['related_content']['button_label'] )
					? sanitize_text_field( (string) $old['related_content']['button_label'] )
					: '';
			endif;
		else :
			$out['breadcrumbs']['home_label']       = $home_label;
			$out['related_content']['button_label'] = $button_label;
		endif;

		$tax_display = isset( $related['taxonomies_display'] )
			? sanitize_key( (string) $related['taxonomies_display'] )
			: 'grouped';
		$out['related_content']['taxonomies_display'] = in_array( $tax_display, array( 'grouped', 'mixed' ), true )
			? $tax_display
			: 'grouped';

		foreach ( tcres_settings_module_post_type_toggles_defaults() as $post_type => $default ) :
			$out['related_content'][ $post_type ] = ! empty( $related[ $post_type ] );
		endforeach;

		foreach ( tcres_settings_module_taxonomy_toggles_defaults( 'tax_' ) as $tax_key => $default ) :
			$out['related_content'][ $tax_key ] = ! empty( $related[ $tax_key ] );
		endforeach;

		$scroll = isset( $value['scroll_to_top'] ) && is_array( $value['scroll_to_top'] )
			? $value['scroll_to_top']
			: array();

		$out['scroll_to_top']['enable']        = ! empty( $scroll['enable'] );
		$out['scroll_to_top']['avoid_footer']  = ! empty( $scroll['avoid_footer'] );

		$threshold = isset( $scroll['threshold_viewports'] )
			? (float) $scroll['threshold_viewports']
			: 1.0;
		if ( $threshold < 0.1 ) :
			$threshold = 0.1;
		endif;
		if ( $threshold > 10 ) :
			$threshold = 10.0;
		endif;
		$out['scroll_to_top']['threshold_viewports'] = $threshold;

		$out['scroll_to_top']['footer_selector'] = isset( $scroll['footer_selector'] )
			? sanitize_text_field( (string) $scroll['footer_selector'] )
			: '';

		$gap = isset( $scroll['footer_gap'] )
			? (int) $scroll['footer_gap']
			: 16;
		if ( $gap < 0 ) :
			$gap = 0;
		endif;
		if ( $gap > 200 ) :
			$gap = 200;
		endif;
		$out['scroll_to_top']['footer_gap'] = $gap;
	endif;

	if ( $submit_tab === 'fields' ) :
		$module_groups = array(
			'bodyclass' => array_merge(
				array( 'enable' => false ),
				tcres_settings_module_post_type_toggles_defaults(),
				tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
			),
			'term_image' => array_merge(
				array( 'enable' => false ),
				tcres_settings_module_taxonomy_toggles_defaults( '' )
			),
			'featured_video' => array_merge(
				array( 'enable' => false ),
				tcres_settings_module_post_type_toggles_defaults(),
				tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
			),
			'subtitle' => array_merge(
				array( 'enable' => false ),
				tcres_settings_module_post_type_toggles_defaults(),
				tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
			),
			'hero' => array_merge(
				array( 'enable' => false ),
				tcres_settings_module_post_type_toggles_defaults(),
				tcres_settings_module_taxonomy_toggles_defaults( 'tax_' )
			),
		);

		foreach ( $module_groups as $group_key => $group_defaults ) :
			$submitted = isset( $value[ $group_key ] ) && is_array( $value[ $group_key ] )
				? $value[ $group_key ]
				: array();
			$out[ $group_key ] = array();
			foreach ( $group_defaults as $key => $default ) :
				$out[ $group_key ][ $key ] = ! empty( $submitted[ $key ] );
			endforeach;
		endforeach;
	endif;

	if ( $submit_tab === 'social' ) :
		$social = isset( $value['social_menu'] ) && is_array( $value['social_menu'] )
			? $value['social_menu']
			: array();
		$out['social_menu']['enable']            = ! empty( $social['enable'] );
		$out['social_menu']['show_network_name'] = ! empty( $social['show_network_name'] );

		$share = isset( $value['share_content'] ) && is_array( $value['share_content'] )
			? $value['share_content']
			: array();

		$out['share_content']['enable']             = ! empty( $share['enable'] );
		$out['share_content']['show_title']         = ! empty( $share['show_title'] );
		$out['share_content']['buttons_vertical']   = ! empty( $share['buttons_vertical'] );
		$out['share_content']['show_on_front_page'] = ! empty( $share['show_on_front_page'] );
		$out['share_content']['show_on_home']       = ! empty( $share['show_on_home'] );
		$out['share_content']['show_on_search']     = ! empty( $share['show_on_search'] );
		$out['share_content']['show_on_author']     = ! empty( $share['show_on_author'] );
		$out['share_content']['show_on_date']       = ! empty( $share['show_on_date'] );

		$share_output = tcres_output_location_sanitize_pair(
			$share,
			tcres_output_location_keys_share_content()
		);
		$out['share_content']['output_location'] = $share_output['output_location'];
		$out['share_content']['output_target']   = $share_output['output_target'];

		$allowed_templates = array();
		foreach ( tcres_template_get_info() as $template ) :
			if ( ! is_array( $template ) || empty( $template['slug'] ) ) continue;
			$allowed_templates[] = (string) $template['slug'];
		endforeach;

		$submitted_templates = isset( $share['show_on_templates'] ) && is_array( $share['show_on_templates'] )
			? $share['show_on_templates']
			: array();
		$out['share_content']['show_on_templates'] = array_values(
			array_filter(
				array_map( 'sanitize_key', $submitted_templates ),
				static function ( string $slug ) use ( $allowed_templates ): bool {
					return $slug !== '' && in_array( $slug, $allowed_templates, true );
				}
			)
		);

		$allowed_archive_post_types = function_exists( 'tcres_settings_social_share_content_get_archive_post_types' )
			? tcres_settings_social_share_content_get_archive_post_types()
			: array();
		$submitted_archive_post_types = isset( $share['show_on_post_type_archives'] ) && is_array( $share['show_on_post_type_archives'] )
			? $share['show_on_post_type_archives']
			: array();
		$out['share_content']['show_on_post_type_archives'] = array_values(
			array_filter(
				array_map( 'sanitize_key', $submitted_archive_post_types ),
				static function ( string $post_type ) use ( $allowed_archive_post_types ): bool {
					return $post_type !== '' && in_array( $post_type, $allowed_archive_post_types, true );
				}
			)
		);

		$allowed_taxonomies = function_exists( 'tcres_settings_content_get_module_taxonomies' )
			? tcres_settings_content_get_module_taxonomies()
			: array();
		$submitted_taxonomies = isset( $share['show_on_taxonomies'] ) && is_array( $share['show_on_taxonomies'] )
			? $share['show_on_taxonomies']
			: array();
		$out['share_content']['show_on_taxonomies'] = array_values(
			array_filter(
				array_map( 'sanitize_key', $submitted_taxonomies ),
				static function ( string $taxonomy ) use ( $allowed_taxonomies ): bool {
					return $taxonomy !== '' && in_array( $taxonomy, $allowed_taxonomies, true );
				}
			)
		);

		$allowed_post_types = function_exists( 'tcres_settings_content_get_module_post_types' )
			? tcres_settings_content_get_module_post_types()
			: array();
		$submitted_post_types = isset( $share['show_on_post_types'] ) && is_array( $share['show_on_post_types'] )
			? $share['show_on_post_types']
			: array();
		$out['share_content']['show_on_post_types'] = array_values(
			array_filter(
				array_map( 'sanitize_key', $submitted_post_types ),
				static function ( string $post_type ) use ( $allowed_post_types ): bool {
					return $post_type !== '' && in_array( $post_type, $allowed_post_types, true );
				}
			)
		);

		$share_title = isset( $share['title'] )
			? sanitize_text_field( (string) $share['title'] )
			: '';

		$i18n_lang = tcres_multilingual_is_active()
			? tcres_multilingual_get_language_from_request()
			: null;
		if ( is_string( $i18n_lang ) && $i18n_lang !== '' ) :
			tcres_multilingual_save_string_overrides(
				$i18n_lang,
				array(
					'share_content' => array(
						'title' => $share_title,
					),
				)
			);
			$default_lang = tcres_multilingual_get_default_language();
			if ( $default_lang === null || $i18n_lang === $default_lang ) :
				$out['share_content']['title'] = $share_title;
			else :
				$old_share = isset( $old['share_content'] ) && is_array( $old['share_content'] )
					? $old['share_content']
					: array();
				$out['share_content']['title'] = isset( $old_share['title'] )
					? sanitize_text_field( (string) $old_share['title'] )
					: '';
			endif;
		else :
			$out['share_content']['title'] = $share_title;
		endif;

		foreach (
			array(
				'network_facebook',
				'network_x',
				'network_linkedin',
				'network_pinterest',
				'network_whatsapp',
				'network_telegram',
				'network_email',
				'network_copy_link',
				'button_show_icon',
				'button_show_share_text',
				'button_show_name',
			) as $share_bool_key
		) :
			$out['share_content'][ $share_bool_key ] = ! empty( $share[ $share_bool_key ] );
		endforeach;

		$chats = isset( $value['chats'] ) && is_array( $value['chats'] )
			? $value['chats']
			: array();

		$out['chats']['enable']             = ! empty( $chats['enable'] );
		$out['chats']['whatsapp_enable']    = ! empty( $chats['whatsapp_enable'] );
		$out['chats']['telegram_enable']    = ! empty( $chats['telegram_enable'] );
		$out['chats']['messenger_enable']   = ! empty( $chats['messenger_enable'] );
		$out['chats']['whatsapp_number']    = isset( $chats['whatsapp_number'] )
			? sanitize_text_field( (string) $chats['whatsapp_number'] )
			: '';
		$out['chats']['telegram_username']  = isset( $chats['telegram_username'] )
			? sanitize_text_field( ltrim( (string) $chats['telegram_username'], '@' ) )
			: '';
		$out['chats']['messenger_fbpageid'] = isset( $chats['messenger_fbpageid'] )
			? sanitize_text_field( (string) $chats['messenger_fbpageid'] )
			: '';

		$chats_general_title     = isset( $chats['general_title'] )
			? sanitize_text_field( (string) $chats['general_title'] )
			: '';
		$chats_whatsapp_message  = isset( $chats['whatsapp_message'] )
			? sanitize_text_field( (string) $chats['whatsapp_message'] )
			: '';
		$chats_telegram_message  = isset( $chats['telegram_message'] )
			? sanitize_text_field( (string) $chats['telegram_message'] )
			: '';

		$i18n_lang = tcres_multilingual_is_active()
			? tcres_multilingual_get_language_from_request()
			: null;
		if ( is_string( $i18n_lang ) && $i18n_lang !== '' ) :
			tcres_multilingual_save_string_overrides(
				$i18n_lang,
				array(
					'chats' => array(
						'general_title'     => $chats_general_title,
						'whatsapp_message'  => $chats_whatsapp_message,
						'telegram_message'  => $chats_telegram_message,
					),
				)
			);
			$default_lang = tcres_multilingual_get_default_language();
			if ( $default_lang === null || $i18n_lang === $default_lang ) :
				$out['chats']['general_title']    = $chats_general_title;
				$out['chats']['whatsapp_message'] = $chats_whatsapp_message;
				$out['chats']['telegram_message'] = $chats_telegram_message;
			else :
				$old_chats = isset( $old['chats'] ) && is_array( $old['chats'] )
					? $old['chats']
					: array();
				$out['chats']['general_title'] = isset( $old_chats['general_title'] )
					? sanitize_text_field( (string) $old_chats['general_title'] )
					: '';
				$out['chats']['whatsapp_message'] = isset( $old_chats['whatsapp_message'] )
					? sanitize_text_field( (string) $old_chats['whatsapp_message'] )
					: '';
				$out['chats']['telegram_message'] = isset( $old_chats['telegram_message'] )
					? sanitize_text_field( (string) $old_chats['telegram_message'] )
					: '';
			endif;
		else :
			$out['chats']['general_title']    = $chats_general_title;
			$out['chats']['whatsapp_message'] = $chats_whatsapp_message;
			$out['chats']['telegram_message'] = $chats_telegram_message;
		endif;
	endif;

	if ( $submit_tab === 'privacy' ) :
		$privacy = isset( $value['privacy_notice'] ) && is_array( $value['privacy_notice'] )
			? $value['privacy_notice']
			: array();

		$out['privacy_notice']['enable'] = ! empty( $privacy['enable'] );

		$trigger = isset( $privacy['trigger'] )
			? wp_kses_post( (string) $privacy['trigger'] )
			: '';
		$contact = isset( $privacy['contact'] )
			? wp_kses_post( (string) $privacy['contact'] )
			: '';
		$subscribe = isset( $privacy['subscribe'] )
			? wp_kses_post( (string) $privacy['subscribe'] )
			: '';
		$comments = isset( $privacy['comments'] )
			? wp_kses_post( (string) $privacy['comments'] )
			: '';
		$register = isset( $privacy['register'] )
			? wp_kses_post( (string) $privacy['register'] )
			: '';
		$checkout = isset( $privacy['checkout'] )
			? wp_kses_post( (string) $privacy['checkout'] )
			: '';

		$i18n_lang = tcres_multilingual_is_active()
			? tcres_multilingual_get_language_from_request()
			: null;
		if ( is_string( $i18n_lang ) && $i18n_lang !== '' ) :
			tcres_multilingual_save_string_overrides(
				$i18n_lang,
				array(
					'privacy_notice' => array(
						'trigger'   => $trigger,
						'contact'   => $contact,
						'subscribe' => $subscribe,
						'comments'  => $comments,
						'register'  => $register,
						'checkout'  => $checkout,
					),
				)
			);
			$default_lang = tcres_multilingual_get_default_language();
			if ( $default_lang === null || $i18n_lang === $default_lang ) :
				$out['privacy_notice']['trigger']   = $trigger;
				$out['privacy_notice']['contact']   = $contact;
				$out['privacy_notice']['subscribe'] = $subscribe;
				$out['privacy_notice']['comments']  = $comments;
				$out['privacy_notice']['register']  = $register;
				$out['privacy_notice']['checkout']  = $checkout;
			else :
				$old_privacy = isset( $old['privacy_notice'] ) && is_array( $old['privacy_notice'] )
					? $old['privacy_notice']
					: array();
				$out['privacy_notice']['trigger'] = isset( $old_privacy['trigger'] )
					? wp_kses_post( (string) $old_privacy['trigger'] )
					: '';
				$out['privacy_notice']['contact'] = isset( $old_privacy['contact'] )
					? wp_kses_post( (string) $old_privacy['contact'] )
					: '';
				$out['privacy_notice']['subscribe'] = isset( $old_privacy['subscribe'] )
					? wp_kses_post( (string) $old_privacy['subscribe'] )
					: '';
				$out['privacy_notice']['comments'] = isset( $old_privacy['comments'] )
					? wp_kses_post( (string) $old_privacy['comments'] )
					: '';
				$out['privacy_notice']['register'] = isset( $old_privacy['register'] )
					? wp_kses_post( (string) $old_privacy['register'] )
					: '';
				$out['privacy_notice']['checkout'] = isset( $old_privacy['checkout'] )
					? wp_kses_post( (string) $old_privacy['checkout'] )
					: '';
			endif;
		else :
			$out['privacy_notice']['trigger']   = $trigger;
			$out['privacy_notice']['contact']   = $contact;
			$out['privacy_notice']['subscribe'] = $subscribe;
			$out['privacy_notice']['comments']  = $comments;
			$out['privacy_notice']['register']  = $register;
			$out['privacy_notice']['checkout']  = $checkout;
		endif;

		$wpforms_contact_ids = isset( $privacy['wpforms_contact_ids'] ) && is_array( $privacy['wpforms_contact_ids'] )
			? $privacy['wpforms_contact_ids']
			: array();
		$out['privacy_notice']['wpforms_contact_ids'] = array_values(
			array_filter(
				array_map( 'absint', $wpforms_contact_ids ),
				static function ( int $id ): bool {
					return $id > 0;
				}
			)
		);

		$wpforms_subscribe_ids = isset( $privacy['wpforms_subscribe_ids'] ) && is_array( $privacy['wpforms_subscribe_ids'] )
			? $privacy['wpforms_subscribe_ids']
			: array();
		$out['privacy_notice']['wpforms_subscribe_ids'] = array_values(
			array_filter(
				array_map( 'absint', $wpforms_subscribe_ids ),
				static function ( int $id ): bool {
					return $id > 0;
				}
			)
		);

		$consent = isset( $value['privacy_consent'] ) && is_array( $value['privacy_consent'] )
			? $value['privacy_consent']
			: array();

		$out['privacy_consent']['enable']   = ! empty( $consent['enable'] );
		$out['privacy_consent']['comments'] = ! empty( $consent['comments'] );
		$out['privacy_consent']['register'] = ! empty( $consent['register'] );
		$out['privacy_consent']['checkout'] = ! empty( $consent['checkout'] );

		$consent_label = isset( $consent['label'] )
			? wp_kses_post( (string) $consent['label'] )
			: '';

		if ( is_string( $i18n_lang ) && $i18n_lang !== '' ) :
			tcres_multilingual_save_string_overrides(
				$i18n_lang,
				array(
					'privacy_consent' => array(
						'label' => $consent_label,
					),
				)
			);
			$default_lang = tcres_multilingual_get_default_language();
			if ( $default_lang === null || $i18n_lang === $default_lang ) :
				$out['privacy_consent']['label'] = $consent_label;
			else :
				$old_consent = isset( $old['privacy_consent'] ) && is_array( $old['privacy_consent'] )
					? $old['privacy_consent']
					: array();
				$out['privacy_consent']['label'] = isset( $old_consent['label'] )
					? wp_kses_post( (string) $old_consent['label'] )
					: '';
			endif;
		else :
			$out['privacy_consent']['label'] = $consent_label;
		endif;

		$google_consent_mode = isset( $value['google_consent_mode'] ) && is_array( $value['google_consent_mode'] )
			? $value['google_consent_mode']
			: array();
		$out['google_consent_mode']['enable'] = ! empty( $google_consent_mode['enable'] );
	endif;

	if ( $submit_tab === 'extras' ) :
		$assets = isset( $value['assets'] ) && is_array( $value['assets'] )
			? $value['assets']
			: array();
		$out['assets']['disable_frontend_css'] = ! empty( $assets['disable_frontend_css'] );
		$out['assets']['disable_frontend_js']  = ! empty( $assets['disable_frontend_js'] );

		$external = isset( $value['external_scripts'] ) && is_array( $value['external_scripts'] )
			? $value['external_scripts']
			: array();
		foreach ( array( 'swiper', 'glightbox', 'choices' ) as $lib_key ) :
			$out['external_scripts'][ $lib_key ] = ! empty( $external[ $lib_key ] );
		endforeach;

		$smooth = isset( $value['smooth_scroll'] ) && is_array( $value['smooth_scroll'] )
			? $value['smooth_scroll']
			: array();
		$out['smooth_scroll']['enable']       = ! empty( $smooth['enable'] );
		$out['smooth_scroll']['smooth_wheel'] = ! empty( $smooth['smooth_wheel'] );
		$out['smooth_scroll']['sync_touch']   = ! empty( $smooth['sync_touch'] );
		$out['smooth_scroll']['anchors']      = ! empty( $smooth['anchors'] );

		$lerp = isset( $smooth['lerp'] ) ? (float) $smooth['lerp'] : 0.05;
		if ( $lerp < 0.01 ) :
			$lerp = 0.01;
		endif;
		if ( $lerp > 1 ) :
			$lerp = 1.0;
		endif;
		$out['smooth_scroll']['lerp'] = $lerp;

		$exclude_raw = isset( $smooth['exclude_selectors'] )
			? sanitize_textarea_field( (string) $smooth['exclude_selectors'] )
			: '';
		$out['smooth_scroll']['exclude_selectors'] = function_exists( 'tcres_smooth_scroll_normalize_exclude_selectors' )
			? tcres_smooth_scroll_normalize_exclude_selectors( $exclude_raw )
			: $exclude_raw;

		$svg = isset( $value['svg_icons'] ) && is_array( $value['svg_icons'] )
			? $value['svg_icons']
			: array();
		$out['svg_icons']['frontend_file'] = isset( $svg['frontend_file'] )
			? esc_url_raw( trim( (string) $svg['frontend_file'] ) )
			: '';
		$out['svg_icons']['admin_file'] = isset( $svg['admin_file'] )
			? esc_url_raw( trim( (string) $svg['admin_file'] ) )
			: '';

		$shortcodes = isset( $value['shortcodes'] ) && is_array( $value['shortcodes'] )
			? $value['shortcodes']
			: array();
		$out['shortcodes']['enable'] = ! empty( $shortcodes['enable'] );
	endif;

	return tcres_settings_merge_defaults( $defaults, $out );
}
