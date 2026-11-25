<?php
/**
 * Includes -> Modules -> Fields -> Hero
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


// Settings helpers ========================================

function tcres_hero_is_enabled(): bool {
	if ( ! (bool) tcres_option_get( 'hero', 'enable' ) ) return false;

	return ! empty( tcres_hero_get_post_types() )
		|| ! empty( tcres_hero_get_taxonomies() );
}


/**
 * @return array<int, string>
 */
function tcres_hero_get_post_types(): array {
	if ( ! (bool) tcres_option_get( 'hero', 'enable' ) ) return array();

	$post_types = tcres_option_get_for_post_types( 'hero', '' );
	if ( ! is_array( $post_types ) ) return array();

	return $post_types;
}


/**
 * @return array<int, string>
 */
function tcres_hero_get_taxonomies(): array {
	if ( ! (bool) tcres_option_get( 'hero', 'enable' ) ) return array();

	$taxonomies = tcres_option_get_for_taxonomies( 'hero', 'tax_' );
	if ( ! is_array( $taxonomies ) ) return array();

	return $taxonomies;
}


function tcres_hero_covers_post_type( string $post_type ): bool {
	return in_array( $post_type, tcres_hero_get_post_types(), true );
}


function tcres_hero_covers_taxonomy( string $taxonomy ): bool {
	return in_array( $taxonomy, tcres_hero_get_taxonomies(), true );
}


// Sanitize / editors ========================================

/**
 * @param string $value
 * @param string $meta_key
 */
function tcres_hero_sanitize_html_value( string $value, string $meta_key ): string {
	if ( $meta_key !== 'tcres_hero_description' ) return $value;

	$value = trim( $value );
	if ( $value === '' ) return '';

	// Match classic WP: plain newlines become paragraphs when no block markup is present
	if ( ! preg_match( '/<(p|div|ul|ol|li|h[1-6]|blockquote|table)[\s>\/]/i', $value ) ) :
		$value = wpautop( $value );
	endif;

	return $value;
}


/**
 * @param array<string, mixed> $settings
 * @return array<string, mixed>
 */
function tcres_hero_tiny_mce_before_init( array $settings, string $editor_id ): array {
	$compact_ids = array(
		'tcres_hero_pretitle',
		'tcres_hero_pretitle_term',
		'tcres_hero_subtitle',
		'tcres_hero_subtitle_term',
	);
	if ( in_array( $editor_id, $compact_ids, true ) ) :
		$settings['wpautop']           = false;
		$settings['forced_root_block'] = false;
		$settings['force_br_newlines'] = true;
		$settings['force_p_newlines']  = false;

		return $settings;
	endif;

	$description_ids = array( 'tcres_hero_description', 'tcres_hero_description_term' );
	if ( in_array( $editor_id, $description_ids, true ) ) :
		$settings['wpautop']               = true;
		$settings['forced_root_block']     = 'p';
		$settings['force_br_newlines']     = false;
		$settings['force_p_newlines']      = true;
		$settings['wp_autoresize_on']      = true;
		$settings['autoresize_min_height'] = 200;
		unset( $settings['height'] );

		return $settings;
	endif;

	return $settings;
}


/**
 * @param array<int, string> $buttons
 * @return array<int, string>
 */
function tcres_hero_teeny_buttons( array $buttons, string $editor_id ): array {
	$compact_ids = array(
		'tcres_hero_pretitle',
		'tcres_hero_pretitle_term',
		'tcres_hero_subtitle',
		'tcres_hero_subtitle_term',
	);
	if ( in_array( $editor_id, $compact_ids, true ) ) :
		return array( 'bold', 'italic', 'underline', 'strikethrough', 'link', 'unlink' );
	endif;

	$description_ids = array( 'tcres_hero_description', 'tcres_hero_description_term' );
	if ( in_array( $editor_id, $description_ids, true ) ) :
		return array( 'bold', 'italic', 'underline', 'strikethrough', 'bullist', 'numlist', 'link', 'unlink' );
	endif;

	return $buttons;
}


/**
 * @param mixed $raw
 * @return array<int, array{title: string, url: string, target: string}>
 */
