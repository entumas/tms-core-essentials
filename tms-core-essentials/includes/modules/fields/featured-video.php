<?php
/**
 * Includes -> Modules -> Fields -> Featured video
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_featured_video_is_enabled(): bool {
	if ( ! (bool) tcres_option_get( 'featured_video', 'enable' ) ) return false;

	return ! empty( tcres_featured_video_get_post_types() )
		|| ! empty( tcres_featured_video_get_taxonomies() );
}


/**
 * @return array<int, string>
 */
function tcres_featured_video_get_post_types(): array {
	if ( ! (bool) tcres_option_get( 'featured_video', 'enable' ) ) return array();

	$post_types = tcres_option_get_for_post_types( 'featured_video', '' );
	if ( ! is_array( $post_types ) ) return array();

	return $post_types;
}


/**
 * @return array<int, string>
 */
function tcres_featured_video_get_taxonomies(): array {
	if ( ! (bool) tcres_option_get( 'featured_video', 'enable' ) ) return array();

	$taxonomies = tcres_option_get_for_taxonomies( 'featured_video', 'tax_' );
	if ( ! is_array( $taxonomies ) ) return array();

	return $taxonomies;
}


/**
 * @return int Attachment ID, or 0
 */
function tcres_featured_video_id_get( int $post_id = 0, int $term_id = 0 ): int {
	$id = 0;

	if ( $term_id > 0 ) :
		$raw = get_term_meta( $term_id, 'tcres_featured_video', true );
		$id  = is_numeric( $raw )
			? (int) $raw
			: 0;
	elseif ( $post_id > 0 ) :
		$raw = get_post_meta( $post_id, 'tcres_featured_video', true );
		$id  = is_numeric( $raw )
			? (int) $raw
			: 0;
	elseif ( is_singular() ) :
		$raw = get_post_meta( (int) get_the_ID(), 'tcres_featured_video', true );
		$id  = is_numeric( $raw )
			? (int) $raw
			: 0;
	elseif ( is_category() || is_tag() || is_tax() ) :
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) :
			$raw = get_term_meta( (int) $term->term_id, 'tcres_featured_video', true );
			$id  = is_numeric( $raw )
				? (int) $raw
				: 0;
		endif;
	endif;

	if ( $id <= 0 ) return 0;
	if ( get_post_type( $id ) !== 'attachment' ) return 0;
	if ( ! wp_attachment_is( 'video', $id ) ) return 0;

	return $id;
}


/**
 * Whether an attachment ID is a usable video
 */
function tcres_featured_video_attachment_is_valid( int $attachment_id ): bool {
	if ( $attachment_id <= 0 ) return false;
	if ( get_post_type( $attachment_id ) !== 'attachment' ) return false;

	return wp_attachment_is( 'video', $attachment_id );
}


/**
 * Get featured video HTML for the frontend
 *
 * @param array{
 *   class?: string,
 *   post_id?: int,
 *   term_id?: int,
 *   attrs?: array{
 *     autoplay?: bool,
 *     muted?: bool,
 *     loop?: bool,
 *     controls?: bool,
 *     playsinline?: bool
 *   }
 * } $args
 */
