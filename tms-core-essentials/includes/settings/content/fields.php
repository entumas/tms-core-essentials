<?php
/**
 * Includes -> Settings -> Content -> Fields
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param array<string, mixed> $group
 * @param string               $option
 * @param string               $group_key
 * @param array<int, string>   $post_types
 * @param array<int, string>   $taxonomies
 * @param string               $title
 * @param string               $description
 * @param bool                 $show_post_types
 * @param bool                 $show_taxonomies
 * @param string               $tax_prefix
 * @param string               $id_prefix
 * @param callable|null        $after_content
 */
function tcres_settings_fields_render_module_panel(
	array $group,
	string $option,
	string $group_key,
	array $post_types,
	array $taxonomies,
	string $title,
	string $description,
	bool $show_post_types,
	bool $show_taxonomies,
	string $tax_prefix,
	string $id_prefix,
	?callable $after_content = null
): void {
	tcres_settings_card_render_field(
		$id_prefix . 'enable',
		$option . '[' . $group_key . '][enable]',
		! empty( $group['enable'] ),
		$title,
		$description,
		static function () use (
			$group,
			$option,
			$group_key,
			$post_types,
			$taxonomies,
			$title,
			$show_post_types,
			$show_taxonomies,
			$tax_prefix,
			$id_prefix,
			$after_content
		): void {
			if ( $show_post_types ) :
				$post_types_id = $id_prefix . 'post-types';
				?>
				<h4 id="<?php echo esc_attr( $post_types_id ); ?>" class="subtitle"><?php esc_html_e( 'Post types', 'tms-core-essentials' ); ?></h4>
				<fieldset aria-labelledby="<?php echo esc_attr( $post_types_id ); ?>">
					<?php
					tcres_settings_content_render_module_enable_list(
						$group,
						$post_types,
						$option,
						$group_key,
						'',
						$id_prefix,
						sprintf(
							/* translators: %s: Module title */
							__( '%s post types', 'tms-core-essentials' ),
							$title
						),
						__( 'No eligible post types were found.', 'tms-core-essentials' ),
						'post_type'
					);
					?>
				</fieldset>
				<?php
			endif;

			if ( $show_taxonomies ) :
				$taxonomies_id = $id_prefix . 'taxonomies';
				?>
				<h4 id="<?php echo esc_attr( $taxonomies_id ); ?>" class="subtitle"><?php esc_html_e( 'Taxonomies', 'tms-core-essentials' ); ?></h4>
				<fieldset aria-labelledby="<?php echo esc_attr( $taxonomies_id ); ?>">
					<?php
					tcres_settings_content_render_module_enable_list(
						$group,
						$taxonomies,
						$option,
						$group_key,
						$tax_prefix,
						$id_prefix . ( $show_post_types ? 'tax-' : '' ),
						sprintf(
							/* translators: %s: Module title */
							__( '%s taxonomies', 'tms-core-essentials' ),
							$title
						),
						__( 'No eligible taxonomies were found.', 'tms-core-essentials' ),
						'taxonomy'
					);
					?>
				</fieldset>
				<?php
			endif;

			if ( is_callable( $after_content ) ) :
				$after_content();
			endif;
		}
	);
}


function tcres_settings_fields_render_modules_cards(): void {
	$settings = tcres_settings_get();
	$keys     = array( 'bodyclass', 'subtitle', 'hero', 'featured_video', 'term_image' );
	$total    = count( $keys );
	$active   = 0;

	foreach ( $keys as $key ) :
		$group = isset( $settings[ $key ] ) && is_array( $settings[ $key ] )
			? $settings[ $key ]
			: array();
		if ( ! empty( $group['enable'] ) ) :
			$active++;
		endif;
	endforeach;

	$inactive_count = $total - $active;
	$card_filter    = tcres_settings_get_current_card_filter( 'fields' );
	?>

		<h2><?php esc_html_e( 'Fields', 'tms-core-essentials' ); ?></h2>
		<?php
		tcres_settings_cards_panel_render_start( 'fields' );
		tcres_settings_cards_render_filter(
			'fields',
			'fields',
			$card_filter,
			$total,
			$active,
			$inactive_count
		);
		tcres_settings_cards_render_grid_start( __( 'Field modules', 'tms-core-essentials' ) );
		tcres_settings_fields_render_bodyclass_panel();
		tcres_settings_fields_render_subtitle_panel();
		tcres_settings_fields_render_hero_panel();
		tcres_settings_fields_render_featured_video_panel();
		tcres_settings_fields_render_termimage_panel();
		tcres_settings_cards_render_grid_end();
		tcres_settings_cards_panel_render_end();
		?>
	<?php
}


