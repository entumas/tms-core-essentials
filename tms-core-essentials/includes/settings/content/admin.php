<?php
/**
 * Includes -> Settings -> Content -> Administration
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_disable_gutenberg_get_post_type_items(): array {
	$items = array();

	foreach ( tcres_settings_disable_gutenberg_get_post_types() as $post_type ) :
		$post_type_object = get_post_type_object( $post_type );
		$label            = $post_type_object && isset( $post_type_object->labels->name )
			? $post_type_object->labels->name
			: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );

		$items[] = array(
			'id'    => 'tcres-disable-gutenberg-' . sanitize_html_class( $post_type ),
			'key'   => 'disable_' . $post_type,
			'title' => sprintf(
				/* translators: %s: Post type label. */
				__( 'Disable block editor for %s', 'tms-core-essentials' ),
				$label
			),
			'description' => sprintf(
				/* translators: %s: Post type label. */
				__( 'Uses the classic editor for the %s post type.', 'tms-core-essentials' ),
				$label
			),
		);
	endforeach;

	return $items;
}


function tcres_settings_admin_render_disable_gutenberg_panel(): void {
	$settings          = tcres_settings_get();
	$disable_gutenberg = isset( $settings['disable_gutenberg'] ) && is_array( $settings['disable_gutenberg'] )
		? $settings['disable_gutenberg']
		: array();
	$option            = TCRES_OPTION_NAME;
	$items             = tcres_settings_disable_gutenberg_get_post_type_items();
	$total             = count( $items );
	$active_count      = tcres_settings_cards_count_active( $items, $disable_gutenberg );
	$inactive_count    = $total - $active_count;
	$card_filter       = tcres_settings_get_current_card_filter( 'disable_gutenberg' );
	?>
		<h2><?php esc_html_e( 'Disable Gutenberg', 'tms-core-essentials' ); ?></h2>
		<?php if ( $total > 0 ) : ?>
			<?php
			tcres_settings_cards_panel_render_start( 'disable_gutenberg' );
			tcres_settings_cards_render_filter(
				'admin',
				'disable_gutenberg',
				$card_filter,
				$total,
				$active_count,
				$inactive_count
			);
			tcres_settings_cards_render_grid_start( __( 'Disable Gutenberg options', 'tms-core-essentials' ) );
			foreach ( $items as $item ) :
				tcres_settings_card_render_field(
					$item['id'],
					$option . '[disable_gutenberg][' . $item['key'] . ']',
					! empty( $disable_gutenberg[ $item['key'] ] ),
					$item['title'],
					$item['description']
				);
			endforeach;
			tcres_settings_cards_render_grid_end();
			tcres_settings_cards_panel_render_end();
			?>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'No eligible post types were found.', 'tms-core-essentials' ); ?></p>
		<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_disable_post_types_config( array $group, string $option_name ): void {
	$selected   = isset( $group['post_types'] ) && is_array( $group['post_types'] )
		? $group['post_types']
		: array();
	$post_types = array_keys( tcres_settings_module_post_type_toggles_defaults() );
	$label_id   = 'tcres-disable-features-post-types';
	?>
	<h4 id="<?php echo esc_attr( $label_id ); ?>" class="subtitle"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $post_types ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible post types were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<ul class="checks">
				<?php foreach ( $post_types as $post_type ) : ?>
					<?php
					$object = get_post_type_object( $post_type );
					$label  = $object && isset( $object->labels->name )
						? $object->labels->name
						: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
					$field  = $option_name . '[disable_features][post_types][' . $post_type . ']';
					$id     = 'tcres-disable-features-post-type-' . sanitize_html_class( $post_type );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $post_type ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Selected post type admin screens and frontend URLs redirect to the homepage.', 'tms-core-essentials' ); ?></p>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_disable_taxonomies_config( array $group, string $option_name ): void {
	$selected   = isset( $group['taxonomies'] ) && is_array( $group['taxonomies'] )
		? $group['taxonomies']
		: array();
	$taxonomies = array_keys( tcres_settings_module_taxonomy_toggles_defaults( '' ) );
	$label_id   = 'tcres-disable-features-taxonomies';
	?>
	<h4 id="<?php echo esc_attr( $label_id ); ?>" class="subtitle"><?php esc_html_e( 'Taxonomies', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $taxonomies ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible taxonomies were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<ul class="checks">
				<?php foreach ( $taxonomies as $taxonomy ) : ?>
					<?php
					$object = get_taxonomy( $taxonomy );
					$label  = $object && isset( $object->labels->name )
						? $object->labels->name
						: ucwords( str_replace( array( '-', '_' ), ' ', $taxonomy ) );
					$used_by = array();
					if ( $object && ! empty( $object->object_type ) ) :
						$object_types = is_array( $object->object_type )
							? $object->object_type
							: array( $object->object_type );
						foreach ( $object_types as $post_type ) :
							$pt_obj = is_string( $post_type )
								? get_post_type_object( $post_type )
								: null;
							if ( $pt_obj && isset( $pt_obj->labels->name ) ) :
								$used_by[] = (string) $pt_obj->labels->name;
							else :
								$used_by[] = is_string( $post_type )
									? $post_type
									: '';
							endif;
						endforeach;
						$used_by = array_values( array_filter( array_unique( $used_by ) ) );
					endif;
					$label_text = $label . ( ! empty( $used_by ) ? ' (' . implode( ', ', $used_by ) . ')' : '' );
					$field      = $option_name . '[disable_features][taxonomies][' . $taxonomy . ']';
					$id         = 'tcres-disable-features-taxonomy-' . sanitize_html_class( $taxonomy );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $taxonomy ] ) ); ?> />
							<?php echo esc_html( $label_text ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Selected taxonomy admin screens and frontend archives redirect to the homepage.', 'tms-core-essentials' ); ?></p>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_clean_admin_bar_config( array $group, string $option_name ): void {
	$role_nodes   = isset( $group['admin_bar_nodes'] ) && is_array( $group['admin_bar_nodes'] )
		? $group['admin_bar_nodes']
		: array();
	$role_custom  = isset( $group['admin_bar_custom'] ) && is_array( $group['admin_bar_custom'] )
		? $group['admin_bar_custom']
		: array();
	$node_choices = tcres_settings_admin_bar_node_choices();
	$wp_roles     = wp_roles();
	$role_names   = $wp_roles
		? $wp_roles->role_names
		: array();
	?>
	<?php if ( empty( $role_names ) ) : ?>
		<p class="description"><?php esc_html_e( 'No editable roles were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<?php foreach ( $role_names as $role_key => $role_label ) : ?>
			<?php
			$selected_nodes = isset( $role_nodes[ $role_key ] ) && is_array( $role_nodes[ $role_key ] )
				? $role_nodes[ $role_key ]
				: array();
			$custom_ids = isset( $role_custom[ $role_key ] )
				? (string) $role_custom[ $role_key ]
				: '';
			$section_id = 'tcres-clean-admin-bar-role-' . sanitize_html_class( $role_key );
			?>
			<fieldset aria-labelledby="<?php echo esc_attr( $section_id ); ?>">
				<h4 id="<?php echo esc_attr( $section_id ); ?>" class="subtitle"><?php echo esc_html( translate_user_role( $role_label ) ); ?></h4>
				<ul class="checks">
					<?php foreach ( $node_choices as $node_id => $node_label ) : ?>
						<?php
						$field = $option_name . '[disable_features][admin_bar_nodes][' . $role_key . '][' . $node_id . ']';
						$id    = 'tcres-clean-admin-bar-' . sanitize_html_class( $role_key . '-' . $node_id );
						?>
						<li>
							<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
								<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
								<input type="checkbox"
									id="<?php echo esc_attr( $id ); ?>"
									name="<?php echo esc_attr( $field ); ?>"
									value="1"
									<?php checked( ! empty( $selected_nodes[ $node_id ] ) ); ?> />
								<?php echo esc_html( $node_label ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="inline">
					<label for="<?php echo esc_attr( $section_id . '-custom' ); ?>"><?php esc_html_e( 'Custom node IDs', 'tms-core-essentials' ); ?></label>
				</p>
				<textarea
					class="large-text code"
					rows="2"
					id="<?php echo esc_attr( $section_id . '-custom' ); ?>"
					name="<?php echo esc_attr( $option_name . '[disable_features][admin_bar_custom][' . $role_key . ']' ); ?>"
					placeholder="<?php echo esc_attr( "rank-math\nwpseo-menu" ); ?>"><?php echo esc_textarea( $custom_ids ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One admin bar node ID per line, or separated by commas.', 'tms-core-essentials' ); ?></p>
			</fieldset>
			<hr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_disable_features_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['disable_features'] ) && is_array( $settings['disable_features'] )
		? $settings['disable_features']
		: array();
	$option   = TCRES_OPTION_NAME;
	$total    = 4;
	$active_count = ( ! empty( $group['disable_post_types'] ) ? 1 : 0 )
		+ ( ! empty( $group['disable_taxonomies'] ) ? 1 : 0 )
		+ ( ! empty( $group['disable_comments'] ) ? 1 : 0 )
		+ ( ! empty( $group['clean_admin_bar'] ) ? 1 : 0 );
	$inactive_count = $total - $active_count;
	$card_filter    = tcres_settings_get_current_card_filter( 'disable_features' );
	?>
		<h2><?php esc_html_e( 'Disable features', 'tms-core-essentials' ); ?></h2>
		<?php
		tcres_settings_cards_panel_render_start( 'disable_features' );
		tcres_settings_cards_render_filter(
			'admin',
			'disable_features',
			$card_filter,
			$total,
			$active_count,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Disable features options', 'tms-core-essentials' ) );
		tcres_settings_card_render_field(
			'tcres-disable-features-post-types-enable',
			$option . '[disable_features][disable_post_types]',
			! empty( $group['disable_post_types'] ),
			__( 'Disable post types', 'tms-core-essentials' ),
			__( 'Disables selected post type admin screens and redirects their frontend URLs to the homepage.', 'tms-core-essentials' ),
			static function () use ( $group, $option ): void {
				tcres_settings_admin_render_disable_post_types_config( $group, $option );
			}
		);
		tcres_settings_card_render_field(
			'tcres-disable-features-taxonomies-enable',
			$option . '[disable_features][disable_taxonomies]',
			! empty( $group['disable_taxonomies'] ),
			__( 'Disable taxonomies', 'tms-core-essentials' ),
			__( 'Disables selected taxonomy admin screens and redirects their frontend archives to the homepage.', 'tms-core-essentials' ),
			static function () use ( $group, $option ): void {
				tcres_settings_admin_render_disable_taxonomies_config( $group, $option );
			}
		);
		tcres_settings_card_render_field(
			'tcres-disable-features-comments-enable',
			$option . '[disable_features][disable_comments]',
			! empty( $group['disable_comments'] ),
			__( 'Disable comments', 'tms-core-essentials' ),
			__( 'Removes comments from the admin, admin bar, and frontend for all post types.', 'tms-core-essentials' )
		);
		tcres_settings_card_render_field(
			'tcres-disable-features-clean-admin-bar-enable',
			$option . '[disable_features][clean_admin_bar]',
			! empty( $group['clean_admin_bar'] ),
			__( 'Clean admin bar', 'tms-core-essentials' ),
			__( 'Hides selected admin bar nodes per user role, including custom node IDs.', 'tms-core-essentials' ),
			static function () use ( $group, $option ): void {
				tcres_settings_admin_render_clean_admin_bar_config( $group, $option );
			}
		);
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


function tcres_settings_admin_render_posts_list_thumbnail_config( array $posts_list, string $option_name ): void {
	$selected   = isset( $posts_list['thumbnail_post_types'] ) && is_array( $posts_list['thumbnail_post_types'] )
		? $posts_list['thumbnail_post_types']
		: array();
	$post_types = tcres_posts_list_get_thumbnail_post_types();
	$label_id   = 'tcres-posts-list-thumbnail-post-types';
	?>
	<h4 id="<?php echo esc_attr( $label_id ); ?>" class="subtitle"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $post_types ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible post types were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<ul class="checks">
				<?php foreach ( $post_types as $post_type ) : ?>
					<?php
					$object = get_post_type_object( $post_type );
					$label  = $object && isset( $object->labels->name )
						? $object->labels->name
						: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
					$field  = $option_name . '[posts_list][thumbnail_post_types][' . $post_type . ']';
					$id     = 'tcres-posts-list-thumbnail-' . sanitize_html_class( $post_type );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $post_type ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_posts_list_sortable_config( array $posts_list, string $option_name ): void {
	$config = isset( $posts_list['sortable_columns_config'] ) && is_array( $posts_list['sortable_columns_config'] )
		? $posts_list['sortable_columns_config']
		: array();
	$post_types = tcres_posts_list_get_sortable_post_types();
	$section_id = 'tcres-posts-list-sortable-columns';
	?>
	<h4 id="<?php echo esc_attr( $section_id ); ?>" class="subtitle"><?php esc_html_e( 'Columns by post type', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $post_types ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible post types were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<?php foreach ( $post_types as $post_type ) : ?>
			<?php
			$defs = tcres_posts_list_get_sortable_column_defs( $post_type );

			$object = get_post_type_object( $post_type );
			$label  = $object && isset( $object->labels->name )
				? $object->labels->name
				: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
			$selected = isset( $config[ $post_type ] ) && is_array( $config[ $post_type ] )
				? $config[ $post_type ]
				: array();
			$title_id = 'tcres-posts-list-sortable-title-' . sanitize_html_class( $post_type );
			?>
			<fieldset aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
				<h4 id="<?php echo esc_attr( $title_id ); ?>" class="subtitle"><?php echo esc_html( $label ); ?></h4>
				<?php if ( empty( $defs ) ) : ?>
					<p class="description"><?php esc_html_e( 'No sortable columns available for this post type.', 'tms-core-essentials' ); ?></p>
				<?php else : ?>
					<ul class="checks">
						<?php foreach ( $defs as $column_key => $def ) : ?>
							<?php
							$field = $option_name . '[posts_list][sortable_columns_config][' . $post_type . '][' . $column_key . ']';
							$id    = 'tcres-posts-list-sortable-' . sanitize_html_class( $post_type . '-' . $column_key );
							$col_label = isset( $def['label'] )
								? (string) $def['label']
								: $column_key;
							?>
							<li>
								<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
									<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
									<input type="checkbox"
										id="<?php echo esc_attr( $id ); ?>"
										name="<?php echo esc_attr( $field ); ?>"
										value="1"
										<?php checked( ! empty( $selected[ $column_key ] ) ); ?> />
									<?php echo esc_html( $col_label ); ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</fieldset>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_posts_list_drag_order_config( array $posts_list, string $option_name ): void {
	$selected   = isset( $posts_list['drag_order_post_types'] ) && is_array( $posts_list['drag_order_post_types'] )
		? $posts_list['drag_order_post_types']
		: array();
	$post_types = tcres_posts_list_get_drag_order_post_types();
	$label_id   = 'tcres-posts-list-drag-order-post-types';
	?>
	<h4 id="<?php echo esc_attr( $label_id ); ?>" class="subtitle"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $post_types ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible post types were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<ul class="checks">
				<?php foreach ( $post_types as $post_type ) : ?>
					<?php
					$object = get_post_type_object( $post_type );
					$label  = $object && isset( $object->labels->name )
						? $object->labels->name
						: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
					$field  = $option_name . '[posts_list][drag_order_post_types][' . $post_type . ']';
					$id     = 'tcres-posts-list-drag-order-' . sanitize_html_class( $post_type );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $post_type ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_posts_list_panel(): void {
	$settings   = tcres_settings_get();
	$posts_list = isset( $settings['posts_list'] ) && is_array( $settings['posts_list'] )
		? $settings['posts_list']
		: array();
	$option     = TCRES_OPTION_NAME;
	$total          = 3;
	$active_count   = ( ! empty( $posts_list['add_thumbnail_column'] ) ? 1 : 0 )
		+ ( ! empty( $posts_list['sortable_columns'] ) ? 1 : 0 )
		+ ( ! empty( $posts_list['drag_order'] ) ? 1 : 0 );
	$inactive_count = $total - $active_count;
	$card_filter    = tcres_settings_get_current_card_filter( 'posts_list' );
	?>
		<h2><?php esc_html_e( 'Posts list', 'tms-core-essentials' ); ?></h2>
		<?php
		tcres_settings_cards_panel_render_start( 'posts_list' );
		tcres_settings_cards_render_filter(
			'admin',
			'posts_list',
			$card_filter,
			$total,
			$active_count,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Posts list options', 'tms-core-essentials' ) );
		tcres_settings_card_render_field(
			'tcres-posts-list-add-thumbnail-column',
			$option . '[posts_list][add_thumbnail_column]',
			! empty( $posts_list['add_thumbnail_column'] ),
			__( 'Featured image column', 'tms-core-essentials' ),
			__( 'Adds an image column to the selected post type list tables in the admin.', 'tms-core-essentials' ),
			static function () use ( $posts_list, $option ): void {
				tcres_settings_admin_render_posts_list_thumbnail_config( $posts_list, $option );
			}
		);
		tcres_settings_card_render_field(
			'tcres-posts-list-sortable-columns',
			$option . '[posts_list][sortable_columns]',
			! empty( $posts_list['sortable_columns'] ),
			__( 'Sortable columns', 'tms-core-essentials' ),
			__( 'Makes selected list columns sortable for each post type (author and taxonomy columns).', 'tms-core-essentials' ),
			static function () use ( $posts_list, $option ): void {
				tcres_settings_admin_render_posts_list_sortable_config( $posts_list, $option );
			}
		);
		tcres_settings_card_render_field(
			'tcres-posts-list-drag-order',
			$option . '[posts_list][drag_order]',
			! empty( $posts_list['drag_order'] ),
			__( 'Drag to reorder', 'tms-core-essentials' ),
			__( 'Adds a drag handle on the admin list to reorder items by menu_order for the selected post types.', 'tms-core-essentials' ),
			static function () use ( $posts_list, $option ): void {
				tcres_settings_admin_render_posts_list_drag_order_config( $posts_list, $option );
			}
		);
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


function tcres_settings_admin_render_terms_list_drag_order_config( array $terms_list, string $option_name ): void {
	$selected   = isset( $terms_list['drag_order_taxonomies'] ) && is_array( $terms_list['drag_order_taxonomies'] )
		? $terms_list['drag_order_taxonomies']
		: array();
	$taxonomies = tcres_terms_list_get_drag_order_taxonomies();
	$label_id   = 'tcres-terms-list-drag-order-taxonomies';
	?>
	<h4 id="<?php echo esc_attr( $label_id ); ?>" class="subtitle"><?php esc_html_e( 'Taxonomies', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $taxonomies ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible taxonomies were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<ul class="checks">
				<?php foreach ( $taxonomies as $taxonomy ) : ?>
					<?php
					$object = get_taxonomy( $taxonomy );
					$label  = $object && isset( $object->labels->name )
						? $object->labels->name
						: ucwords( str_replace( array( '-', '_' ), ' ', $taxonomy ) );
					$field  = $option_name . '[terms_list][drag_order_taxonomies][' . $taxonomy . ']';
					$id     = 'tcres-terms-list-drag-order-' . sanitize_html_class( $taxonomy );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $taxonomy ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endif; ?>
	<?php
}


function tcres_settings_admin_render_terms_list_drag_order_usage(): void {
	$meta_key = TCRES_TERM_ORDER_META_KEY;
	$example  = "\$terms = get_terms( array(\n\t'taxonomy' => 'category',\n\t'hide_empty' => false,\n\t'meta_key' => '{$meta_key}',\n\t'orderby' => 'meta_value_num',\n\t'order' => 'ASC',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
		<p><?php esc_html_e( 'When enabled, the terms list gains a drag handle and the term edit screen gets a numeric Order field. Order is stored in term meta:', 'tms-core-essentials' ); ?></p>
		<p><code><?php echo esc_html( $meta_key ); ?></code></p>
		<p><?php esc_html_e( 'On the frontend, query terms with that meta key:', 'tms-core-essentials' ); ?></p>
		<pre><code><?php echo esc_html( $example ); ?></code></pre>
	</details>
	<?php
}


function tcres_settings_admin_render_terms_list_panel(): void {
	$settings       = tcres_settings_get();
	$terms_list     = isset( $settings['terms_list'] ) && is_array( $settings['terms_list'] )
		? $settings['terms_list']
		: array();
	$option         = TCRES_OPTION_NAME;
	$total          = 1;
	$active_count   = ! empty( $terms_list['drag_order'] )
		? 1
		: 0;
	$inactive_count = $total - $active_count;
	$card_filter    = tcres_settings_get_current_card_filter( 'terms_list' );
	?>
		<h2><?php esc_html_e( 'Terms list', 'tms-core-essentials' ); ?></h2>
		<?php
		tcres_settings_cards_panel_render_start( 'terms_list' );
		tcres_settings_cards_render_filter(
			'admin',
			'terms_list',
			$card_filter,
			$total,
			$active_count,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Terms list options', 'tms-core-essentials' ) );
		tcres_settings_card_render_field(
			'tcres-terms-list-drag-order',
			$option . '[terms_list][drag_order]',
			! empty( $terms_list['drag_order'] ),
			__( 'Drag to reorder', 'tms-core-essentials' ),
			__( 'Adds a drag handle on the terms list and an Order field on the term edit screen for the selected taxonomies.', 'tms-core-essentials' ),
			static function () use ( $terms_list, $option ): void {
				tcres_settings_admin_render_terms_list_drag_order_config( $terms_list, $option );
				tcres_settings_admin_render_terms_list_drag_order_usage();
			}
		);
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


/**
 * Role labels for SVG uploads (roles that can upload files).
 *
 * @return array<string, string> role_key => label
 */
function tcres_settings_svg_uploads_get_role_choices(): array {
	$choices  = array();
	$wp_roles = wp_roles();
	if ( ! $wp_roles ) return $choices;

	foreach ( array_keys( tcres_settings_svg_uploads_role_defaults() ) as $role_key ) :
		$name = isset( $wp_roles->role_names[ $role_key ] )
			? (string) $wp_roles->role_names[ $role_key ]
			: $role_key;
		$choices[ $role_key ] = translate_user_role( $name );
	endforeach;

	return $choices;
}


function tcres_settings_admin_render_svg_uploads_config( array $group, string $option_name ): void {
	$selected = isset( $group['roles'] ) && is_array( $group['roles'] )
		? $group['roles']
		: array();
	$roles    = tcres_settings_svg_uploads_get_role_choices();
	$label_id = 'tcres-svg-uploads-roles';
	?>
	<h4 id="<?php echo esc_attr( $label_id ); ?>" class="subtitle"><?php esc_html_e( 'Who can upload', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $roles ) ) : ?>
		<p class="description"><?php esc_html_e( 'No roles with upload permission were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
			<ul class="checks">
				<?php foreach ( $roles as $role_key => $label ) : ?>
					<?php
					$field = $option_name . '[svg_uploads][roles][' . $role_key . ']';
					$id    = 'tcres-svg-uploads-role-' . sanitize_html_class( $role_key );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input
								type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $role_key ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endif; ?>
	<p class="description">
		<?php esc_html_e( 'Only roles that can already upload media are listed. SVG sanitization is always enabled and cannot be turned off.', 'tms-core-essentials' ); ?>
	</p>
	<?php
}


function tcres_settings_admin_render_duplicate_posts_config( array $group, string $option_name ): void {
	$selected   = isset( $group['post_types'] ) && is_array( $group['post_types'] )
		? $group['post_types']
		: array();
	$post_types = array_keys( tcres_settings_duplicate_posts_post_type_defaults() );
	$status     = isset( $group['status'] )
		? (string) $group['status']
		: 'draft';
	if ( ! in_array( $status, array( 'draft', 'pending', 'private', 'publish' ), true ) ) $status = 'draft';

	$status_choices = array(
		'draft'   => __( 'Draft', 'tms-core-essentials' ),
		'pending' => __( 'Pending review', 'tms-core-essentials' ),
		'private' => __( 'Private', 'tms-core-essentials' ),
		'publish' => __( 'Published', 'tms-core-essentials' ),
	);
	$post_types_id   = 'tcres-duplicate-posts-post-types';
	$copy_options_id = 'tcres-duplicate-posts-copy-options';
	?>
	<h4 id="<?php echo esc_attr( $post_types_id ); ?>" class="subtitle"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h4>
	<?php if ( empty( $post_types ) ) : ?>
		<p class="description"><?php esc_html_e( 'No eligible post types were found.', 'tms-core-essentials' ); ?></p>
	<?php else : ?>
		<fieldset aria-labelledby="<?php echo esc_attr( $post_types_id ); ?>">
			<ul class="checks">
				<?php foreach ( $post_types as $post_type ) : ?>
					<?php
					$object = get_post_type_object( $post_type );
					$label  = $object && isset( $object->labels->name )
						? $object->labels->name
						: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
					$field  = $option_name . '[duplicate_posts][post_types][' . $post_type . ']';
					$id     = 'tcres-duplicate-posts-type-' . sanitize_html_class( $post_type );
					?>
					<li>
						<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input
								type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $post_type ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endif; ?>

	<h4 class="subtitle"><?php esc_html_e( 'Duplicated post', 'tms-core-essentials' ); ?></h4>
	<p class="inline">
		<label for="tcres-duplicate-posts-status"><?php esc_html_e( 'Status', 'tms-core-essentials' ); ?></label>
		<select
			name="<?php echo esc_attr( $option_name . '[duplicate_posts][status]' ); ?>"
			id="tcres-duplicate-posts-status">
			<?php foreach ( $status_choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Status assigned to the new copy.', 'tms-core-essentials' ); ?></p>
	<h4 id="<?php echo esc_attr( $copy_options_id ); ?>" class="subtitle"><?php esc_html_e( 'Copy options', 'tms-core-essentials' ); ?></h4>
	<fieldset aria-labelledby="<?php echo esc_attr( $copy_options_id ); ?>">
		<ul class="checks">
			<li>
				<label class="has-checkbox">
					<input type="hidden" name="<?php echo esc_attr( $option_name . '[duplicate_posts][copy_taxonomies]' ); ?>" value="0" />
					<input
						type="checkbox"
						name="<?php echo esc_attr( $option_name . '[duplicate_posts][copy_taxonomies]' ); ?>"
						value="1"
						<?php checked( ! empty( $group['copy_taxonomies'] ) ); ?> />
					<?php esc_html_e( 'Taxonomies', 'tms-core-essentials' ); ?>
				</label>
			</li>
			<li>
				<label class="has-checkbox">
					<input type="hidden" name="<?php echo esc_attr( $option_name . '[duplicate_posts][copy_meta]' ); ?>" value="0" />
					<input
						type="checkbox"
						name="<?php echo esc_attr( $option_name . '[duplicate_posts][copy_meta]' ); ?>"
						value="1"
						<?php checked( ! empty( $group['copy_meta'] ) ); ?> />
					<?php esc_html_e( 'Meta', 'tms-core-essentials' ); ?>
				</label>
			</li>
			<li>
				<label class="has-checkbox">
					<input type="hidden" name="<?php echo esc_attr( $option_name . '[duplicate_posts][copy_featured_image]' ); ?>" value="0" />
					<input
						type="checkbox"
						name="<?php echo esc_attr( $option_name . '[duplicate_posts][copy_featured_image]' ); ?>"
						value="1"
						<?php checked( ! empty( $group['copy_featured_image'] ) ); ?> />
					<?php esc_html_e( 'Featured image', 'tms-core-essentials' ); ?>
				</label>
			</li>
		</ul>
	</fieldset>
	<?php
}


function tcres_settings_admin_render_utilities_panel(): void {
	$settings = tcres_settings_get();
	$option   = TCRES_OPTION_NAME;

	$duplicate = isset( $settings['duplicate_posts'] ) && is_array( $settings['duplicate_posts'] )
		? $settings['duplicate_posts']
		: array();
	$svg       = isset( $settings['svg_uploads'] ) && is_array( $settings['svg_uploads'] )
		? $settings['svg_uploads']
		: array();

	$total          = 2;
	$active_count   = ( ! empty( $duplicate['enable'] ) ? 1 : 0 ) + ( ! empty( $svg['enable'] ) ? 1 : 0 );
	$inactive_count = $total - $active_count;
	$card_filter    = tcres_settings_get_current_card_filter( 'utilities' );
	?>
		<h2><?php esc_html_e( 'Utilities', 'tms-core-essentials' ); ?></h2>
		<?php
		tcres_settings_cards_panel_render_start( 'utilities' );
		tcres_settings_cards_render_filter(
			'admin',
			'utilities',
			$card_filter,
			$total,
			$active_count,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Utilities options', 'tms-core-essentials' ) );
		tcres_settings_card_render_field(
			'tcres-duplicate-posts-enable',
			$option . '[duplicate_posts][enable]',
			! empty( $duplicate['enable'] ),
			__( 'Duplicate posts', 'tms-core-essentials' ),
			__( 'Adds a Duplicate action on the admin list for the selected post types.', 'tms-core-essentials' ),
			static function () use ( $duplicate, $option ): void {
				tcres_settings_admin_render_duplicate_posts_config( $duplicate, $option );
			}
		);
		tcres_settings_card_render_field(
			'tcres-svg-uploads-enable',
			$option . '[svg_uploads][enable]',
			! empty( $svg['enable'] ),
			__( 'Allow SVG uploads', 'tms-core-essentials' ),
			__( 'Allow SVG files in the Media Library. Uploaded SVGs are always sanitized.', 'tms-core-essentials' ),
			static function () use ( $svg, $option ): void {
				tcres_settings_admin_render_svg_uploads_config( $svg, $option );
			}
		);
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


function tcres_settings_admin_render_tab_fields(): void {
	tcres_settings_admin_render_disable_gutenberg_panel();
	tcres_settings_admin_render_disable_features_panel();
	tcres_settings_admin_render_posts_list_panel();
	tcres_settings_admin_render_terms_list_panel();
	tcres_settings_admin_render_utilities_panel();
}
