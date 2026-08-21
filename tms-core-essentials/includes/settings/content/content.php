<?php
/**
 * Includes -> Settings -> Content -> Content
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_content_render_extend_search_config( array $group, string $option_name ): void {
	$selected   = isset( $group['post_types'] ) && is_array( $group['post_types'] )
		? $group['post_types']
		: array();
	$post_types = array_keys( tcres_settings_extend_search_post_type_defaults() );
	$meta_mode  = isset( $group['meta_mode'] ) ? (string) $group['meta_mode'] : 'public';
	if ( ! in_array( $meta_mode, array( 'public', 'keys', 'none' ), true ) ) :
		$meta_mode = 'public';
	endif;
	$meta_keys = isset( $group['meta_keys'] ) ? (string) $group['meta_keys'] : '';

	$meta_mode_choices = array(
		'public' => __( 'All public custom fields (exclude keys starting with _)', 'tms-core-essentials' ),
		'keys'   => __( 'Only specific meta keys', 'tms-core-essentials' ),
		'none'   => __( 'Do not search custom fields', 'tms-core-essentials' ),
	);
	?>
	<div>
		<h3 id="tcres-extend-search-post-types"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h3>
		<?php if ( empty( $post_types ) ) : ?>
			<p class="description"><?php esc_html_e( 'No searchable post types were found.', 'tms-core-essentials' ); ?></p>
		<?php else : ?>
			<fieldset aria-labelledby="tcres-extend-search-post-types">
				<ul class="checks">
					<?php foreach ( $post_types as $post_type ) : ?>
						<?php
						$object = get_post_type_object( $post_type );
						$label  = $object && isset( $object->labels->name )
							? $object->labels->name
							: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
						$field  = $option_name . '[extend_search][post_types][' . $post_type . ']';
						$id     = 'tcres-extend-search-type-' . sanitize_html_class( $post_type );
						?>
						<li><label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
							<input
								type="checkbox"
								id="<?php echo esc_attr( $id ); ?>"
								name="<?php echo esc_attr( $field ); ?>"
								value="1"
								<?php checked( ! empty( $selected[ $post_type ] ) ); ?> />
							<?php echo esc_html( $label ); ?>
						</label></li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		<?php endif; ?>
	</div>

	<hr>

	<div>
		<p class="inline">
			<label for="tcres-extend-search-meta-mode"><?php esc_html_e( 'Custom fields', 'tms-core-essentials' ); ?></label>
			<select
				name="<?php echo esc_attr( $option_name . '[extend_search][meta_mode]' ); ?>"
				id="tcres-extend-search-meta-mode"
				data-tcres-toggle-target="tcres-extend-search-meta-keys-options"
				data-tcres-toggle-value="keys"
				aria-controls="tcres-extend-search-meta-keys-options"
				aria-expanded="<?php echo $meta_mode === 'keys' ? 'true' : 'false'; ?>">
				<?php foreach ( $meta_mode_choices as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $meta_mode, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<div
			id="tcres-extend-search-meta-keys-options"
			class="tcres-settings-suboptions"
			<?php echo $meta_mode === 'keys' ? '' : ' hidden'; ?>>
			<p class="inline">
				<label for="tcres-extend-search-meta-keys"><?php esc_html_e( 'Meta keys', 'tms-core-essentials' ); ?></label>
			</p>
			<textarea
				class="large-text code"
				rows="3"
				name="<?php echo esc_attr( $option_name . '[extend_search][meta_keys]' ); ?>"
				id="tcres-extend-search-meta-keys"
				placeholder="<?php echo esc_attr( 'sku\n_custom_field' ); ?>"><?php echo esc_textarea( $meta_keys ); ?></textarea>
			<p class="description"><?php esc_html_e( 'One key per line, or separated by commas.', 'tms-core-essentials' ); ?></p>
		</div>
	</div>

	<hr>

	<div>
		<p class="inline">
			<label for="tcres-extend-search-taxonomies"><?php esc_html_e( 'Taxonomies', 'tms-core-essentials' ); ?></label>
			<input type="hidden" name="<?php echo esc_attr( $option_name . '[extend_search][search_taxonomies]' ); ?>" value="0" />
			<label class="has-checkbox" for="tcres-extend-search-taxonomies">
				<input
					type="checkbox"
					id="tcres-extend-search-taxonomies"
					name="<?php echo esc_attr( $option_name . '[extend_search][search_taxonomies]' ); ?>"
					value="1"
					<?php checked( ! empty( $group['search_taxonomies'] ) ); ?> />
				<?php esc_html_e( 'Also search term names and descriptions', 'tms-core-essentials' ); ?>
			</label>
		</p>
	</div>

	<hr>

	<div>
		<p class="inline">
			<label for="tcres-extend-search-prevent-empty"><?php esc_html_e( 'Empty searches', 'tms-core-essentials' ); ?></label>
			<input type="hidden" name="<?php echo esc_attr( $option_name . '[extend_search][prevent_empty_search]' ); ?>" value="0" />
			<label class="has-checkbox" for="tcres-extend-search-prevent-empty">
				<input
					type="checkbox"
					id="tcres-extend-search-prevent-empty"
					name="<?php echo esc_attr( $option_name . '[extend_search][prevent_empty_search]' ); ?>"
					value="1"
					<?php checked( ! empty( $group['prevent_empty_search'] ) ); ?> />
				<?php esc_html_e( 'Prevent empty search queries from returning results', 'tms-core-essentials' ); ?>
			</label>
		</p>
	</div>
	<?php
}


function tcres_settings_content_render_extend_search_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['extend_search'] ) && is_array( $settings['extend_search'] )
		? $settings['extend_search']
		: array();
	$option   = TCRES_OPTION_NAME;
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Extend search', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-extend-search-enable',
						$option . '[extend_search][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Extends frontend search beyond title and content for the selected post types.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-extend-search-options',
						)
					);
					?>
					<div
						id="tcres-extend-search-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<?php tcres_settings_content_render_extend_search_config( $group, $option ); ?>
						</div>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


/**
 * @return array<int, string>
 */
function tcres_settings_content_get_module_post_types(): array {
	$post_types = array();

	foreach ( tcres_post_types_get_included() as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$post_types[] = $post_type;
	endforeach;

	return $post_types;
}


/**
 * @return array<int, string>
 */
function tcres_settings_content_get_module_taxonomies(): array {
	$taxonomies = array();

	foreach ( tcres_taxonomies_get_included() as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->show_ui ) ) continue;

		$taxonomies[] = $taxonomy;
	endforeach;

	return $taxonomies;
}


/**
 * @param array<string, mixed> $group
 * @param array<int, string>   $items
 * @param string               $option
 * @param string               $group_key
 * @param string               $key_prefix
 * @param string               $id_prefix
 * @param string               $legend
 * @param string               $empty_message
 * @param string               $object_type post_type|taxonomy
 * @param string               $list_class Extra class(es) for the list element
 */