function tcres_hero_sanitize_button_classes( string $value ): string {
	$parts = preg_split( '/\s+/', trim( $value ), -1, PREG_SPLIT_NO_EMPTY );
	if ( ! is_array( $parts ) ) return '';

	$classes = array();
	foreach ( $parts as $class ) :
		$class = sanitize_html_class( (string) $class );
		if ( $class === '' ) continue;
		$classes[] = $class;
	endforeach;

	return implode( ' ', $classes );
}


function tcres_hero_sanitize_buttons( $raw ): array {
	if ( ! is_array( $raw ) ) return array();

	$out = array();
	foreach ( $raw as $row ) :
		if ( ! is_array( $row ) ) continue;

		$title  = isset( $row['title'] )
			? sanitize_text_field( wp_unslash( (string) $row['title'] ) )
			: '';
		$url    = isset( $row['url'] )
			? esc_url_raw( wp_unslash( (string) $row['url'] ) )
			: '';
		$target = ! empty( $row['target'] )
			? '_blank'
			: '';
		$class  = isset( $row['class'] )
			? tcres_hero_sanitize_button_classes( sanitize_text_field( wp_unslash( (string) $row['class'] ) ) )
			: '';

		if ( $title === '' && $url === '' ) continue;

		$out[] = array(
			'title'  => $title,
			'url'    => $url,
			'target' => $target,
			'class'  => $class,
		);
	endforeach;

	return $out;
}


/**
 * @return array<int, array{title: string, url: string, target: string, class: string}>
 */
function tcres_hero_get_buttons( int $post_id = 0, int $term_id = 0 ): array {
	$raw = null;

	if ( $term_id > 0 ) :
		$raw = get_term_meta( $term_id, 'tcres_hero_buttons', true );
	elseif ( $post_id > 0 ) :
		$raw = get_post_meta( $post_id, 'tcres_hero_buttons', true );
	endif;

	if ( ! is_array( $raw ) ) return array();

	$out = array();
	foreach ( $raw as $row ) :
		if ( ! is_array( $row ) ) continue;
		$title  = isset( $row['title'] )
			? (string) $row['title']
			: '';
		$url    = isset( $row['url'] )
			? (string) $row['url']
			: '';
		$target = isset( $row['target'] )
			? (string) $row['target']
			: '';
		$class  = isset( $row['class'] )
			? tcres_hero_sanitize_button_classes( (string) $row['class'] )
			: '';
		if ( $title === '' && $url === '' ) continue;
		$out[] = array(
			'title'  => $title,
			'url'    => $url,
			'target' => $target === '_blank' ? '_blank' : '',
			'class'  => $class,
		);
	endforeach;

	return $out;
}


function tcres_hero_verify_nonce(): bool {
	return isset( $_POST['tcres_hero_nonce'] )
		&& wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST['tcres_hero_nonce'] ) ),
			'_tcres_hero_nonce'
		);
}


function tcres_hero_save_buttons_post( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! tcres_hero_verify_nonce() ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Nonce verified above, sanitized in tcres_hero_sanitize_buttons().
	$raw     = isset( $_POST['tcres_hero_buttons'] )
		? wp_unslash( $_POST['tcres_hero_buttons'] )
		: array();
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
	$buttons = tcres_hero_sanitize_buttons( $raw );

	if ( empty( $buttons ) ) :
		delete_post_meta( $post_id, 'tcres_hero_buttons' );
	else :
		update_post_meta( $post_id, 'tcres_hero_buttons', $buttons );
	endif;
}


function tcres_hero_save_buttons_term( int $term_id ): void {
	if ( ! tcres_hero_verify_nonce() ) return;
	if ( ! current_user_can( 'edit_term', $term_id ) ) return;

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- Nonce verified above, sanitized in tcres_hero_sanitize_buttons().
	$raw     = isset( $_POST['tcres_hero_buttons'] )
		? wp_unslash( $_POST['tcres_hero_buttons'] )
		: array();
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
	$buttons = tcres_hero_sanitize_buttons( $raw );

	if ( empty( $buttons ) ) :
		delete_term_meta( $term_id, 'tcres_hero_buttons' );
	else :
		update_term_meta( $term_id, 'tcres_hero_buttons', $buttons );
	endif;
}


// Render ========================================

/**
 * @param array<int, array{title: string, url: string, target: string, class: string}> $buttons
 */