function tcres_featured_video_get( array $args = array() ): string {
	$post_id = isset( $args['post_id'] )
		? (int) $args['post_id']
		: 0;
	$term_id = isset( $args['term_id'] )
		? (int) $args['term_id']
		: 0;
	$id      = tcres_featured_video_id_get( $post_id, $term_id );
	if ( $id <= 0 ) return '';

	$url = wp_get_attachment_url( $id );
	if ( ! is_string( $url ) || $url === '' ) return '';

	$mime = get_post_mime_type( $id );
	$mime = is_string( $mime )
		? $mime
		: '';

	$class = 'tcres-featured-video';
	if ( isset( $args['class'] ) ) :
		$extra = trim( preg_replace( '/\s+/', ' ', (string) $args['class'] ) ?? '' );
		if ( $extra !== '' ) :
			$class .= ' ' . $extra;
		endif;
	endif;

	$opts = isset( $args['attrs'] ) && is_array( $args['attrs'] )
		? $args['attrs']
		: array();

	$autoplay    = array_key_exists( 'autoplay', $opts )
		? ! empty( $opts['autoplay'] )
		: false;
	$muted       = array_key_exists( 'muted', $opts )
		? ! empty( $opts['muted'] )
		: $autoplay;
	$loop        = array_key_exists( 'loop', $opts )
		? ! empty( $opts['loop'] )
		: false;
	$controls    = array_key_exists( 'controls', $opts )
		? ! empty( $opts['controls'] )
		: ! $autoplay;
	$playsinline = array_key_exists( 'playsinline', $opts )
		? ! empty( $opts['playsinline'] )
		: true;

	$html_attrs = ' class="' . esc_attr( $class ) . '"';
	if ( $autoplay ) $html_attrs .= ' autoplay';
	if ( $muted ) $html_attrs .= ' muted';
	if ( $loop ) $html_attrs .= ' loop';
	if ( $controls ) $html_attrs .= ' controls';
	if ( $playsinline ) $html_attrs .= ' playsinline';

	$html  = '<video' . $html_attrs . '>';
	$html .= '<source src="' . esc_url( $url ) . '"' . ( $mime !== '' ? ' type="' . esc_attr( $mime ) . '"' : '' ) . ' />';
	$html .= '</video>';

	return $html;
}


/**
 * Echo featured video HTML.
 *
 * @param array{
 *   class?: string,
 *   post_id?: int,
 *   term_id?: int,
 *   attrs?: array{
 *     autoplay?: bool,
 *     muted?: bool,
 *     loop?: bool,
 *     controls?: bool,
 *     playsinline?: bool
 *   }
 * } $args
 */
function tcres_featured_video( array $args = array() ): void {
	tcres_echo_html( tcres_featured_video_get( $args ) );
}


/**
 * Admin preview markup for a video attachment
 */
function tcres_featured_video_get_preview_html( int $attachment_id ): string {
	if ( ! tcres_featured_video_attachment_is_valid( $attachment_id ) ) return '';

	$url = wp_get_attachment_url( $attachment_id );
	if ( ! is_string( $url ) || $url === '' ) return '';

	$mime = get_post_mime_type( $attachment_id );
	$mime = is_string( $mime )
		? $mime
		: '';

	$html  = '<video class="tcres-featured-video-preview-player" muted preload="metadata" playsinline>';
	$html .= '<source src="' . esc_url( $url ) . '"' . ( $mime !== '' ? ' type="' . esc_attr( $mime ) . '"' : '' ) . ' />';
	$html .= '</video>';

	return $html;
}


/**
 * @param int $attachment_id
 */
function tcres_featured_video_render_field( int $attachment_id ): void {
	$preview   = tcres_featured_video_get_preview_html( $attachment_id );
	$has_video = $preview !== '';
	?>
	<div class="tcres-metabox-field">
		<div class="tcres-featured-video" data-tcres-featured-video>
			<input type="hidden"
				name="tcres_featured_video"
				id="tcres_featured_video"
				value="<?php echo esc_attr( (string) ( $has_video ? $attachment_id : 0 ) ); ?>"
				data-tcres-featured-video-input />
			<div class="tcres-featured-video-preview" data-tcres-featured-video-preview<?php echo $has_video ? '' : ' hidden'; ?>>
				<?php tcres_echo_html( $preview ); ?>
			</div>
			<p class="tcres-featured-video-actions">
				<button type="button" class="button" data-tcres-featured-video-select>
					<?php echo $has_video ? esc_html__( 'Replace video', 'tms-core-essentials' ) : esc_html__( 'Select video', 'tms-core-essentials' ); ?>
				</button>
			</p>
			<p class="tcres-featured-video-remove"<?php echo $has_video ? '' : ' hidden'; ?>>
				<a href="#" class="tcres-link-delete" data-tcres-featured-video-remove>
					<?php esc_html_e( 'Remove video', 'tms-core-essentials' ); ?>
				</a>
			</p>
		</div>
	</div>
	<?php
}


function tcres_featured_video_register_meta_box(): void {
	$post_types = tcres_featured_video_get_post_types();
	if ( empty( $post_types ) ) return;

	add_meta_box(
		'tcres_featured_video_metabox',
		__( 'Featured video', 'tms-core-essentials' ),
		'tcres_featured_video_render_meta_box',
		$post_types,
		'side',
		'low'
	);

	tcres_admin_register_metabox_classes(
		$post_types,
		'tcres_featured_video_metabox',
		array( 'tcres-metabox', 'tcres-featured-video-field' )
	);
}