function tcres_settings_content_render_module_enable_list(
	array $group,
	array $items,
	string $option,
	string $group_key,
	string $key_prefix,
	string $id_prefix,
	string $legend,
	string $empty_message,
	string $object_type = 'post_type',
	string $list_class = ''
): void {
	if ( empty( $items ) ) :
		echo '<p class="description">' . esc_html( $empty_message ) . '</p>';
		return;
	endif;

	$list_classes = trim( 'checks ' . $list_class );
	?>
	<ul class="<?php echo esc_attr( $list_classes ); ?>" role="group" aria-label="<?php echo esc_attr( $legend ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<?php
			if ( $object_type === 'taxonomy' ) :
				$object = get_taxonomy( $item );
				$label  = $object && isset( $object->labels->name )
					? $object->labels->name
					: ucwords( str_replace( array( '-', '_' ), ' ', $item ) );
			else :
				$object = get_post_type_object( $item );
				$label  = $object && isset( $object->labels->name )
					? $object->labels->name
					: ucwords( str_replace( array( '-', '_' ), ' ', $item ) );
			endif;

			$key   = $key_prefix . $item;
			$field = $option . '[' . $group_key . '][' . $key . ']';
			$id    = $id_prefix . sanitize_html_class( $item );
			?>
			<li>
				<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
					<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0" />
					<input type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $field ); ?>"
						value="1"
						<?php checked( ! empty( $group[ $key ] ) ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}