function tcres_settings_fields_render_subtitle_usage(): void {
	$example = "echo tcres_subtitle_get( array(\n\t'tag'   => 'p',\n\t'class' => 'entry-subtitle',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Get the subtitle HTML on the frontend (auto-detects singular posts and taxonomy archives):', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>tag</code>
					&mdash; <?php esc_html_e( 'Optional wrapper tag: p, div, span, h2–h6. Omit for content only (no wrapper, no class).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Custom CSS classes for the wrapper (only used when tag is set).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>post_id</code>
					&mdash; <?php esc_html_e( 'Optional post ID (defaults to the current post).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>term_id</code>
					&mdash; <?php esc_html_e( 'Optional term ID (defaults to the current taxonomy archive term).', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Meta key:', 'tms-core-essentials' ); ?>
				<code>tcres_subtitle</code>
			</p>
	</details>
	<?php
}


function tcres_settings_fields_render_hero_usage(): void {
	$example = "echo tcres_hero_get( array(\n\t'tag'          => 'header',\n\t'subtitle_tag' => 'p',\n\t'class'        => 'hero',\n\t'image_size'   => 'large',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print the hero on the frontend (auto-detects singular posts and taxonomy archives). Includes the title. Background uses Featured video when set (with featured/term image as poster when available), otherwise the featured image (posts) or term featured image:', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>tag</code>
					&mdash; <?php esc_html_e( 'Wrapper tag: header (default), section or div.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>subtitle_tag</code>
					&mdash; <?php esc_html_e( 'Subtitle tag: p (default), div, span, or h2–h6.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Extra CSS class(es). The wrapper always includes tcres-hero.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>image_size</code>
					&mdash; <?php esc_html_e( 'Featured image size name (default: full), or [width, height].', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>post_id</code>
					&mdash; <?php esc_html_e( 'Optional post ID (defaults to the current singular post).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>term_id</code>
					&mdash; <?php esc_html_e( 'Optional term ID (defaults to the current taxonomy archive term).', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Meta keys:', 'tms-core-essentials' ); ?>
				<code>tcres_hero_subtitle</code>
				<code>tcres_hero_description</code>
				<code>tcres_hero_buttons</code>
			</p>
			<p class="description">
				<?php esc_html_e( 'On taxonomy archives, the image comes from Featured image for terms when that module is enabled for the taxonomy.', 'tms-core-essentials' ); ?>
			</p>
			<p class="description">
				<?php esc_html_e( 'When Hero is enabled for the same post type or taxonomy as Subtitle, the standalone Subtitle field is hidden.', 'tms-core-essentials' ); ?>
			</p>
	</details>
	<?php
}


function tcres_settings_fields_render_termimage_usage(): void {
	$example = "echo tcres_term_image_get( array(\n\t'image_size' => 'large',\n\t'class'      => 'term-image',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print the featured image for the current taxonomy archive term (or a specific term ID):', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>image_size</code>
					&mdash; <?php esc_html_e( 'Image size name (default: full), or [width, height].', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Optional CSS class for the img element.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>term_id</code>
					&mdash; <?php esc_html_e( 'Optional term ID (defaults to the current taxonomy archive term).', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Meta key:', 'tms-core-essentials' ); ?>
				<code>tcres_term_image</code>
			</p>
	</details>
	<?php
}


function tcres_settings_fields_render_bodyclass_panel(): void {
	$settings   = tcres_settings_get();
	$group      = isset( $settings['bodyclass'] ) && is_array( $settings['bodyclass'] )
		? $settings['bodyclass']
		: array();
	$option     = TCRES_OPTION_NAME;
	$post_types = tcres_settings_content_get_module_post_types();
	$taxonomies = tcres_settings_content_get_module_taxonomies();

	tcres_settings_fields_render_module_panel(
		$group,
		$option,
		'bodyclass',
		$post_types,
		$taxonomies,
		__( 'Body classes', 'tms-core-essentials' ),
		__( 'Adds a field to enter extra CSS classes. Classes are applied to the frontend body tag on singular posts and taxonomy archives.', 'tms-core-essentials' ),
		true,
		true,
		'tax_',
		'tcres-bodyclass-'
	);
}


function tcres_settings_fields_render_termimage_panel(): void {
	$settings   = tcres_settings_get();
	$group      = isset( $settings['term_image'] ) && is_array( $settings['term_image'] )
		? $settings['term_image']
		: array();
	$option     = TCRES_OPTION_NAME;
	$taxonomies = tcres_settings_content_get_module_taxonomies();

	tcres_settings_fields_render_module_panel(
		$group,
		$option,
		'term_image',
		array(),
		$taxonomies,
		__( 'Featured image for terms', 'tms-core-essentials' ),
		__( 'Adds a featured image field on term screens. Useful for taxonomy archives and modules such as Hero.', 'tms-core-essentials' ),
		false,
		true,
		'',
		'tcres-term-image-',
		'tcres_settings_fields_render_termimage_usage'
	);
}


function tcres_settings_fields_render_featured_video_usage(): void {
	$example = "echo tcres_featured_video_get( array(\n\t'class' => 'entry-video',\n\t'attrs' => array(\n\t\t'controls'    => true,\n\t\t'autoplay'    => false,\n\t\t'muted'       => false,\n\t\t'loop'        => false,\n\t\t'playsinline' => true,\n\t),\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print the featured video for the current post/term (or a specific ID):', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>class</code>
					&mdash; <?php esc_html_e( 'Optional CSS class for the video element.', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>attrs</code>
					&mdash; <?php esc_html_e( 'Optional video attributes:', 'tms-core-essentials' ); ?>
					<ul>
						<li>
							<code>controls</code>
							&mdash; <?php esc_html_e( 'Show native controls. Default: true when autoplay is off, false when autoplay is on.', 'tms-core-essentials' ); ?>
						</li>
						<li>
							<code>autoplay</code>
							&mdash; <?php esc_html_e( 'Start playback automatically. Default: false.', 'tms-core-essentials' ); ?>
						</li>
						<li>
							<code>muted</code>
							&mdash; <?php esc_html_e( 'Mute audio. Default: true when autoplay is on (required by browsers), otherwise false.', 'tms-core-essentials' ); ?>
						</li>
						<li>
							<code>loop</code>
							&mdash; <?php esc_html_e( 'Loop playback. Default: false.', 'tms-core-essentials' ); ?>
						</li>
						<li>
							<code>playsinline</code>
							&mdash; <?php esc_html_e( 'Play inline on mobile. Default: true.', 'tms-core-essentials' ); ?>
						</li>
					</ul>
				</li>
				<li>
					<code>post_id</code>
					&mdash; <?php esc_html_e( 'Optional post ID (defaults to the current singular post).', 'tms-core-essentials' ); ?>
				</li>
				<li>
					<code>term_id</code>
					&mdash; <?php esc_html_e( 'Optional term ID (defaults to the current taxonomy archive term).', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Meta key:', 'tms-core-essentials' ); ?>
				<code>tcres_featured_video</code>
			</p>
			<p class="description">
				<?php esc_html_e( 'When a featured video is set, Hero uses it as the background. If a featured/term image is also set, it is used as the video poster.', 'tms-core-essentials' ); ?>
			</p>
	</details>
	<?php
}


function tcres_settings_fields_render_featured_video_panel(): void {
	$settings   = tcres_settings_get();
	$group      = isset( $settings['featured_video'] ) && is_array( $settings['featured_video'] )
		? $settings['featured_video']
		: array();
	$option     = TCRES_OPTION_NAME;
	$post_types = tcres_settings_content_get_module_post_types();
	$taxonomies = tcres_settings_content_get_module_taxonomies();

	tcres_settings_fields_render_module_panel(
		$group,
		$option,
		'featured_video',
		$post_types,
		$taxonomies,
		__( 'Featured video', 'tms-core-essentials' ),
		__( 'Adds a featured video field on post and term screens. When set, Hero uses this video as the background (featured/term image becomes the poster when available).', 'tms-core-essentials' ),
		true,
		true,
		'tax_',
		'tcres-featured-video-',
		'tcres_settings_fields_render_featured_video_usage'
	);
}


function tcres_settings_fields_render_subtitle_panel(): void {
	$settings   = tcres_settings_get();
	$group      = isset( $settings['subtitle'] ) && is_array( $settings['subtitle'] )
		? $settings['subtitle']
		: array();
	$option     = TCRES_OPTION_NAME;
	$post_types = tcres_settings_content_get_module_post_types();
	$taxonomies = tcres_settings_content_get_module_taxonomies();

	tcres_settings_fields_render_module_panel(
		$group,
		$option,
		'subtitle',
		$post_types,
		$taxonomies,
		__( 'Subtitle', 'tms-core-essentials' ),
		__( 'Adds a subtitle WYSIWYG in the main editor area for posts, and on term screens (no media button; primary toolbar only).', 'tms-core-essentials' ),
		true,
		true,
		'tax_',
		'tcres-subtitle-',
		'tcres_settings_fields_render_subtitle_usage'
	);
}


function tcres_settings_fields_render_hero_panel(): void {
	$settings   = tcres_settings_get();
	$group      = isset( $settings['hero'] ) && is_array( $settings['hero'] )
		? $settings['hero']
		: array();
	$option     = TCRES_OPTION_NAME;
	$post_types = tcres_settings_content_get_module_post_types();
	$taxonomies = tcres_settings_content_get_module_taxonomies();

	tcres_settings_fields_render_module_panel(
		$group,
		$option,
		'hero',
		$post_types,
		$taxonomies,
		__( 'Hero', 'tms-core-essentials' ),
		__( 'Adds a Hero with title, featured image or featured video (posts), subtitle, description (WYSIWYG) and repeatable link buttons for posts and terms.', 'tms-core-essentials' ),
		true,
		true,
		'tax_',
		'tcres-hero-',
		'tcres_settings_fields_render_hero_usage'
	);
}


function tcres_settings_fields_render_tab_fields(): void {
	tcres_settings_fields_render_modules_cards();
}
