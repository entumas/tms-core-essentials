<?php
/**
 * Includes -> Modules -> Fields -> Subtitle
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_subtitle_is_enabled(): bool {
	if ( ! (bool) tcres_option_get( 'subtitle', 'enable' ) ) :
		return false;
	endif;

	return ! empty( tcres_subtitle_get_post_types() )
		|| ! empty( tcres_subtitle_get_taxonomies() );
}


/**
 * @return array<int, string>
 */
function tcres_subtitle_get_post_types(): array {
	if ( ! (bool) tcres_option_get( 'subtitle', 'enable' ) ) :
		return array();
	endif;

	$post_types = tcres_option_get_for_post_types( 'subtitle', '' );
	if ( ! is_array( $post_types ) ) return array();

	return array_values(
		array_filter(
			$post_types,
			static function ( string $post_type ): bool {
				return ! tcres_hero_covers_post_type( $post_type );
			}
		)
	);
}


/**
 * @return array<int, string>
 */
function tcres_subtitle_get_taxonomies(): array {
	if ( ! (bool) tcres_option_get( 'subtitle', 'enable' ) ) :
		return array();
	endif;

	$taxonomies = tcres_option_get_for_taxonomies( 'subtitle', 'tax_' );
	if ( ! is_array( $taxonomies ) ) return array();

	return array_values(
		array_filter(
			$taxonomies,
			static function ( string $taxonomy ): bool {
				return ! tcres_hero_covers_taxonomy( $taxonomy );
			}
		)
	);
}


function tcres_subtitle_register_meta_box(): void {
	$post_types = tcres_subtitle_get_post_types();
	if ( empty( $post_types ) ) return;

	add_meta_box(
		'tcres_subtitle_metabox',
		__( 'Subtitle', 'tms-core-essentials' ),
		'tcres_subtitle_render_meta_box',
		$post_types,
		'normal',
		'high'
	);

	tcres_admin_register_metabox_classes(
		$post_types,
		'tcres_subtitle_metabox',
		array( 'tcres-metabox', 'tcres-subtitle-field', 'tcres-wysiwyg-compact' )
	);
}


/**
 * TinyMCE init: Enter inserts <br>, not <p>
 *
 * @param array<string, mixed> $settings
 * @return array<string, mixed>
 */
function tcres_subtitle_tiny_mce_before_init( array $settings, string $editor_id ): array {
	if ( $editor_id !== 'tcres_subtitle' && $editor_id !== 'tcres_subtitle_term' ) return $settings;

	$settings['wpautop']           = false;
	$settings['forced_root_block'] = false;
	$settings['force_br_newlines'] = true;
	$settings['force_p_newlines']  = false;

	return $settings;
}


/**
 * Filter teeny toolbar for subtitle editors
 *
 * @param array<int, string> $buttons
 * @param string             $editor_id
 * @return array<int, string>
 */
function tcres_subtitle_teeny_buttons( array $buttons, string $editor_id ): array {
	if ( $editor_id !== 'tcres_subtitle' && $editor_id !== 'tcres_subtitle_term' ) return $buttons;

	return array( 'bold', 'italic', 'underline', 'strikethrough', 'link', 'unlink' );
}


function tcres_subtitle_render_field( string $value, string $context = 'metabox' ): void {
	$editor_id = $context === 'term'
		? 'tcres_subtitle_term'
		: 'tcres_subtitle';

	echo '<div class="tcres-metabox-field">';
	printf(
		'<textarea class="tcres-subtitle-editor tcres-wysiwyg" id="%1$s" name="tcres_subtitle" rows="2">%2$s</textarea>',
		esc_attr( $editor_id ),
		esc_textarea( $value )
	);
	echo '</div>';
}


function tcres_subtitle_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( '_tcres_subtitle_nonce', 'tcres_subtitle_nonce' );

	$value = tcres_field_get(
		array(
			'field'   => 'tcres_subtitle',
			'post_id' => (int) $post->ID,
		)
	);

	tcres_subtitle_render_field( $value, 'metabox' );
}


function tcres_subtitle_save_post( int $post_id ): void {
	tcres_post_meta_update( $post_id, array( 'tcres_subtitle' ), 'tcres_', 'html' );
}


function tcres_subtitle_render_term_add_field(): void {
	wp_nonce_field( '_tcres_subtitle_nonce', 'tcres_subtitle_nonce' );
	?>
	<div class="form-field tcres-metabox tcres-subtitle-field tcres-wysiwyg-compact">
		<label class="tcres-metabox-field-label" for="tcres_subtitle_term"><?php esc_html_e( 'Subtitle', 'tms-core-essentials' ); ?></label>
		<?php tcres_subtitle_render_field( '', 'term' ); ?>
	</div>
	<?php
}


/**
 * @param WP_Term $term
 */