function tcres_settings_content_render_related_content_usage(): void {
	$example = "echo tcres_related_content_get( array(\n\t'class'                 => 'related',\n\t'posts_per_page'        => 3,\n\t'image_size'            => 'medium',\n\t'title_tag'             => 'h3',\n\t'show_taxonomies'       => true,\n\t'taxonomies'            => array( 'category', 'post_tag' ),\n\t'taxonomies_display'    => 'grouped',\n\t'show_taxonomy_labels'  => true,\n\t'show_excerpt'          => true,\n\t'show_button'           => true,\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print related content for the current post (or a specific ID). Manual picks from the metabox come first; remaining slots are filled by taxonomy affinity, then random posts. The shortcode has no attributes and always uses the panel settings:', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Extra CSS class(es). The wrapper always includes tcres-related-content.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>posts_per_page</code>
					&mdash; <?php esc_html_e( 'Number of items (default from settings, max 12).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>taxonomy</code>
					&mdash; <?php esc_html_e( 'Primary taxonomy for auto picks (default: category).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>fallback_taxonomy</code>
					&mdash; <?php esc_html_e( 'Fallback taxonomy (default: post_tag).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>image_size</code>
					&mdash; <?php esc_html_e( 'Thumbnail size name, or none to hide images.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>title_tag</code>
					&mdash; <?php esc_html_e( 'Title tag: h2–h6, p or span.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>show_taxonomies</code>
					&mdash; <?php esc_html_e( 'Show term links on each related item when true.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>taxonomies</code>
					&mdash; <?php esc_html_e( 'Taxonomy slugs to display (defaults to the selected taxonomies in settings).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>taxonomies_display</code>
					&mdash; <?php esc_html_e( 'grouped (one list per taxonomy) or mixed (single flat list).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>show_taxonomy_labels</code>
					&mdash; <?php esc_html_e( 'Show the taxonomy name before each term list when display is grouped.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>show_excerpt</code>
					&mdash; <?php esc_html_e( 'Show excerpts when true.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>show_button</code>
					&mdash; <?php esc_html_e( 'Show the read-more button when true.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>button_label</code>
					&mdash; <?php esc_html_e( 'Read-more label.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>post_id</code>
					&mdash; <?php esc_html_e( 'Optional post ID (defaults to the current singular post).', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Meta key:', 'tms-core-essentials' ); ?>
				<code>tcres_related_content_posts</code>
			</p>
			<p class="description">
				<?php esc_html_e( 'Shortcode:', 'tms-core-essentials' ); ?>
				<code>[tcres-related-content]</code>
			</p>
	</details>
	<?php
}


function tcres_settings_content_render_related_content_panel(): void {
	$settings     = tcres_settings_get();
	$group        = isset( $settings['related_content'] ) && is_array( $settings['related_content'] )
		? $settings['related_content']
		: array();
	$option       = TCRES_OPTION_NAME;
	$post_types   = tcres_settings_content_get_module_post_types();
	$taxonomies   = tcres_settings_content_get_module_taxonomies();
	$image_sizes  = tcres_image_size_get_registered();
	$list_value   = isset( $group['post_type_list'] )
		? (string) $group['post_type_list']
		: 'current';
	$ppp          = isset( $group['posts_per_page'] )
		? (int) $group['posts_per_page']
		: 3;
	$image_size   = isset( $group['image_size'] )
		? (string) $group['image_size']
		: 'medium';
	$title_tag    = isset( $group['title_tag'] )
		? (string) $group['title_tag']
		: 'h3';
	$button_label = isset( $group['button_label'] )
		? (string) $group['button_label']
		: '';
	$tax_display  = isset( $group['taxonomies_display'] )
		? (string) $group['taxonomies_display']
		: 'grouped';
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Related content', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-related-content-enable',
						$option . '[related_content][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds a Related content metabox on enabled post types. Choose an automatic output location or print with tcres_related_content_get() / [tcres-related-content].', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-related-content-options',
						)
					);
					?>
					<div
						id="tcres-related-content-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h3 id="tcres-related-content-post-types"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h3>
							<fieldset aria-labelledby="tcres-related-content-post-types">
								<?php
								tcres_settings_content_render_module_enable_list(
									$group,
									$post_types,
									$option,
									'related_content',
									'',
									'tcres-related-content-',
									__( 'Related content post types', 'tms-core-essentials' ),
									__( 'No eligible post types were found.', 'tms-core-essentials' ),
									'post_type'
								);
								?>
							</fieldset>
						</div>

						<hr>

						<div>
							<p class="inline">
								<label for="tcres-related-content-post-type-list"><?php esc_html_e( 'Post type list', 'tms-core-essentials' ); ?></label>
								<select name="<?php echo esc_attr( $option . '[related_content][post_type_list]' ); ?>" id="tcres-related-content-post-type-list">
									<option value="all" <?php selected( $list_value, 'all' ); ?>><?php esc_html_e( 'All post types', 'tms-core-essentials' ); ?></option>
									<option value="current" <?php selected( $list_value, 'current' ); ?>><?php esc_html_e( 'Current post type', 'tms-core-essentials' ); ?></option>
									<option value="others" <?php selected( $list_value, 'others' ); ?>><?php esc_html_e( 'Other post types', 'tms-core-essentials' ); ?></option>
								</select>
								<span class="description"><?php esc_html_e( 'Which posts appear in the metabox selectors.', 'tms-core-essentials' ); ?></span>
							</p>
						</div>

						<div>
							<p class="inline">
								<label for="tcres-related-content-posts-per-page"><?php esc_html_e( 'Posts number', 'tms-core-essentials' ); ?></label>
								<input
									type="number"
									min="1"
									max="12"
									step="1"
									name="<?php echo esc_attr( $option . '[related_content][posts_per_page]' ); ?>"
									id="tcres-related-content-posts-per-page"
									value="<?php echo esc_attr( (string) $ppp ); ?>"
									class="small-text" />
							</p>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Post item', 'tms-core-essentials' ); ?></h3>
							<p class="inline">
								<label for="tcres-related-content-image-size"><?php esc_html_e( 'Image size', 'tms-core-essentials' ); ?></label>
								<select name="<?php echo esc_attr( $option . '[related_content][image_size]' ); ?>" id="tcres-related-content-image-size">
									<option value="none" <?php selected( $image_size, 'none' ); ?>><?php esc_html_e( 'None', 'tms-core-essentials' ); ?></option>
									<?php foreach ( $image_sizes as $size ) : ?>
										<?php
										if ( ! is_array( $size ) ) continue;
										$value = isset( $size['value'] ) ? (string) $size['value'] : '';
										$label = isset( $size['label'] ) ? (string) $size['label'] : $value;
										if ( $value === '' ) continue;
										?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $image_size, $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p class="inline">
								<label for="tcres-related-content-title-tag"><?php esc_html_e( 'Title tag', 'tms-core-essentials' ); ?></label>
								<select name="<?php echo esc_attr( $option . '[related_content][title_tag]' ); ?>" id="tcres-related-content-title-tag">
									<?php foreach ( array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' ) as $tag ) : ?>
										<option value="<?php echo esc_attr( $tag ); ?>" <?php selected( $title_tag, $tag ); ?>>&lt;<?php echo esc_html( $tag ); ?>&gt;</option>
									<?php endforeach; ?>
								</select>
							</p>
							<div>
								<label class="has-checkbox">
										<input
											type="checkbox"
											id="tcres-related-content-show-taxonomies"
											name="<?php echo esc_attr( $option . '[related_content][show_taxonomies]' ); ?>"
											value="1"
											data-tcres-toggle-target="tcres-related-content-taxonomies-options"
											aria-controls="tcres-related-content-taxonomies-options"
											aria-expanded="<?php echo ! empty( $group['show_taxonomies'] ) ? 'true' : 'false'; ?>"
											<?php checked( ! empty( $group['show_taxonomies'] ) ); ?> />
										<?php esc_html_e( 'Show taxonomies', 'tms-core-essentials' ); ?>
									</label>
								<div
									id="tcres-related-content-taxonomies-options"
									class="tcres-settings-suboptions"
									<?php echo ! empty( $group['show_taxonomies'] ) ? '' : ' hidden'; ?>>
									<h4><?php esc_html_e( 'Taxonomies to display', 'tms-core-essentials' ); ?></h4>
									<?php
									tcres_settings_content_render_module_enable_list(
										$group,
										$taxonomies,
										$option,
										'related_content',
										'tax_',
										'tcres-related-content-tax-',
										__( 'Related content taxonomies to display', 'tms-core-essentials' ),
										__( 'No eligible taxonomies were found.', 'tms-core-essentials' ),
										'taxonomy',
										'is-horizontal'
									);
									?>

									<hr>

									<p class="inline">
										<label for="tcres-related-content-taxonomies-display"><?php esc_html_e( 'Taxonomies display', 'tms-core-essentials' ); ?></label>
										<select
											name="<?php echo esc_attr( $option . '[related_content][taxonomies_display]' ); ?>"
											id="tcres-related-content-taxonomies-display"
											data-tcres-toggle-target="tcres-related-content-taxonomy-labels-options"
											data-tcres-toggle-value="grouped"
											aria-controls="tcres-related-content-taxonomy-labels-options"
											aria-expanded="<?php echo $tax_display === 'grouped' ? 'true' : 'false'; ?>">
											<option value="grouped" <?php selected( $tax_display, 'grouped' ); ?>><?php esc_html_e( 'Grouped (one list per taxonomy)', 'tms-core-essentials' ); ?></option>
											<option value="mixed" <?php selected( $tax_display, 'mixed' ); ?>><?php esc_html_e( 'Mixed (single flat list)', 'tms-core-essentials' ); ?></option>
										</select>
									</p>
									<div
										id="tcres-related-content-taxonomy-labels-options"
										class="tcres-settings-suboptions"
										<?php echo $tax_display === 'grouped' ? '' : ' hidden'; ?>>
										<label class="has-checkbox">
												<input type="hidden" name="<?php echo esc_attr( $option . '[related_content][show_taxonomy_labels]' ); ?>" value="0" />
												<input
													type="checkbox"
													id="tcres-related-content-show-taxonomy-labels"
													name="<?php echo esc_attr( $option . '[related_content][show_taxonomy_labels]' ); ?>"
													value="1"
													<?php checked( ! empty( $group['show_taxonomy_labels'] ) ); ?> />
												<?php esc_html_e( 'Show taxonomy names', 'tms-core-essentials' ); ?>
											</label>
									</div>
								</div>
							</div>
							<label class="has-checkbox">
									<input
										type="checkbox"
										name="<?php echo esc_attr( $option . '[related_content][show_excerpt]' ); ?>"
										value="1"
										<?php checked( ! empty( $group['show_excerpt'] ) ); ?> />
									<?php esc_html_e( 'Show excerpt', 'tms-core-essentials' ); ?>
								</label>
							<div>
								<label class="has-checkbox">
										<input
											type="checkbox"
											id="tcres-related-content-show-button"
											name="<?php echo esc_attr( $option . '[related_content][show_button]' ); ?>"
											value="1"
											data-tcres-toggle-target="tcres-related-content-button-options"
											aria-controls="tcres-related-content-button-options"
											aria-expanded="<?php echo ! empty( $group['show_button'] ) ? 'true' : 'false'; ?>"
											<?php checked( ! empty( $group['show_button'] ) ); ?> />
										<?php esc_html_e( 'Show button', 'tms-core-essentials' ); ?>
									</label>
								<div
									id="tcres-related-content-button-options"
									class="tcres-settings-suboptions"
									<?php echo ! empty( $group['show_button'] ) ? '' : ' hidden'; ?>>
									<p class="inline">
										<label for="tcres-related-content-button-label"><?php esc_html_e( 'Button label', 'tms-core-essentials' ); ?></label>
										<input
											type="text"
											class="regular-text"
											name="<?php echo esc_attr( $option . '[related_content][button_label]' ); ?>"
											id="tcres-related-content-button-label"
											value="<?php echo esc_attr( $button_label ); ?>"
											placeholder="<?php esc_attr_e( 'Read more', 'tms-core-essentials' ); ?>" />
										<?php if ( tcres_multilingual_is_active() ) : ?>
											<span class="description"><?php esc_html_e( 'Translatable: saved for the current admin language.', 'tms-core-essentials' ); ?></span>
										<?php endif; ?>
									</p>
								</div>
							</div>
						</div>

						<?php
						tcres_settings_render_output_location(
							array(
								'group_key'    => 'related_content',
								'option_name'  => $option,
								'id_prefix'    => 'tcres-related-content',
								'location'     => isset( $group['output_location'] ) ? (string) $group['output_location'] : 'manual',
								'target'       => isset( $group['output_target'] ) ? (string) $group['output_target'] : '',
								'choices'      => tcres_output_location_choices_for( tcres_output_location_keys_related_content() ),
								'usage_render' => 'tcres_settings_content_render_related_content_usage',
							)
						);
						?>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_content_render_breadcrumbs_usage(): void {
	$example = "echo tcres_breadcrumb_get( array(\n\t'separator' => '/',\n\t'class'     => 'site-breadcrumbs',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print breadcrumbs on the frontend. Settings from this panel are the defaults; function args override them. The shortcode has no attributes and always uses the panel settings:', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>separator</code>
					&mdash; <?php esc_html_e( 'Text between crumbs (default from settings).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>show_home</code>, <code>home_label</code>, <code>home_display</code>
					&mdash; <?php esc_html_e( 'Home crumb: icon_label (default), label, or icon.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>blog_page</code>, <code>blog_taxonomy</code>
					&mdash; <?php esc_html_e( 'Blog / posts trail (0 = automatic Posts page, -1 = none; empty taxonomy = none).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>cpt</code>
					&mdash; <?php esc_html_e( 'Per post type: page, taxonomy (None omits that crumb).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Extra CSS class(es). The nav always includes tcres-breadcrumbs.', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Shortcode:', 'tms-core-essentials' ); ?>
				<code>[tcres-breadcrumb]</code>
			</p>
	</details>
	<?php
}


/**
 * @param array<string, string> $extra Extra args for wp_dropdown_pages (name, id, selected, …)
 */
function tcres_settings_content_render_breadcrumbs_page_dropdown( array $extra ): void {
	$args = array_merge(
		array(
			'echo'              => 1,
			'show_option_none'  => __( 'None', 'tms-core-essentials' ),
			'option_none_value' => '0',
		),
		$extra
	);
	wp_dropdown_pages( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP core markup
}


/**
 * Blog page select: Automatic (0), None (-1), then pages.
 */
function tcres_settings_content_render_breadcrumbs_blog_page_dropdown(
	string $name,
	string $id,
	int $selected
): void {
	$pages = get_pages(
		array(
			'sort_column' => 'menu_order,post_title',
		)
	);
	?>
	<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
		<option value="0" <?php selected( $selected, 0 ); ?>><?php esc_html_e( 'Automatic (Posts page)', 'tms-core-essentials' ); ?></option>
		<option value="-1" <?php selected( $selected, -1 ); ?>><?php esc_html_e( 'None', 'tms-core-essentials' ); ?></option>
		<?php foreach ( $pages as $page ) : ?>
			<?php if ( ! $page instanceof WP_Post ) continue; ?>
			<option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $selected, (int) $page->ID ); ?>>
				<?php echo esc_html( $page->post_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}


/**
 * @param string               $name
 * @param string               $id
 * @param string               $selected
 * @param array<int, string>   $taxonomy_slugs
 * @param bool                 $allow_empty
 */
function tcres_settings_content_render_breadcrumbs_taxonomy_select(
	string $name,
	string $id,
	string $selected,
	array $taxonomy_slugs,
	bool $allow_empty = false
): void {
	?>
	<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
		<?php if ( $allow_empty ) : ?>
			<option value="" <?php selected( $selected, '' ); ?>><?php esc_html_e( 'None', 'tms-core-essentials' ); ?></option>
		<?php endif; ?>
		<?php foreach ( $taxonomy_slugs as $taxonomy ) : ?>
			<?php
			$object = get_taxonomy( $taxonomy );
			$label  = $object && isset( $object->labels->name )
				? $object->labels->name
				: ucwords( str_replace( array( '-', '_' ), ' ', $taxonomy ) );
			?>
			<option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $selected, $taxonomy ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}


function tcres_settings_content_render_breadcrumbs_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['breadcrumbs'] ) && is_array( $settings['breadcrumbs'] )
		? $settings['breadcrumbs']
		: array();
	$option   = TCRES_OPTION_NAME;
	$cpt_rows = isset( $group['cpt'] ) && is_array( $group['cpt'] )
		? $group['cpt']
		: array();

	$separator     = isset( $group['separator'] ) ? (string) $group['separator'] : '/';
	$home_label    = isset( $group['home_label'] ) ? (string) $group['home_label'] : '';
	$home_display  = isset( $group['home_display'] ) ? (string) $group['home_display'] : 'icon_label';
	$blog_page     = isset( $group['blog_page'] ) ? (int) $group['blog_page'] : 0;
	$blog_taxonomy = isset( $group['blog_taxonomy'] ) ? (string) $group['blog_taxonomy'] : 'category';
	$show_home     = ! empty( $group['show_home'] );
	if ( ! in_array( $home_display, array( 'icon_label', 'label', 'icon' ), true ) ) :
		$home_display = 'icon_label';
	endif;

	$post_taxonomies = array();
	foreach ( get_object_taxonomies( 'post', 'names' ) as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		$post_taxonomies[] = $taxonomy;
	endforeach;
	if ( empty( $post_taxonomies ) ) :
		$post_taxonomies = array( 'category' );
	endif;
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Breadcrumbs', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-breadcrumbs-enable',
						$option . '[breadcrumbs][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds a frontend breadcrumbs helper configurable from this panel. Choose an automatic output location or print with tcres_breadcrumb_get() / [tcres-breadcrumb].', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-breadcrumbs-options',
						)
					);
					?>
					<div
						id="tcres-breadcrumbs-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h3><?php esc_html_e( 'General', 'tms-core-essentials' ); ?></h3>
							<p class="inline">
								<label for="tcres-breadcrumbs-separator"><?php esc_html_e( 'Separator', 'tms-core-essentials' ); ?></label>
								<input
									type="text"
									class="small-text"
									name="<?php echo esc_attr( $option . '[breadcrumbs][separator]' ); ?>"
									id="tcres-breadcrumbs-separator"
									value="<?php echo esc_attr( $separator ); ?>"
									placeholder="/" />
							</p>
							<div>
								<label class="has-checkbox">
										<input
											type="checkbox"
											id="tcres-breadcrumbs-show-home"
											name="<?php echo esc_attr( $option . '[breadcrumbs][show_home]' ); ?>"
											value="1"
											data-tcres-toggle-target="tcres-breadcrumbs-home-options"
											aria-controls="tcres-breadcrumbs-home-options"
											aria-expanded="<?php echo $show_home ? 'true' : 'false'; ?>"
											<?php checked( $show_home ); ?> />
										<?php esc_html_e( 'Show home', 'tms-core-essentials' ); ?>
									</label>
								<div
									id="tcres-breadcrumbs-home-options"
									class="tcres-settings-suboptions"
									<?php echo $show_home ? '' : ' hidden'; ?>>
									<p class="inline">
										<label for="tcres-breadcrumbs-home-display"><?php esc_html_e( 'Home display', 'tms-core-essentials' ); ?></label>
										<select
											name="<?php echo esc_attr( $option . '[breadcrumbs][home_display]' ); ?>"
											id="tcres-breadcrumbs-home-display"
											data-tcres-toggle-target="tcres-breadcrumbs-home-label-options"
											data-tcres-toggle-unless-value="icon"
											aria-controls="tcres-breadcrumbs-home-label-options"
											aria-expanded="<?php echo $home_display !== 'icon' ? 'true' : 'false'; ?>">
											<option value="icon_label" <?php selected( $home_display, 'icon_label' ); ?>><?php esc_html_e( 'Icon and label', 'tms-core-essentials' ); ?></option>
											<option value="label" <?php selected( $home_display, 'label' ); ?>><?php esc_html_e( 'Label only', 'tms-core-essentials' ); ?></option>
											<option value="icon" <?php selected( $home_display, 'icon' ); ?>><?php esc_html_e( 'Icon only', 'tms-core-essentials' ); ?></option>
										</select>
									</p>
									<div
										id="tcres-breadcrumbs-home-label-options"
										class="tcres-settings-suboptions"
										<?php echo $home_display !== 'icon' ? '' : ' hidden'; ?>>
										<p class="inline">
											<label for="tcres-breadcrumbs-home-label"><?php esc_html_e( 'Home label', 'tms-core-essentials' ); ?></label>
											<input
												type="text"
												class="regular-text"
												name="<?php echo esc_attr( $option . '[breadcrumbs][home_label]' ); ?>"
												id="tcres-breadcrumbs-home-label"
												value="<?php echo esc_attr( $home_label ); ?>" />
											<span class="description"><?php esc_html_e( 'Leave empty to use the front page title.', 'tms-core-essentials' ); ?></span>
											<?php if ( tcres_multilingual_is_active() ) : ?>
												<span class="description"><?php esc_html_e( 'Translatable: saved for the current admin language.', 'tms-core-essentials' ); ?></span>
											<?php endif; ?>
										</p>
									</div>
								</div>
							</div>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h3>
							<p class="description"><?php
								printf(
									/* translators: %s: field label "Parent page" */
									esc_html__( '%s is the optional page that appears in the trail before items of this type.', 'tms-core-essentials' ),
									'<strong>' . esc_html__( 'Parent page', 'tms-core-essentials' ) . '</strong>'
								);
							?></p>
							<p class="description"><?php
								printf(
									/* translators: %s: field label "Taxonomy" */
									esc_html__( '%s is the optional term trail between that page and the item.', 'tms-core-essentials' ),
									'<strong>' . esc_html__( 'Taxonomy', 'tms-core-essentials' ) . '</strong>'
								);
							?></p>

							<div>
								<h4 id="tcres-breadcrumbs-post-type-post">
									<?php esc_html_e( 'Blog / posts', 'tms-core-essentials' ); ?>
									<code>post</code>
								</h4>
								<fieldset aria-labelledby="tcres-breadcrumbs-post-type-post">
									<p class="inline">
										<label for="tcres-breadcrumbs-blog-page"><?php esc_html_e( 'Parent page', 'tms-core-essentials' ); ?></label>
										<?php
										tcres_settings_content_render_breadcrumbs_blog_page_dropdown(
											$option . '[breadcrumbs][blog_page]',
											'tcres-breadcrumbs-blog-page',
											$blog_page
										);
										?>
										<span class="description"><?php esc_html_e( 'Automatic uses the Reading settings Posts page; None omits the parent page.', 'tms-core-essentials' ); ?></span>
									</p>
									<p class="inline">
										<label for="tcres-breadcrumbs-blog-taxonomy"><?php esc_html_e( 'Taxonomy', 'tms-core-essentials' ); ?></label>
										<?php
										tcres_settings_content_render_breadcrumbs_taxonomy_select(
											$option . '[breadcrumbs][blog_taxonomy]',
											'tcres-breadcrumbs-blog-taxonomy',
											$blog_taxonomy,
											$post_taxonomies,
											true
										);
										?>
									</p>
								</fieldset>
							</div>

							<?php
							$cpt_slugs = tcres_settings_breadcrumbs_get_cpt_slugs();
							if ( empty( $cpt_slugs ) ) :
								?>
								<p class="description"><?php esc_html_e( 'No custom post types with a UI were found.', 'tms-core-essentials' ); ?></p>
								<?php
							else :
								foreach ( $cpt_slugs as $post_type ) :
									$object = get_post_type_object( $post_type );
									$label  = $object && isset( $object->labels->name )
										? $object->labels->name
										: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
									$row    = isset( $cpt_rows[ $post_type ] ) && is_array( $cpt_rows[ $post_type ] )
										? $cpt_rows[ $post_type ]
										: array();
									$cpt_page = isset( $row['page'] ) ? (int) $row['page'] : 0;
									$cpt_tax  = isset( $row['taxonomy'] ) ? (string) $row['taxonomy'] : '';
									$cpt_class = sanitize_html_class( $post_type );
									$heading_id = 'tcres-breadcrumbs-post-type-' . $cpt_class;

									$cpt_taxonomies = array();
									foreach ( get_object_taxonomies( $post_type, 'names' ) as $taxonomy ) :
										$tax_object = get_taxonomy( $taxonomy );
										if ( ! $tax_object || empty( $tax_object->show_ui ) ) continue;
										$cpt_taxonomies[] = $taxonomy;
									endforeach;
									?>
									<div>
										<h4 id="<?php echo esc_attr( $heading_id ); ?>">
											<?php echo esc_html( $label ); ?>
											<code><?php echo esc_html( $post_type ); ?></code>
										</h4>
										<fieldset aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
											<p class="inline">
												<label for="tcres-breadcrumbs-cpt-<?php echo esc_attr( $cpt_class ); ?>-page"><?php esc_html_e( 'Parent page', 'tms-core-essentials' ); ?></label>
												<?php
												tcres_settings_content_render_breadcrumbs_page_dropdown(
													array(
														'name'              => $option . '[breadcrumbs][cpt][' . $post_type . '][page]',
														'id'                => 'tcres-breadcrumbs-cpt-' . $cpt_class . '-page',
														'selected'          => $cpt_page,
														'show_option_none'  => __( 'None', 'tms-core-essentials' ),
														'option_none_value' => '0',
													)
												);
												?>
											</p>
											<p class="inline">
												<label for="tcres-breadcrumbs-cpt-<?php echo esc_attr( $cpt_class ); ?>-taxonomy"><?php esc_html_e( 'Taxonomy', 'tms-core-essentials' ); ?></label>
												<?php
												if ( empty( $cpt_taxonomies ) ) :
													?>
													<span class="description"><?php esc_html_e( 'No taxonomies available for this post type.', 'tms-core-essentials' ); ?></span>
													<input type="hidden" name="<?php echo esc_attr( $option . '[breadcrumbs][cpt][' . $post_type . '][taxonomy]' ); ?>" value="" />
													<?php
												else :
													tcres_settings_content_render_breadcrumbs_taxonomy_select(
														$option . '[breadcrumbs][cpt][' . $post_type . '][taxonomy]',
														'tcres-breadcrumbs-cpt-' . $cpt_class . '-taxonomy',
														$cpt_tax,
														$cpt_taxonomies,
														true
													);
												endif;
												?>
											</p>
										</fieldset>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>

						<?php
						tcres_settings_render_output_location(
							array(
								'group_key'    => 'breadcrumbs',
								'option_name'  => $option,
								'id_prefix'    => 'tcres-breadcrumbs',
								'location'     => isset( $group['output_location'] ) ? (string) $group['output_location'] : 'manual',
								'target'       => isset( $group['output_target'] ) ? (string) $group['output_target'] : '',
								'choices'      => tcres_output_location_choices_for( tcres_output_location_keys_breadcrumbs() ),
								'usage_render' => 'tcres_settings_content_render_breadcrumbs_usage',
							)
						);
						?>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


/**
 * Blog / CPT anchor page select for Sitemap.
 * $empty_label is the option for value 0 (Reading default or “select a page”).
 */
function tcres_settings_content_render_sitemap_page_dropdown(
	string $name,
	string $id,
	int $selected,
	string $empty_label
): void {
	$pages = get_pages(
		array(
			'sort_column' => 'menu_order,post_title',
		)
	);
	?>
	<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
		<option value="0" <?php selected( $selected, 0 ); ?>><?php echo esc_html( $empty_label ); ?></option>
		<?php foreach ( $pages as $page ) : ?>
			<?php if ( ! $page instanceof WP_Post ) continue; ?>
			<option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $selected, (int) $page->ID ); ?>>
				<?php echo esc_html( $page->post_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}


function tcres_settings_content_render_sitemap_usage(): void {
	$example = "echo tcres_sitemap_get( array(\n\t'max_depth' => 3,\n\t'class'     => 'site-sitemap',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print an HTML sitemap on the frontend. Settings from this panel are the defaults; function args override them. The shortcode has no attributes and always uses the panel settings:', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>hide_empty</code>, <code>show_list_bullets</code>, <code>max_depth</code>, <code>page_sort</code>
					&mdash; <?php esc_html_e( 'General list behaviour (page tree).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>blog</code>, <code>blog_parent_page_id</code>, <code>blog_taxonomy</code>, <code>blog_show_taxonomy</code>, <code>blog_show_posts</code>, <code>blog_max_depth</code>
					&mdash; <?php esc_html_e( 'Blog section under an anchor page (0 = Posts page from Reading settings).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>cpt</code>
					&mdash; <?php esc_html_e( 'Per post type: enable, parent_page_id, taxonomy, show_taxonomy, show_posts, max_depth.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Extra CSS class(es). The wrapper always includes tcres-sitemap.', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Shortcode:', 'tms-core-essentials' ); ?>
				<code>[tcres-sitemap]</code>
			</p>
	</details>
	<?php
}


function tcres_settings_content_render_sitemap_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['sitemap'] ) && is_array( $settings['sitemap'] )
		? $settings['sitemap']
		: array();
	$option   = TCRES_OPTION_NAME;
	$cpt_rows = isset( $group['cpt'] ) && is_array( $group['cpt'] )
		? $group['cpt']
		: array();

	$hide_empty        = ! empty( $group['hide_empty'] );
	$show_list_bullets = ! empty( $group['show_list_bullets'] );
	$max_depth         = isset( $group['max_depth'] ) ? absint( $group['max_depth'] ) : 3;
	if ( $max_depth < 1 ) :
		$max_depth = 3;
	endif;
	$page_sort = isset( $group['page_sort'] ) ? (string) $group['page_sort'] : 'menu_order';
	if ( ! in_array( $page_sort, array( 'menu_order', 'alphabetical' ), true ) ) :
		$page_sort = 'menu_order';
	endif;

	$blog                = ! empty( $group['blog'] );
	$blog_parent_page_id = isset( $group['blog_parent_page_id'] ) ? absint( $group['blog_parent_page_id'] ) : 0;
	$blog_taxonomy       = isset( $group['blog_taxonomy'] ) ? (string) $group['blog_taxonomy'] : 'category';
	$blog_show_taxonomy  = ! empty( $group['blog_show_taxonomy'] );
	$blog_show_posts     = ! empty( $group['blog_show_posts'] );
	$blog_max_depth      = isset( $group['blog_max_depth'] ) ? (string) $group['blog_max_depth'] : '';

	$post_taxonomies = array();
	foreach ( get_object_taxonomies( 'post', 'names' ) as $taxonomy ) :
		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->show_ui ) ) continue;
		$post_taxonomies[] = $taxonomy;
	endforeach;
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Sitemap', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-sitemap-enable',
						$option . '[sitemap][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds a frontend HTML sitemap helper configurable from this panel. Print with tcres_sitemap_get() or the shortcode [tcres-sitemap].', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-sitemap-options',
						)
					);
					?>
					<div
						id="tcres-sitemap-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h3><?php esc_html_e( 'General', 'tms-core-essentials' ); ?></h3>
							<ul class="checks">
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											id="tcres-sitemap-hide-empty"
											name="<?php echo esc_attr( $option . '[sitemap][hide_empty]' ); ?>"
											value="1"
											<?php checked( $hide_empty ); ?> />
										<?php esc_html_e( 'Hide empty taxonomy terms', 'tms-core-essentials' ); ?>
									</label>
								</li>
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											id="tcres-sitemap-show-list-bullets"
											name="<?php echo esc_attr( $option . '[sitemap][show_list_bullets]' ); ?>"
											value="1"
											<?php checked( $show_list_bullets ); ?> />
										<?php esc_html_e( 'Show list bullets', 'tms-core-essentials' ); ?>
									</label>
								</li>
							</ul>
							<p class="inline">
								<label for="tcres-sitemap-page-sort"><?php esc_html_e( 'Page sort', 'tms-core-essentials' ); ?></label>
								<select
									name="<?php echo esc_attr( $option . '[sitemap][page_sort]' ); ?>"
									id="tcres-sitemap-page-sort">
									<option value="menu_order" <?php selected( $page_sort, 'menu_order' ); ?>><?php esc_html_e( 'Menu order', 'tms-core-essentials' ); ?></option>
									<option value="alphabetical" <?php selected( $page_sort, 'alphabetical' ); ?>><?php esc_html_e( 'Alphabetical (title)', 'tms-core-essentials' ); ?></option>
								</select>
							</p>
							<p class="inline">
								<label for="tcres-sitemap-max-depth"><?php esc_html_e( 'Max depth (page hierarchy)', 'tms-core-essentials' ); ?></label>
								<input
									type="number"
									class="small-text"
									min="1"
									step="1"
									name="<?php echo esc_attr( $option . '[sitemap][max_depth]' ); ?>"
									id="tcres-sitemap-max-depth"
									value="<?php echo esc_attr( (string) $max_depth ); ?>" />
							</p>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h3>

							<div>
								<h4 id="tcres-sitemap-post-type-post">
									<?php esc_html_e( 'Blog / posts', 'tms-core-essentials' ); ?>
									<code>post</code>
								</h4>
								<fieldset aria-labelledby="tcres-sitemap-post-type-post">
									<label class="has-checkbox">
											<input
												type="checkbox"
												id="tcres-sitemap-blog"
												name="<?php echo esc_attr( $option . '[sitemap][blog]' ); ?>"
												value="1"
												data-tcres-toggle-target="tcres-sitemap-blog-options"
												aria-controls="tcres-sitemap-blog-options"
												aria-expanded="<?php echo $blog ? 'true' : 'false'; ?>"
												<?php checked( $blog ); ?> />
											<?php esc_html_e( 'Include section in sitemap', 'tms-core-essentials' ); ?>
										</label>
									<div
										id="tcres-sitemap-blog-options"
										class="tcres-settings-suboptions"
										<?php echo $blog ? '' : ' hidden'; ?>>
										<p class="inline">
											<label for="tcres-sitemap-blog-parent-page"><?php esc_html_e( 'Anchor page in sitemap', 'tms-core-essentials' ); ?></label>
											<?php
											tcres_settings_content_render_sitemap_page_dropdown(
												$option . '[sitemap][blog_parent_page_id]',
												'tcres-sitemap-blog-parent-page',
												$blog_parent_page_id,
												__( 'Default (Settings → Reading)', 'tms-core-essentials' )
											);
											?>
										</p>
										<p class="inline">
											<label for="tcres-sitemap-blog-taxonomy"><?php esc_html_e( 'Grouping taxonomy', 'tms-core-essentials' ); ?></label>
											<?php
											if ( empty( $post_taxonomies ) ) :
												?>
												<span class="description"><?php esc_html_e( 'No taxonomies available for posts.', 'tms-core-essentials' ); ?></span>
												<input type="hidden" name="<?php echo esc_attr( $option . '[sitemap][blog_taxonomy]' ); ?>" value="" />
												<?php
											else :
												tcres_settings_content_render_breadcrumbs_taxonomy_select(
													$option . '[sitemap][blog_taxonomy]',
													'tcres-sitemap-blog-taxonomy',
													$blog_taxonomy,
													$post_taxonomies,
													false
												);
											endif;
											?>
										</p>
										<ul class="checks">
											<li>
												<label class="has-checkbox">
													<input
														type="checkbox"
														id="tcres-sitemap-blog-show-taxonomy"
														name="<?php echo esc_attr( $option . '[sitemap][blog_show_taxonomy]' ); ?>"
														value="1"
														<?php checked( $blog_show_taxonomy ); ?> />
													<?php esc_html_e( 'Show taxonomy', 'tms-core-essentials' ); ?>
												</label>
											</li>
											<li>
												<label class="has-checkbox">
													<input
														type="checkbox"
														id="tcres-sitemap-blog-show-posts"
														name="<?php echo esc_attr( $option . '[sitemap][blog_show_posts]' ); ?>"
														value="1"
														<?php checked( $blog_show_posts ); ?> />
													<?php esc_html_e( 'Show entries', 'tms-core-essentials' ); ?>
												</label>
											</li>
										</ul>
										<p class="inline">
											<label for="tcres-sitemap-blog-max-depth"><?php esc_html_e( 'Section max depth (empty = global)', 'tms-core-essentials' ); ?></label>
											<input
												type="text"
												class="small-text"
												inputmode="numeric"
												name="<?php echo esc_attr( $option . '[sitemap][blog_max_depth]' ); ?>"
												id="tcres-sitemap-blog-max-depth"
												value="<?php echo esc_attr( $blog_max_depth ); ?>"
												placeholder="" />
										</p>
									</div>
								</fieldset>
							</div>

							<?php
							$cpt_slugs = tcres_settings_breadcrumbs_get_cpt_slugs();
							if ( empty( $cpt_slugs ) ) :
								?>
								<p class="description"><?php esc_html_e( 'No public custom post types are registered.', 'tms-core-essentials' ); ?></p>
								<?php
							else :
								foreach ( $cpt_slugs as $post_type ) :
									$object = get_post_type_object( $post_type );
									$label  = $object && isset( $object->labels->name )
										? $object->labels->name
										: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) );
									$row    = isset( $cpt_rows[ $post_type ] ) && is_array( $cpt_rows[ $post_type ] )
										? $cpt_rows[ $post_type ]
										: array();
									$cpt_enable         = ! empty( $row['enable'] );
									$cpt_parent_page_id = isset( $row['parent_page_id'] ) ? absint( $row['parent_page_id'] ) : 0;
									$cpt_tax            = isset( $row['taxonomy'] ) ? (string) $row['taxonomy'] : '';
									$cpt_show_taxonomy  = ! empty( $row['show_taxonomy'] );
									$cpt_show_posts     = ! empty( $row['show_posts'] );
									$cpt_max_depth      = isset( $row['max_depth'] ) ? (string) $row['max_depth'] : '';
									$cpt_class          = sanitize_html_class( $post_type );
									$cpt_options_id     = 'tcres-sitemap-cpt-' . $cpt_class . '-options';
									$heading_id         = 'tcres-sitemap-post-type-' . $cpt_class;

									$cpt_taxonomies = array();
									foreach ( get_object_taxonomies( $post_type, 'names' ) as $taxonomy ) :
										$tax_object = get_taxonomy( $taxonomy );
										if ( ! $tax_object || empty( $tax_object->show_ui ) ) continue;
										$cpt_taxonomies[] = $taxonomy;
									endforeach;
									?>
									<div>
										<h4 id="<?php echo esc_attr( $heading_id ); ?>">
											<?php echo esc_html( $label ); ?>
											<code><?php echo esc_html( $post_type ); ?></code>
										</h4>
										<fieldset aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
											<label class="has-checkbox">
													<input
														type="checkbox"
														id="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-enable"
														name="<?php echo esc_attr( $option . '[sitemap][cpt][' . $post_type . '][enable]' ); ?>"
														value="1"
														data-tcres-toggle-target="<?php echo esc_attr( $cpt_options_id ); ?>"
														aria-controls="<?php echo esc_attr( $cpt_options_id ); ?>"
														aria-expanded="<?php echo $cpt_enable ? 'true' : 'false'; ?>"
														<?php checked( $cpt_enable ); ?> />
													<?php esc_html_e( 'Include section in sitemap', 'tms-core-essentials' ); ?>
												</label>
											<div
												id="<?php echo esc_attr( $cpt_options_id ); ?>"
												class="tcres-settings-suboptions"
												<?php echo $cpt_enable ? '' : ' hidden'; ?>>
												<p class="inline">
													<label for="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-parent"><?php esc_html_e( 'Anchor page in sitemap', 'tms-core-essentials' ); ?></label>
													<?php
													tcres_settings_content_render_sitemap_page_dropdown(
														$option . '[sitemap][cpt][' . $post_type . '][parent_page_id]',
														'tcres-sitemap-cpt-' . $cpt_class . '-parent',
														$cpt_parent_page_id,
														__( '— Select a page —', 'tms-core-essentials' )
													);
													?>
												</p>
												<?php if ( ! empty( $cpt_taxonomies ) ) : ?>
													<p class="inline">
														<label for="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-taxonomy"><?php esc_html_e( 'Grouping taxonomy', 'tms-core-essentials' ); ?></label>
														<?php
														tcres_settings_content_render_breadcrumbs_taxonomy_select(
															$option . '[sitemap][cpt][' . $post_type . '][taxonomy]',
															'tcres-sitemap-cpt-' . $cpt_class . '-taxonomy',
															$cpt_tax,
															$cpt_taxonomies,
															false
														);
														?>
													</p>
												<?php else : ?>
													<input type="hidden" name="<?php echo esc_attr( $option . '[sitemap][cpt][' . $post_type . '][taxonomy]' ); ?>" value="" />
												<?php endif; ?>
												<ul class="checks">
													<?php if ( ! empty( $cpt_taxonomies ) ) : ?>
														<li>
															<label class="has-checkbox">
																<input
																	type="checkbox"
																	id="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-show-taxonomy"
																	name="<?php echo esc_attr( $option . '[sitemap][cpt][' . $post_type . '][show_taxonomy]' ); ?>"
																	value="1"
																	<?php checked( $cpt_show_taxonomy ); ?> />
																<?php esc_html_e( 'Show taxonomy', 'tms-core-essentials' ); ?>
															</label>
														</li>
													<?php endif; ?>
													<li>
														<label class="has-checkbox">
															<input
																type="checkbox"
																id="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-show-posts"
																name="<?php echo esc_attr( $option . '[sitemap][cpt][' . $post_type . '][show_posts]' ); ?>"
																value="1"
																<?php checked( $cpt_show_posts ); ?> />
															<?php esc_html_e( 'Show entries', 'tms-core-essentials' ); ?>
														</label>
													</li>
												</ul>
												<p class="inline">
													<label for="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-max-depth"><?php esc_html_e( 'Section max depth (empty = global)', 'tms-core-essentials' ); ?></label>
													<input
														type="text"
														class="small-text"
														inputmode="numeric"
														name="<?php echo esc_attr( $option . '[sitemap][cpt][' . $post_type . '][max_depth]' ); ?>"
														id="tcres-sitemap-cpt-<?php echo esc_attr( $cpt_class ); ?>-max-depth"
														value="<?php echo esc_attr( $cpt_max_depth ); ?>" />
												</p>
											</div>
										</fieldset>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>

						<?php tcres_settings_content_render_sitemap_usage(); ?>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_content_render_scroll_to_top_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['scroll_to_top'] ) && is_array( $settings['scroll_to_top'] )
		? $settings['scroll_to_top']
		: array();
	$option   = TCRES_OPTION_NAME;

	$threshold = isset( $group['threshold_viewports'] )
		? (float) $group['threshold_viewports']
		: 1.0;
	if ( $threshold <= 0 ) :
		$threshold = 1.0;
	endif;

	$footer_selector = isset( $group['footer_selector'] ) ? (string) $group['footer_selector'] : '';
	$footer_gap      = isset( $group['footer_gap'] ) ? (int) $group['footer_gap'] : 16;
	$avoid_footer    = ! empty( $group['avoid_footer'] );
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Scroll to top', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-scroll-to-top-enable',
						$option . '[scroll_to_top][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds a floating button in the footer that scrolls the page back to the top.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-scroll-to-top-options',
						)
					);
					?>
					<div
						id="tcres-scroll-to-top-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>

						<div>
							<h3><?php esc_html_e( 'Visibility', 'tms-core-essentials' ); ?></h3>
							<p class="inline">
								<label for="tcres-scroll-to-top-threshold"><?php esc_html_e( 'Show after', 'tms-core-essentials' ); ?></label>
								<input
									type="number"
									min="0.1"
									max="10"
									step="0.1"
									name="<?php echo esc_attr( $option . '[scroll_to_top][threshold_viewports]' ); ?>"
									id="tcres-scroll-to-top-threshold"
									value="<?php echo esc_attr( (string) $threshold ); ?>" />
								<span class="description"><?php esc_html_e( 'Viewport heights scrolled before the button appears (1 = one screen).', 'tms-core-essentials' ); ?></span>
							</p>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Footer', 'tms-core-essentials' ); ?></h3>
							<div>
								<label class="has-checkbox">
										<input
											type="checkbox"
											id="tcres-scroll-to-top-avoid-footer"
											name="<?php echo esc_attr( $option . '[scroll_to_top][avoid_footer]' ); ?>"
											value="1"
											data-tcres-toggle-target="tcres-scroll-to-top-footer-options"
											aria-controls="tcres-scroll-to-top-footer-options"
											aria-expanded="<?php echo $avoid_footer ? 'true' : 'false'; ?>"
											<?php checked( $avoid_footer ); ?> />
										<?php esc_html_e( 'Move up when the footer is visible', 'tms-core-essentials' ); ?>
									</label>
								<div
									id="tcres-scroll-to-top-footer-options"
									class="tcres-settings-suboptions"
									<?php echo $avoid_footer ? '' : ' hidden'; ?>>
									<p class="inline">
										<label for="tcres-scroll-to-top-footer-selector"><?php esc_html_e( 'Footer selector', 'tms-core-essentials' ); ?></label>
										<input
											type="text"
											class="regular-text"
											name="<?php echo esc_attr( $option . '[scroll_to_top][footer_selector]' ); ?>"
											id="tcres-scroll-to-top-footer-selector"
											value="<?php echo esc_attr( $footer_selector ); ?>"
											placeholder="#site-footer" />
										<span class="description"><?php esc_html_e( 'CSS selector for the footer element (e.g. #site-footer, .site-footer, footer.wp-block-template-part). Required for this behavior.', 'tms-core-essentials' ); ?></span>
									</p>
									<p class="inline">
										<label for="tcres-scroll-to-top-footer-gap"><?php esc_html_e( 'Gap above footer', 'tms-core-essentials' ); ?></label>
										<input
											type="number"
											min="0"
											max="200"
											step="1"
											name="<?php echo esc_attr( $option . '[scroll_to_top][footer_gap]' ); ?>"
											id="tcres-scroll-to-top-footer-gap"
											value="<?php echo esc_attr( (string) $footer_gap ); ?>" />
										<span class="description"><?php esc_html_e( 'Pixels between the button and the footer when lifting.', 'tms-core-essentials' ); ?></span>
									</p>
								</div>
							</div>
							<p class="description"><?php esc_html_e( 'When disabled, the button stays fixed at the bottom of the viewport.', 'tms-core-essentials' ); ?></p>
						</div>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_content_render_tab_fields(): void {
	tcres_settings_render_multilingual_strings_notice();
	tcres_settings_content_render_breadcrumbs_panel();
	echo '<hr>';
	tcres_settings_content_render_sitemap_panel();
	echo '<hr>';
	tcres_settings_content_render_related_content_panel();
	echo '<hr>';
	tcres_settings_content_render_extend_search_panel();
	echo '<hr>';
	tcres_settings_content_render_scroll_to_top_panel();
}
