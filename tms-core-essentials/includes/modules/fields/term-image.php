<?php
/**
 * Includes -> Modules -> Fields -> Featured image for terms
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_term_image_is_enabled(): bool {
	if ( ! (bool) tcres_option_get( 'term_image', 'enable' ) ) return false;

	return ! empty( tcres_term_image_get_taxonomies() );
}


/**
 * @return array<int, string>
 */
function tcres_term_image_get_taxonomies(): array {
	if ( ! (bool) tcres_option_get( 'term_image', 'enable' ) ) return array();

	$taxonomies = tcres_option_get_for_taxonomies( 'term_image', '' );
	if ( ! is_array( $taxonomies ) ) return array();

	return $taxonomies;
}


function tcres_term_image_covers_taxonomy( string $taxonomy ): bool {
	return in_array( $taxonomy, tcres_term_image_get_taxonomies(), true );
}


/**
 * @return int Attachment ID, or 0
 */
function tcres_term_image_id_get( int $term_id = 0 ): int {
	if ( $term_id <= 0 ) :
		if ( ! ( is_category() || is_tag() || is_tax() ) ) return 0;
		$term = get_queried_object();
		if ( ! ( $term instanceof WP_Term ) ) return 0;
		$term_id = (int) $term->term_id;
	endif;

	$raw = get_term_meta( $term_id, 'tcres_term_image', true );
	$id  = is_numeric( $raw )
		? (int) $raw
		: 0;
	if ( $id <= 0 ) return 0;
	if ( get_post_type( $id ) !== 'attachment' ) return 0;
	if ( ! wp_attachment_is_image( $id ) ) return 0;

	return $id;
}


/**
 * Normalize image size for wp_get_attachment_image()
 *
 * @param mixed $size
 * @return string|array{0: int, 1: int}
 */
function tcres_term_image_normalize_size( $size ) {
	return tcres_hero_normalize_image_size( $size );
}


/**
 * Get featured image HTML for a term
 *
 * @param array{
 *   class?: string,
 *   image_size?: string|array{0?: int, 1?: int},
 *   term_id?: int
 * } $args
 */
function tcres_term_image_get( array $args = array() ): string {
	$term_id = isset( $args['term_id'] )
		? (int) $args['term_id']
		: 0;
	$id      = tcres_term_image_id_get( $term_id );
	if ( $id <= 0 ) return '';

	$image_size = tcres_term_image_normalize_size( $args['image_size'] ?? 'full' );
	$attr       = array();
	if ( isset( $args['class'] ) ) :
		$class = trim( preg_replace( '/\s+/', ' ', (string) $args['class'] ) ?? '' );
		if ( $class !== '' ) :
			$attr['class'] = $class;
		endif;
	endif;

	$html = wp_get_attachment_image( $id, $image_size, false, $attr );
	return is_string( $html )
		? $html
		: '';
}


/**
 * Echo featured image HTML for a term
 *
 * @param array{class?: string, image_size?: string|array{0?: int, 1?: int}, term_id?: int} $args
 */
function tcres_term_image( array $args = array() ): void {
	tcres_echo_html( tcres_term_image_get( $args ) );
}


/**
 * @param int $attachment_id
 */
function tcres_term_image_render_field( int $attachment_id ): void {
	$preview = '';
	if ( $attachment_id > 0 && wp_attachment_is_image( $attachment_id ) ) :
		$preview = wp_get_attachment_image( $attachment_id, 'medium' );
	endif;
	$has_image = $preview !== '';
	?>
	<div class="tcres-metabox-field">
		<div class="tcres-term-image" data-tcres-term-image>
			<input type="hidden"
				name="tcres_term_image"
				id="tcres_term_image"
				value="<?php echo esc_attr( (string) ( $has_image ? $attachment_id : 0 ) ); ?>"
				data-tcres-term-image-input />
			<div class="tcres-term-image-preview" data-tcres-term-image-preview<?php echo $has_image ? '' : ' hidden'; ?>>
				<?php tcres_echo_html( $preview ); ?>
			</div>
			<p class="tcres-term-image-actions">
				<button type="button" class="button" data-tcres-term-image-select>
					<?php echo $has_image ? esc_html__( 'Replace image', 'tms-core-essentials' ) : esc_html__( 'Select image', 'tms-core-essentials' ); ?>
				</button>
			</p>
			<p class="tcres-term-image-remove"<?php echo $has_image ? '' : ' hidden'; ?>>
				<a href="#" class="tcres-link-delete" data-tcres-term-image-remove>
					<?php esc_html_e( 'Remove image', 'tms-core-essentials' ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
}


function tcres_term_image_render_term_add_field(): void {
	wp_nonce_field( '_tcres_term_image_nonce', 'tcres_term_image_nonce' );
	?>
	<div class="form-field tcres-metabox tcres-term-image-field">
		<label class="tcres-metabox-field-label" for="tcres_term_image"><?php esc_html_e( 'Featured image', 'tms-core-essentials' ); ?></label>
		<?php tcres_term_image_render_field( 0 ); ?>
	</div>
	<?php
}


/**
 * @param WP_Term $term
 */
function tcres_term_image_render_term_edit_field( $term ): void {
	$attachment_id = 0;
	if ( $term instanceof WP_Term ) :
		$attachment_id = tcres_term_image_id_get( (int) $term->term_id );
	endif;

	wp_nonce_field( '_tcres_term_image_nonce', 'tcres_term_image_nonce' );
	?>
	<tr class="form-field tcres-metabox tcres-term-image-field">
		<th scope="row">
			<label class="tcres-metabox-field-label" for="tcres_term_image"><?php esc_html_e( 'Featured image', 'tms-core-essentials' ); ?></label>
		</th>
		<td>
			<?php tcres_term_image_render_field( $attachment_id ); ?>
		</td>
	</tr>
	<?php
}


function tcres_term_image_save_term( int $term_id ): void {
	if (
		! isset( $_POST['tcres_term_image_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST['tcres_term_image_nonce'] ) ),
			'_tcres_term_image_nonce'
		)
		|| ! current_user_can( 'edit_term', $term_id )
	) :
		return;
	endif;

	$raw = isset( $_POST['tcres_term_image'] )
		? absint( wp_unslash( $_POST['tcres_term_image'] ) )
		: 0;
	if ( $raw > 0 && get_post_type( $raw ) === 'attachment' && wp_attachment_is_image( $raw ) ) :
		update_term_meta( $term_id, 'tcres_term_image', $raw );
		return;
	endif;

	delete_term_meta( $term_id, 'tcres_term_image' );
}


function tcres_term_image_register_term_hooks(): void {
	foreach ( tcres_term_image_get_taxonomies() as $taxonomy ) :
		add_action( "{$taxonomy}_add_form_fields", 'tcres_term_image_render_term_add_field', 10 );
		add_action( "{$taxonomy}_edit_form_fields", 'tcres_term_image_render_term_edit_field', 10 );
		add_action( "created_{$taxonomy}", 'tcres_term_image_save_term' );
		add_action( "edited_{$taxonomy}", 'tcres_term_image_save_term' );
	endforeach;
}


add_action( 'init', function (): void {
	if ( empty( tcres_term_image_get_taxonomies() ) ) return;

	tcres_term_image_register_term_hooks();
}, 20 );