function tcres_hero_render_buttons_ui( array $buttons ): void {
	?>
	<div class="tcres-hero-buttons" data-tcres-hero-buttons>
		<p class="tcres-metabox-field-label"><?php esc_html_e( 'Buttons', 'tms-core-essentials' ); ?></p>
		<div class="tcres-hero-buttons-list" data-tcres-hero-buttons-list>
			<?php
			if ( empty( $buttons ) ) :
				$buttons = array(
					array(
						'title'  => '',
						'url'    => '',
						'target' => '',
						'class'  => '',
					),
				);
			endif;

			foreach ( $buttons as $index => $button ) :
				tcres_hero_render_button_row( (int) $index, $button );
			endforeach;
			?>
		</div>
		<p>
			<button type="button" class="button" data-tcres-hero-add-button>
				<?php esc_html_e( 'Add button', 'tms-core-essentials' ); ?>
			</button>
		</p>
		<template data-tcres-hero-button-template>
			<?php
			tcres_hero_render_button_row(
				'__INDEX__',
				array(
					'title'  => '',
					'url'    => '',
					'target' => '',
					'class'  => '',
				)
			);
			?>
		</template>
	</div>
	<?php
}


/**
 * @param int|string                                           $index
 * @param array{title?: string, url?: string, target?: string, class?: string} $button
 */
function tcres_hero_render_button_row( $index, array $button ): void {
	$title  = isset( $button['title'] )
		? (string) $button['title']
		: '';
	$url    = isset( $button['url'] )
		? (string) $button['url']
		: '';
	$target = ! empty( $button['target'] );
	$class  = isset( $button['class'] )
		? (string) $button['class']
		: '';
	$base   = 'tcres_hero_buttons[' . $index . ']';
	$url_id = 'tcres_hero_button_url_' . $index;
	?>
	<div class="tcres-hero-button-row" data-tcres-hero-button-row>
		<p class="tcres-hero-button-row-head">
			<span class="tcres-hero-button-row-title"><?php esc_html_e( 'Button', 'tms-core-essentials' ); ?></span>
			<a href="#" class="tcres-link-delete" data-tcres-hero-remove-button>
				<?php esc_html_e( 'Remove button', 'tms-core-essentials' ); ?>
			</a>
		</p>
		<div class="tcres-hero-button-fields">
			<label class="tcres-hero-button-title">
				<span class="tcres-metabox-field-label"><?php esc_html_e( 'Link text', 'tms-core-essentials' ); ?></span>
				<input type="text"
					class="widefat"
					name="<?php echo esc_attr( $base . '[title]' ); ?>"
					value="<?php echo esc_attr( $title ); ?>"
					data-tcres-hero-button-title />
			</label>
			<label class="tcres-hero-button-url-wrap">
				<span class="tcres-metabox-field-label"><?php esc_html_e( 'URL', 'tms-core-essentials' ); ?></span>
				<input type="text"
					class="widefat tcres-hero-button-url"
					id="<?php echo esc_attr( $url_id ); ?>"
					name="<?php echo esc_attr( $base . '[url]' ); ?>"
					value="<?php echo esc_attr( $url ); ?>"
					data-tcres-hero-button-url />
			</label>
			<label class="tcres-hero-button-class">
				<span class="tcres-metabox-field-label"><?php esc_html_e( 'Custom classes', 'tms-core-essentials' ); ?></span>
				<input type="text"
					class="widefat"
					name="<?php echo esc_attr( $base . '[class]' ); ?>"
					value="<?php echo esc_attr( $class ); ?>"
					data-tcres-hero-button-class />
			</label>
			<button type="button" class="button tcres-hero-button-select" data-tcres-hero-select-link>
				<?php esc_html_e( 'Select', 'tms-core-essentials' ); ?>
			</button>
			<label class="tcres-hero-button-target">
				<input type="checkbox"
					name="<?php echo esc_attr( $base . '[target]' ); ?>"
					value="1"
					data-tcres-hero-button-target
					<?php checked( $target ); ?> />
				<span><?php esc_html_e( 'Open in new tab', 'tms-core-essentials' ); ?></span>
			</label>
		</div>
	</div>
	<?php
}