function tcres_subtitle_render_term_edit_field( $term ): void {
	$value = '';
	if ( $term instanceof WP_Term ) :
		$raw   = get_term_meta( (int) $term->term_id, 'tcres_subtitle', true );
		$value = is_string( $raw )
			? $raw
			: '';
	endif;

	wp_nonce_field( '_tcres_subtitle_nonce', 'tcres_subtitle_nonce' );
	?>
	<tr class="form-field tcres-metabox tcres-subtitle-field tcres-wysiwyg-compact">
		<th scope="row">
			<label class="tcres-metabox-field-label" for="tcres_subtitle_term"><?php esc_html_e( 'Subtitle', 'tms-core-essentials' ); ?></label>
		</th>
		<td>
			<?php tcres_subtitle_render_field( $value, 'term' ); ?>
		</td>
	</tr>
	<?php
}


function tcres_subtitle_save_term( int $term_id ): void {
	tcres_term_meta_update( $term_id, array( 'tcres_subtitle' ), 'tcres_', 'html' );
}


function tcres_subtitle_register_term_hooks(): void {
	foreach ( tcres_subtitle_get_taxonomies() as $taxonomy ) :
		add_action( "{$taxonomy}_add_form_fields", 'tcres_subtitle_render_term_add_field', 40 );
		add_action( "{$taxonomy}_edit_form_fields", 'tcres_subtitle_render_term_edit_field', 40 );
		add_action( "created_{$taxonomy}", 'tcres_subtitle_save_term' );
		add_action( "edited_{$taxonomy}", 'tcres_subtitle_save_term' );
	endforeach;
}


add_action( 'init', function (): void {
	$post_types = tcres_subtitle_get_post_types();
	$taxonomies = tcres_subtitle_get_taxonomies();
	if ( empty( $post_types ) && empty( $taxonomies ) ) return;

	if ( ! empty( $post_types ) ) :
		add_action( 'add_meta_boxes', 'tcres_subtitle_register_meta_box', 10 );
		add_action( 'save_post', 'tcres_subtitle_save_post' );
	endif;

	if ( ! empty( $taxonomies ) ) :
		tcres_subtitle_register_term_hooks();
	endif;

	add_filter( 'teeny_mce_buttons', 'tcres_subtitle_teeny_buttons', 10, 2 );
	add_filter( 'tiny_mce_before_init', 'tcres_subtitle_tiny_mce_before_init', 10, 2 );
}, 20 );


// Frontend ========================================

/**
 * Get subtitle HTML for the frontend
 *
 * Without `tag`, returns the sanitized content only (no wrapper, no class)
 *
 * @param array{
 *   tag?: string,
 *   class?: string,
 *   classes?: string,
 *   post_id?: int,
 *   term_id?: int
 * } $args
 */
function tcres_subtitle_get( array $args = array() ): string {
	$post_id = isset( $args['post_id'] )
		? (int) $args['post_id']
		: 0;
	$term_id = isset( $args['term_id'] )
		? (int) $args['term_id']
		: 0;

	$content = '';

	if ( $term_id > 0 ) :
		$raw     = get_term_meta( $term_id, 'tcres_subtitle', true );
		$content = is_string( $raw )
			? $raw
			: '';
	elseif ( $post_id > 0 ) :
		$content = tcres_field_get(
			array(
				'field'   => 'tcres_subtitle',
				'post_id' => $post_id,
			)
		);
	elseif ( is_singular() ) :
		$content = tcres_field_get( array( 'field' => 'tcres_subtitle' ) );
	elseif ( is_category() || is_tag() || is_tax() ) :
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) :
			$raw     = get_term_meta( (int) $term->term_id, 'tcres_subtitle', true );
			$content = is_string( $raw )
				? $raw
				: '';
		endif;
	endif;
	if ( $content === '' ) return '';

	$content = tcres_clean_wysiwyg_title( $content );
	if ( $content === '' ) return '';

	$tag = isset( $args['tag'] )
		? strtolower( sanitize_key( (string) $args['tag'] ) )
		: '';
	$allowed_tags = array( 'p', 'div', 'span', 'h2', 'h3', 'h4', 'h5', 'h6' );
	if ( $tag === '' || ! in_array( $tag, $allowed_tags, true ) ) :
		return $content;
	endif;

	$class = '';
	if ( isset( $args['class'] ) ) :
		$class = (string) $args['class'];
	elseif ( isset( $args['classes'] ) ) :
		$class = (string) $args['classes'];
	endif;
	$class = trim( preg_replace( '/\s+/', ' ', $class ) ?? '' );

	$attr = '';
	if ( $class !== '' ) :
		$attr = ' class="' . esc_attr( $class ) . '"';
	endif;

	return '<' . $tag . $attr . '>' . $content . '</' . $tag . '>';
}