/**
 * @param WP_Post $post
 */
function tcres_featured_video_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( '_tcres_featured_video_nonce', 'tcres_featured_video_nonce' );
	tcres_featured_video_render_field( tcres_featured_video_id_get( (int) $post->ID, 0 ) );
}


function tcres_featured_video_save_post( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if (
		! isset( $_POST['tcres_featured_video_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST['tcres_featured_video_nonce'] ) ),
			'_tcres_featured_video_nonce'
		)
		|| ! current_user_can( 'edit_post', $post_id )
	) :
		return;
	endif;

	$raw = isset( $_POST['tcres_featured_video'] )
		? absint( wp_unslash( $_POST['tcres_featured_video'] ) )
		: 0;
	if ( tcres_featured_video_attachment_is_valid( $raw ) ) :
		update_post_meta( $post_id, 'tcres_featured_video', $raw );
		return;
	endif;

	delete_post_meta( $post_id, 'tcres_featured_video' );
}


function tcres_featured_video_render_term_add_field(): void {
	wp_nonce_field( '_tcres_featured_video_nonce', 'tcres_featured_video_nonce' );
	?>
	<div class="form-field tcres-metabox tcres-featured-video-field">
		<label class="tcres-metabox-field-label" for="tcres_featured_video"><?php esc_html_e( 'Featured video', 'tms-core-essentials' ); ?></label>
		<?php tcres_featured_video_render_field( 0 ); ?>
	</div>
	<?php
}


/**
 * @param WP_Term $term
 */
function tcres_featured_video_render_term_edit_field( $term ): void {
	$attachment_id = 0;
	if ( $term instanceof WP_Term ) :
		$attachment_id = tcres_featured_video_id_get( 0, (int) $term->term_id );
	endif;

	wp_nonce_field( '_tcres_featured_video_nonce', 'tcres_featured_video_nonce' );
	?>
	<tr class="form-field tcres-metabox tcres-featured-video-field">
		<th scope="row">
			<label class="tcres-metabox-field-label" for="tcres_featured_video"><?php esc_html_e( 'Featured video', 'tms-core-essentials' ); ?></label>
		</th>
		<td>
			<?php tcres_featured_video_render_field( $attachment_id ); ?>
		</td>
	</tr>
	<?php
}


function tcres_featured_video_save_term( int $term_id ): void {
	if (
		! isset( $_POST['tcres_featured_video_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST['tcres_featured_video_nonce'] ) ),
			'_tcres_featured_video_nonce'
		)
		|| ! current_user_can( 'edit_term', $term_id )
	) :
		return;
	endif;

	$raw = isset( $_POST['tcres_featured_video'] )
		? absint( wp_unslash( $_POST['tcres_featured_video'] ) )
		: 0;
	if ( tcres_featured_video_attachment_is_valid( $raw ) ) :
		update_term_meta( $term_id, 'tcres_featured_video', $raw );
		return;
	endif;

	delete_term_meta( $term_id, 'tcres_featured_video' );
}


function tcres_featured_video_register_term_hooks(): void {
	foreach ( tcres_featured_video_get_taxonomies() as $taxonomy ) :
		add_action( "{$taxonomy}_add_form_fields", 'tcres_featured_video_render_term_add_field', 20 );
		add_action( "{$taxonomy}_edit_form_fields", 'tcres_featured_video_render_term_edit_field', 20 );
		add_action( "created_{$taxonomy}", 'tcres_featured_video_save_term' );
		add_action( "edited_{$taxonomy}", 'tcres_featured_video_save_term' );
	endforeach;
}


add_action( 'init', function (): void {
	$post_types = tcres_featured_video_get_post_types();
	$taxonomies = tcres_featured_video_get_taxonomies();
	if ( empty( $post_types ) && empty( $taxonomies ) ) return;

	if ( ! empty( $post_types ) ) :
		add_action( 'add_meta_boxes', 'tcres_featured_video_register_meta_box', 20 );
		add_action( 'save_post', 'tcres_featured_video_save_post' );
	endif;

	if ( ! empty( $taxonomies ) ) :
		tcres_featured_video_register_term_hooks();
	endif;
}, 20 );