function tcres_hero_render_fields( string $pretitle, string $subtitle, string $description, array $buttons, string $context = 'metabox' ): void {
	$suffix = $context === 'term'
		? '_term'
		: '';
	?>
	<div class="tcres-hero-fields">
		<?php if ( empty( tcres_option_get( 'hero', 'disable_pretitle' ) ) ) : ?>
		<div class="tcres-metabox-field tcres-wysiwyg-compact">
			<label class="tcres-metabox-field-label" for="tcres_hero_pretitle<?php echo esc_attr( $suffix ); ?>">
				<?php esc_html_e( 'Pretitle', 'tms-core-essentials' ); ?>
			</label>
			<textarea
				class="tcres-hero-editor tcres-wysiwyg"
				id="tcres_hero_pretitle<?php echo esc_attr( $suffix ); ?>"
				name="tcres_hero_pretitle"
				rows="2"><?php echo esc_textarea( $pretitle ); ?></textarea>
		</div>
		<?php endif; ?>
		<?php if ( empty( tcres_option_get( 'hero', 'disable_subtitle' ) ) ) : ?>
		<div class="tcres-metabox-field tcres-wysiwyg-compact">
			<label class="tcres-metabox-field-label" for="tcres_hero_subtitle<?php echo esc_attr( $suffix ); ?>">
				<?php esc_html_e( 'Subtitle', 'tms-core-essentials' ); ?>
			</label>
			<textarea
				class="tcres-hero-editor tcres-wysiwyg"
				id="tcres_hero_subtitle<?php echo esc_attr( $suffix ); ?>"
				name="tcres_hero_subtitle"
				rows="2"><?php echo esc_textarea( $subtitle ); ?></textarea>
		</div>
		<?php endif; ?>
		<?php if ( empty( tcres_option_get( 'hero', 'disable_description' ) ) ) : ?>
		<div class="tcres-metabox-field">
			<label class="tcres-metabox-field-label" for="tcres_hero_description<?php echo esc_attr( $suffix ); ?>">
				<?php esc_html_e( 'Description', 'tms-core-essentials' ); ?>
			</label>
			<textarea
				class="tcres-hero-editor tcres-wysiwyg"
				id="tcres_hero_description<?php echo esc_attr( $suffix ); ?>"
				name="tcres_hero_description"
				rows="8"><?php echo esc_textarea( $description ); ?></textarea>
		</div>
		<?php endif; ?>
		<?php if ( empty( tcres_option_get( 'hero', 'disable_buttons' ) ) ) : ?>
			<?php tcres_hero_render_buttons_ui( $buttons ); ?>
		<?php endif; ?>
	</div>
	<?php
}


function tcres_hero_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( '_tcres_hero_nonce', 'tcres_hero_nonce' );

	$pretitle = tcres_field_get(
		array(
			'field'   => 'tcres_hero_pretitle',
			'post_id' => (int) $post->ID,
		)
	);
	$subtitle = tcres_field_get(
		array(
			'field'   => 'tcres_hero_subtitle',
			'post_id' => (int) $post->ID,
		)
	);
	$description = tcres_field_get(
		array(
			'field'   => 'tcres_hero_description',
			'post_id' => (int) $post->ID,
		)
	);
	$buttons = tcres_hero_get_buttons( (int) $post->ID, 0 );

	tcres_hero_render_fields( $pretitle, $subtitle, $description, $buttons, 'metabox' );
}


function tcres_hero_register_meta_box(): void {
	$post_types = tcres_hero_get_post_types();
	if ( empty( $post_types ) ) return;

	add_meta_box(
		'tcres_hero_metabox',
		__( 'Hero', 'tms-core-essentials' ),
		'tcres_hero_render_meta_box',
		$post_types,
		'normal',
		'high'
	);

	tcres_admin_register_metabox_classes(
		$post_types,
		'tcres_hero_metabox',
		array( 'tcres-metabox', 'tcres-hero-field' )
	);
}


function tcres_hero_save_post( int $post_id ): void {
	$keys = array();
	if ( empty( tcres_option_get( 'hero', 'disable_pretitle' ) ) ) $keys[] = 'tcres_hero_pretitle';
	if ( empty( tcres_option_get( 'hero', 'disable_subtitle' ) ) ) $keys[] = 'tcres_hero_subtitle';
	if ( empty( tcres_option_get( 'hero', 'disable_description' ) ) ) $keys[] = 'tcres_hero_description';

	if ( ! empty( $keys ) ) :
		tcres_post_meta_update( $post_id, $keys, 'tcres_', 'html' );
	endif;

	if ( empty( tcres_option_get( 'hero', 'disable_buttons' ) ) ) :
		tcres_hero_save_buttons_post( $post_id );
	endif;
}


