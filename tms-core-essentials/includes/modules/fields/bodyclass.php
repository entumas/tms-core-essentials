<?php
/**
 * Includes -> Modules -> Fields -> Body classes
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_body_classes_is_enabled(): bool {
	if ( ! (bool) tcres_option_get( 'bodyclass', 'enable' ) ) return false;

	return ! empty( tcres_body_classes_get_post_types() )
		|| ! empty( tcres_body_classes_get_taxonomies() );
}


/**
 * @return array<int, string>
 */
function tcres_body_classes_get_post_types(): array {
	if ( ! (bool) tcres_option_get( 'bodyclass', 'enable' ) ) return array();

	$post_types = tcres_option_get_for_post_types( 'bodyclass', '' );
	if ( ! is_array( $post_types ) ) return array();

	return $post_types;
}


/**
 * @return array<int, string>
 */
function tcres_body_classes_get_taxonomies(): array {
	if ( ! (bool) tcres_option_get( 'bodyclass', 'enable' ) ) return array();

	$taxonomies = tcres_option_get_for_taxonomies( 'bodyclass', 'tax_' );
	if ( ! is_array( $taxonomies ) ) return array();

	return $taxonomies;
}


/**
 * @param array<int, string> $classes
 * @return array<int, string>
 */
function tcres_body_classes_append( array $classes, string $value ): array {
	if ( $value === '' ) return $classes;

	$parts = preg_split( '/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! is_array( $parts ) ) return $classes;

	foreach ( $parts as $class ) :
		$class = sanitize_html_class( (string) $class );
		if ( $class === '' ) continue;
		$classes[] = $class;
	endforeach;

	return $classes;
}


function tcres_body_classes_register_meta_box(): void {
	$post_types = tcres_body_classes_get_post_types();
	if ( empty( $post_types ) ) return;

	add_meta_box(
		'tcres_bodyclass',
		__( 'Body classes', 'tms-core-essentials' ),
		'tcres_body_classes_render_meta_box',
		$post_types,
		'side',
		'low'
	);

	tcres_admin_register_metabox_classes(
		$post_types,
		'tcres_bodyclass',
		array( 'tcres-metabox', 'tcres-bodyclass-field' )
	);
}


function tcres_body_classes_render_field( string $value, string $context = 'metabox' ): void {
	$input_id = 'tcres_bodyclass';
	$help_id  = 'tcres_bodyclass-help';
	?>
	<div class="tcres-metabox-field">
		<?php if ( $context === 'metabox' ) : ?>
			<label class="tcres-metabox-field-label" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Custom classes', 'tms-core-essentials' ); ?></label>
		<?php endif; ?>
		<input type="text"
			class="tcres-metabox-field-input"
			name="tcres_bodyclass"
			id="<?php echo esc_attr( $input_id ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php esc_attr_e( 'Add classes to <body>', 'tms-core-essentials' ); ?>"
			aria-describedby="<?php echo esc_attr( $help_id ); ?>" />
		<p id="<?php echo esc_attr( $help_id ); ?>" class="tcres-metabox-field-help"><?php esc_html_e( 'Separate with spaces.', 'tms-core-essentials' ); ?></p>
	</div>
	<?php
}


function tcres_body_classes_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( '_tcres_bodyclass_nonce', 'tcres_bodyclass_nonce' );

	$value = tcres_field_get(
		array(
			'field'   => 'tcres_bodyclass',
			'post_id' => (int) $post->ID,
		)
	);

	tcres_body_classes_render_field( $value, 'metabox' );
}


function tcres_body_classes_save_post( int $post_id ): void {
	tcres_post_meta_update( $post_id, array( 'tcres_bodyclass' ), 'tcres_' );
}


function tcres_body_classes_render_term_add_field(): void {
	wp_nonce_field( '_tcres_bodyclass_nonce', 'tcres_bodyclass_nonce' );
	?>
	<div class="form-field tcres-metabox tcres-bodyclass-field">
		<label class="tcres-metabox-field-label" for="tcres_bodyclass"><?php esc_html_e( 'Body classes', 'tms-core-essentials' ); ?></label>
		<?php tcres_body_classes_render_field( '', 'term' ); ?>
	</div>
	<?php
}


/**
 * @param WP_Term $term
 */
function tcres_body_classes_render_term_edit_field( $term ): void {
	$value = '';
	if ( $term instanceof WP_Term ) :
		$raw   = get_term_meta( (int) $term->term_id, 'tcres_bodyclass', true );
		$value = is_string( $raw )
			? $raw
			: '';
	endif;

	wp_nonce_field( '_tcres_bodyclass_nonce', 'tcres_bodyclass_nonce' );
	?>
	<tr class="form-field tcres-metabox tcres-bodyclass-field">
		<th scope="row">
			<label class="tcres-metabox-field-label" for="tcres_bodyclass"><?php esc_html_e( 'Body classes', 'tms-core-essentials' ); ?></label>
		</th>
		<td>
			<?php tcres_body_classes_render_field( $value, 'term' ); ?>
		</td>
	</tr>
	<?php
}


function tcres_body_classes_save_term( int $term_id ): void {
	tcres_term_meta_update( $term_id, array( 'tcres_bodyclass' ), 'tcres_' );
}


/**
 * @param array<int, string> $classes
 * @return array<int, string>
 */
function tcres_body_classes_filter( array $classes ): array {
	if ( is_singular() ) :
		$value = tcres_field_get( array( 'field' => 'tcres_bodyclass' ) );
		return tcres_body_classes_append( $classes, $value );
	endif;

	if ( ! is_category() && ! is_tag() && ! is_tax() ) return $classes;

	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) return $classes;

	$taxonomies = tcres_body_classes_get_taxonomies();
	if ( ! in_array( $term->taxonomy, $taxonomies, true ) ) return $classes;

	$raw   = get_term_meta( (int) $term->term_id, 'tcres_bodyclass', true );
	$value = is_string( $raw )
		? $raw
		: '';

	return tcres_body_classes_append( $classes, $value );
}


function tcres_body_classes_register_term_hooks(): void {
	foreach ( tcres_body_classes_get_taxonomies() as $taxonomy ) :
		add_action( "{$taxonomy}_add_form_fields", 'tcres_body_classes_render_term_add_field', 30 );
		add_action( "{$taxonomy}_edit_form_fields", 'tcres_body_classes_render_term_edit_field', 30 );
		add_action( "created_{$taxonomy}", 'tcres_body_classes_save_term' );
		add_action( "edited_{$taxonomy}", 'tcres_body_classes_save_term' );
	endforeach;
}


add_action( 'init', function (): void {
	$post_types = tcres_body_classes_get_post_types();
	$taxonomies = tcres_body_classes_get_taxonomies();
	if ( empty( $post_types ) && empty( $taxonomies ) ) return;

	if ( ! empty( $post_types ) ) :
		add_action( 'add_meta_boxes', 'tcres_body_classes_register_meta_box', 21 );
		add_action( 'save_post', 'tcres_body_classes_save_post' );
	endif;

	if ( ! empty( $taxonomies ) ) :
		tcres_body_classes_register_term_hooks();
	endif;

	add_filter( 'body_class', 'tcres_body_classes_filter' );
}, 20 );