function tcres_hero_render_term_add_field(): void {
	wp_nonce_field( '_tcres_hero_nonce', 'tcres_hero_nonce' );
	?>
	<div class="form-field tcres-metabox tcres-hero-field">
		<label class="tcres-metabox-field-label"><?php esc_html_e( 'Hero', 'tms-core-essentials' ); ?></label>
		<?php tcres_hero_render_fields( '', '', '', array(), 'term' ); ?>
	</div>
	<?php
}


/**
 * @param WP_Term $term
 */
function tcres_hero_render_term_edit_field( $term ): void {
	$pretitle    = '';
	$subtitle    = '';
	$description = '';
	$buttons     = array();

	if ( $term instanceof WP_Term ) :
		$raw_pretitle = get_term_meta( (int) $term->term_id, 'tcres_hero_pretitle', true );
		$pretitle     = is_string( $raw_pretitle )
			? $raw_pretitle
			: '';
		$raw_subtitle = get_term_meta( (int) $term->term_id, 'tcres_hero_subtitle', true );
		$subtitle     = is_string( $raw_subtitle )
			? $raw_subtitle
			: '';
		$raw_desc     = get_term_meta( (int) $term->term_id, 'tcres_hero_description', true );
		$description  = is_string( $raw_desc )
			? $raw_desc
			: '';
		$buttons      = tcres_hero_get_buttons( 0, (int) $term->term_id );
	endif;

	wp_nonce_field( '_tcres_hero_nonce', 'tcres_hero_nonce' );
	?>
	<tr class="form-field tcres-metabox tcres-hero-field">
		<th scope="row">
			<label class="tcres-metabox-field-label"><?php esc_html_e( 'Hero', 'tms-core-essentials' ); ?></label>
		</th>
		<td>
			<?php tcres_hero_render_fields( $pretitle, $subtitle, $description, $buttons, 'term' ); ?>
		</td>
	</tr>
	<?php
}


function tcres_hero_save_term( int $term_id ): void {
	$keys = array();
	if ( empty( tcres_option_get( 'hero', 'disable_pretitle' ) ) ) $keys[] = 'tcres_hero_pretitle';
	if ( empty( tcres_option_get( 'hero', 'disable_subtitle' ) ) ) $keys[] = 'tcres_hero_subtitle';
	if ( empty( tcres_option_get( 'hero', 'disable_description' ) ) ) $keys[] = 'tcres_hero_description';

	if ( ! empty( $keys ) ) :
		tcres_term_meta_update( $term_id, $keys, 'tcres_', 'html' );
	endif;

	if ( empty( tcres_option_get( 'hero', 'disable_buttons' ) ) ) :
		tcres_hero_save_buttons_term( $term_id );
	endif;
}


function tcres_hero_register_term_hooks(): void {
	foreach ( tcres_hero_get_taxonomies() as $taxonomy ) :
		add_action( "{$taxonomy}_add_form_fields", 'tcres_hero_render_term_add_field', 50 );
		add_action( "{$taxonomy}_edit_form_fields", 'tcres_hero_render_term_edit_field', 50 );
		add_action( "created_{$taxonomy}", 'tcres_hero_save_term' );
		add_action( "edited_{$taxonomy}", 'tcres_hero_save_term' );
	endforeach;
}


add_action( 'init', function (): void {
	$post_types = tcres_hero_get_post_types();
	$taxonomies = tcres_hero_get_taxonomies();
	if ( empty( $post_types ) && empty( $taxonomies ) ) return;

	if ( ! empty( $post_types ) ) :
		add_action( 'add_meta_boxes', 'tcres_hero_register_meta_box', 11 );
		add_action( 'save_post', 'tcres_hero_save_post' );
	endif;

	if ( ! empty( $taxonomies ) ) :
		tcres_hero_register_term_hooks();
	endif;

	add_filter( 'teeny_mce_buttons', 'tcres_hero_teeny_buttons', 10, 2 );
	add_filter( 'tiny_mce_before_init', 'tcres_hero_tiny_mce_before_init', 10, 2 );
	add_filter( 'tcres_post_meta_sanitize_html_value', 'tcres_hero_sanitize_html_value', 10, 2 );
}, 20 );


// Frontend ========================================

/**
 * Build one hero button HTML
 *
 * @param array{title?: string, url?: string, target?: string, class?: string} $button
 */
function tcres_hero_render_button_html( array $button ): string {
	$title  = isset( $button['title'] )
		? (string) $button['title']
		: '';
	$url    = isset( $button['url'] )
		? (string) $button['url']
		: '';
	$target = isset( $button['target'] )
		? (string) $button['target']
		: '';
	$class  = isset( $button['class'] )
		? tcres_hero_sanitize_button_classes( (string) $button['class'] )
		: '';

	if ( $title === '' || $url === '' ) return '';

	$icon   = '';
	$attr_t = '';
	$label  = esc_html( $title );

	if ( stripos( $url, 'tel:' ) !== false ) :
		$icon = 'phone';
	elseif ( stripos( $url, 'mailto:' ) !== false ) :
		$icon = 'mail';
	elseif ( strpos( $url, '://wa.me/' ) !== false ) :
		$icon   = 'whatsapp';
		$attr_t = ' target="_blank" rel="noopener noreferrer"';
	elseif ( $target === '_blank' ) :
		$attr_t = ' target="_blank" rel="noopener noreferrer"';
	endif;

	if ( $icon !== '' ) :
		$svg = tcres_svg_icon_get(
			array(
				'icon'   => $icon,
				'inline' => true,
			)
		);
		if ( is_string( $svg ) && $svg !== '' ) :
			$label = $svg . ' <span>' . $label . '</span>';
		endif;
	endif;

	$classes = 'button';
	if ( $class !== '' ) :
		$classes .= ' ' . $class;
	endif;

	return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $url ) . '"' . $attr_t . '>' . $label . '</a>';
}


/**
 * Allowed wrapper tags for the Hero
 *
 * @return array<int, string>
 */
function tcres_hero_get_allowed_tags(): array {
	return array( 'header', 'section', 'div' );
}


/**
 * Allowed tags for the Hero pretitle element
 *
 * @return array<int, string>
 */
function tcres_hero_get_allowed_pretitle_tags(): array {
	return array( 'p', 'div', 'span' );
}


/**
 * Allowed tags for the Hero subtitle element
 *
 * @return array<int, string>
 */
function tcres_hero_get_allowed_subtitle_tags(): array {
	return array( 'p', 'div', 'span', 'h2', 'h3', 'h4', 'h5', 'h6' );
}


/**
 * Normalize featured image size for get_the_post_thumbnail()
 *
 * @param mixed $size Registered size name, or [width, height]
 * @return string|array{0: int, 1: int}
 */
function tcres_hero_normalize_image_size( $size ) {
	if ( is_array( $size ) ) :
		$width  = isset( $size[0] )
			? absint( $size[0] )
			: 0;
		$height = isset( $size[1] )
			? absint( $size[1] )
			: 0;
		if ( $width > 0 && $height > 0 ) :
			return array( $width, $height );
		endif;

		return 'full';
	endif;

	if ( ! is_string( $size ) ) return 'full';

	$size = sanitize_key( $size );
	if ( $size === '' ) return 'full';

	$core = array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' );
	if ( in_array( $size, $core, true ) || has_image_size( $size ) ) :
		return $size;
	endif;

	return 'full';
}


/**
 * Resolve post or term context for the Hero
 *
 * @param array{post_id?: int, term_id?: int} $args
 * @return array{post_id: int, term_id: int}
 */
function tcres_hero_resolve_context( array $args ): array {
	$post_id = isset( $args['post_id'] )
		? (int) $args['post_id']
		: 0;
	$term_id = isset( $args['term_id'] )
		? (int) $args['term_id']
		: 0;

	if ( $term_id > 0 || $post_id > 0 ) :
		return array(
			'post_id' => $post_id,
			'term_id' => $term_id,
		);
	endif;

	if ( is_singular() ) :
		return array(
			'post_id' => (int) get_the_ID(),
			'term_id' => 0,
		);
	endif;

	if ( is_category() || is_tag() || is_tax() ) :
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) :
			return array(
				'post_id' => 0,
				'term_id' => (int) $term->term_id,
			);
		endif;
	endif;

	return array(
		'post_id' => 0,
		'term_id' => 0,
	);
}


/**
 * Build hero background markup (absolute cover image or video)
 *
 * When a featured video is set, it is used as the background. If an image is
 * also available, it is used as the video poster
 *
 * @param int                          $attachment_id Image attachment ID (background when no video; poster when video is set)
 * @param string|array{0: int, 1: int} $image_size
 * @param int                          $video_id      Featured video attachment ID (takes priority as background)
 */
function tcres_hero_render_background_html( int $attachment_id, $image_size, int $video_id = 0 ): string {
	if ( $video_id > 0 && wp_attachment_is( 'video', $video_id ) ) :
		$url = wp_get_attachment_url( $video_id );
		if ( is_string( $url ) && $url !== '' ) :
			$mime = get_post_mime_type( $video_id );
			$mime = is_string( $mime )
				? $mime
				: '';

			$poster_attr = '';
			if ( $attachment_id > 0 && wp_attachment_is_image( $attachment_id ) ) :
				$poster = wp_get_attachment_image_url( $attachment_id, $image_size );
				if ( is_string( $poster ) && $poster !== '' ) :
					$poster_attr = ' poster="' . esc_url( $poster ) . '"';
				endif;
			endif;

			$video = '<video class="video" autoplay muted loop playsinline' . $poster_attr . '>'
				. '<source src="' . esc_url( $url ) . '"' . ( $mime !== '' ? ' type="' . esc_attr( $mime ) . '"' : '' ) . ' />'
				. '</video>';

			return '<div class="background" aria-hidden="true">'
				. $video
				. '<div class="overlay"></div>'
				. '</div>';
		endif;
	endif;

	if ( $attachment_id <= 0 ) return '';
	if ( ! wp_attachment_is_image( $attachment_id ) ) return '';

	$img = wp_get_attachment_image(
		$attachment_id,
		$image_size,
		false,
		array(
			'class'    => 'image',
			'alt'      => '',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	if ( ! is_string( $img ) || $img === '' ) return '';

	return '<div class="background" aria-hidden="true">'
		. $img
		. '<div class="overlay"></div>'
		. '</div>';
}


/**
 * Get Hero HTML for the frontend
 *
 * Always includes the post/term title. Posts use the featured image when set
 * Terms use Featured image for terms when that module provides one
 * When a featured video is set for the post/term, it is used as the background
 * If an image is also available, it is used as the video poster
 * Wrapper class always starts with `tcres-hero`
 *
 * @param array{
 *   class?: string,
 *   tag?: string,
 *   pretitle_tag?: string,
 *   subtitle_tag?: string,
 *   image_size?: string|array{0?: int, 1?: int},
 *   post_id?: int,
 *   term_id?: int
 * } $args
 */
function tcres_hero_get( array $args = array() ): string {
	$context = tcres_hero_resolve_context( $args );
	$post_id = $context['post_id'];
	$term_id = $context['term_id'];

	$title          = '';
	$background_html = '';
	$pretitle       = '';
	$subtitle       = '';
	$description    = '';
	$buttons        = array();
	$image_size     = tcres_hero_normalize_image_size( $args['image_size'] ?? 'full' );

	if ( $term_id > 0 ) :
		$term = get_term( $term_id );
		if ( ! ( $term instanceof WP_Term ) || is_wp_error( $term ) ) return '';

		$title         = $term->name;
		$raw_pretitle  = get_term_meta( $term_id, 'tcres_hero_pretitle', true );
		$pretitle      = is_string( $raw_pretitle )
			? $raw_pretitle
			: '';
		$raw_subtitle = get_term_meta( $term_id, 'tcres_hero_subtitle', true );
		$subtitle     = is_string( $raw_subtitle )
			? $raw_subtitle
			: '';
		$raw_desc     = get_term_meta( $term_id, 'tcres_hero_description', true );
		$description  = is_string( $raw_desc )
			? $raw_desc
			: '';
		$buttons      = tcres_hero_get_buttons( 0, $term_id );
		$background_html = tcres_hero_render_background_html(
			tcres_term_image_id_get( $term_id ),
			$image_size,
			tcres_featured_video_id_get( 0, $term_id )
		);
	elseif ( $post_id > 0 ) :
		$post = get_post( $post_id );
		if ( ! ( $post instanceof WP_Post ) ) return '';

		$title = get_the_title( $post );
		$background_html = tcres_hero_render_background_html(
			(int) get_post_thumbnail_id( $post_id ),
			$image_size,
			tcres_featured_video_id_get( $post_id, 0 )
		);

		$pretitle = tcres_field_get(
			array(
				'field'   => 'tcres_hero_pretitle',
				'post_id' => $post_id,
			)
		);
		$subtitle = tcres_field_get(
			array(
				'field'   => 'tcres_hero_subtitle',
				'post_id' => $post_id,
			)
		);
		$description = tcres_field_get(
			array(
				'field'   => 'tcres_hero_description',
				'post_id' => $post_id,
			)
		);
		$buttons = tcres_hero_get_buttons( $post_id, 0 );
	else :
		return '';
	endif;

	$title = is_string( $title )
		? trim( $title )
		: '';
	if ( $title === '' ) return '';

	$pretitle = tcres_clean_wysiwyg_title( $pretitle );
	$subtitle = tcres_clean_wysiwyg_title( $subtitle );

	// Like the_content: keep HTML, and turn bare newlines into <p> when needed.
	if ( $description !== '' ) :
		$description = wp_kses_post( $description );
		if ( ! preg_match( '/<(p|div|ul|ol|li|h[1-6]|blockquote|table)[\s>\/]/i', $description ) ) :
			$description = wpautop( $description );
		endif;
	else :
		$description = '';
	endif;

	$buttons_html = '';
	foreach ( $buttons as $button ) :
		$buttons_html .= tcres_hero_render_button_html( $button );
	endforeach;

	$tag = isset( $args['tag'] )
		? strtolower( sanitize_key( (string) $args['tag'] ) )
		: 'header';
	if ( ! in_array( $tag, tcres_hero_get_allowed_tags(), true ) ) :
		$tag = 'header';
	endif;

	$pretitle_tag = isset( $args['pretitle_tag'] )
		? strtolower( sanitize_key( (string) $args['pretitle_tag'] ) )
		: 'p';
	if ( ! in_array( $pretitle_tag, tcres_hero_get_allowed_pretitle_tags(), true ) ) :
		$pretitle_tag = 'p';
	endif;

	$subtitle_tag = isset( $args['subtitle_tag'] )
		? strtolower( sanitize_key( (string) $args['subtitle_tag'] ) )
		: 'p';
	if ( ! in_array( $subtitle_tag, tcres_hero_get_allowed_subtitle_tags(), true ) ) :
		$subtitle_tag = 'p';
	endif;

	$class = 'tcres-hero';
	if ( $background_html !== '' ) :
		$class .= ' has-background';
	endif;
	if ( isset( $args['class'] ) ) :
		$extra = trim( preg_replace( '/\s+/', ' ', (string) $args['class'] ) ?? '' );
		if ( $extra !== '' ) :
			$class .= ' ' . $extra;
		endif;
	endif;

	$html  = '<' . $tag . ' class="' . esc_attr( $class ) . '">';
	$html .= $background_html;
	$html .= '<div class="inner">';
	if ( $pretitle !== '' ) :
		$html .= '<' . $pretitle_tag . ' class="pretitle">' . $pretitle . '</' . $pretitle_tag . '>';
	endif;
	$html .= '<h1 class="title">' . esc_html( $title ) . '</h1>';
	if ( $subtitle !== '' ) :
		$html .= '<' . $subtitle_tag . ' class="subtitle">' . $subtitle . '</' . $subtitle_tag . '>';
	endif;
	if ( $description !== '' ) :
		$html .= '<div class="description">' . $description . '</div>';
	endif;
	if ( $buttons_html !== '' ) :
		$html .= '<div class="buttons">' . $buttons_html . '</div>';
	endif;
	$html .= '</div>';
	$html .= '</' . $tag . '>';

	return $html;
}


/**
 * Echo Hero HTML
 *
 * @param array{
 *   class?: string,
 *   tag?: string,
 *   pretitle_tag?: string,
 *   subtitle_tag?: string,
 *   image_size?: string|array{0?: int, 1?: int},
 *   post_id?: int,
 *   term_id?: int
 * } $args
 */
function tcres_hero( array $args = array() ): void {
	tcres_echo_html( tcres_hero_get( $args ) );
}
