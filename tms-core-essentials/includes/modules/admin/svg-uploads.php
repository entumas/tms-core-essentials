<?php
/**
 * Includes -> Modules -> Admin -> SVG uploads
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_svg_uploads_is_enabled(): bool {
	return (bool) tcres_option_get( 'svg_uploads', 'enable' );
}


/**
 * Whether the current user may upload SVG files.
 */
function tcres_svg_uploads_current_user_can(): bool {
	if ( ! tcres_svg_uploads_is_enabled() ) return false;
	if ( ! is_user_logged_in() || ! current_user_can( 'upload_files' ) ) return false;

	$allowed = tcres_option_get( 'svg_uploads', 'roles' );
	if ( ! is_array( $allowed ) ) return false;

	$user = wp_get_current_user();
	foreach ( (array) $user->roles as $role ) :
		if ( ! empty( $allowed[ $role ] ) ) return true;
	endforeach;

	return false;
}


/**
 * @param array<string, string> $mimes
 * @return array<string, string>
 */
add_filter( 'upload_mimes', function( array $mimes ): array {
	if ( ! tcres_svg_uploads_current_user_can() ) return $mimes;

	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
} );


/**
 * Fix WordPress filetype checks that often fail for SVG (finfo / getimagesize).
 *
 * @param array{ext?: string|false, type?: string|false, proper_filename?: string|false} $data
 * @param array<string, string>|null                                                     $mimes
 * @return array{ext?: string|false, type?: string|false, proper_filename?: string|false}
 */
add_filter( 'wp_check_filetype_and_ext', function( array $data, string $file, string $filename, $mimes ): array {
	unset( $file, $mimes );

	if ( ! tcres_svg_uploads_current_user_can() ) return $data;

	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( $ext !== 'svg' ) return $data;

	$data['ext']  = 'svg';
	$data['type'] = 'image/svg+xml';

	return $data;
}, 10, 4 );


/**
 * @return array<int, string>
 */
function tcres_svg_uploads_get_disallowed_tags(): array {
	return array(
		'script',
		'foreignobject',
		'iframe',
		'embed',
		'object',
		'applet',
		'audio',
		'video',
		'canvas',
		'link',
		'meta',
		'base',
		'form',
		'input',
		'button',
		'textarea',
		'select',
		'option',
		'handler',
		'animate',
		'set',
		'prefetch',
	);
}


/**
 * Whether an attribute value is an unsafe URI.
 */
function tcres_svg_uploads_is_unsafe_uri( string $value ): bool {
	$value = trim( html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$value = preg_replace( '/\s+/', '', $value ) ?? $value;

	if ( $value === '' ) return false;
	if ( str_starts_with( $value, '#' ) ) return false;

	$lower = strtolower( $value );

	if (
		str_starts_with( $lower, 'javascript:' )
		|| str_starts_with( $lower, 'vbscript:' )
		|| str_starts_with( $lower, 'data:text/html' )
		|| str_starts_with( $lower, 'data:application' )
	) :
		return true;
	endif;

	// External references in href / xlink:href / use.
	if (
		str_starts_with( $lower, 'http:' )
		|| str_starts_with( $lower, 'https:' )
		|| str_starts_with( $lower, '//' )
		|| str_starts_with( $lower, 'data:' )
	) :
		return true;
	endif;

	return false;
}


/**
 * Sanitize SVG markup. Returns WP_Error when the file cannot be made safe.
 *
 * @return string|WP_Error
 */
function tcres_svg_uploads_sanitize_contents( string $contents ) {
	$contents = trim( $contents );
	if ( $contents === '' ) :
		return new WP_Error(
			'tcres_svg_empty',
			__( 'The SVG file is empty.', 'tms-core-essentials' )
		);
	endif;

	// Block PHP / null bytes early.
	if ( str_contains( $contents, '<?php' ) || str_contains( $contents, "\0" ) ) :
		return new WP_Error(
			'tcres_svg_unsafe',
			__( 'The SVG file contains unsafe content.', 'tms-core-essentials' )
		);
	endif;

	// Strip XML DOCTYPE / ENTITY declarations (XXE).
	$contents = preg_replace( '/<!DOCTYPE[^>]*>/i', '', $contents ) ?? $contents;
	$contents = preg_replace( '/<!ENTITY[^>]*>/i', '', $contents ) ?? $contents;

	if ( ! class_exists( 'DOMDocument', false ) ) :
		return new WP_Error(
			'tcres_svg_dom',
			__( 'SVG sanitization requires DOMDocument.', 'tms-core-essentials' )
		);
	endif;

	$previous                = libxml_use_internal_errors( true );
	$dom                     = new DOMDocument();
	$dom->preserveWhiteSpace = true;
	$dom->formatOutput       = false;

	$loaded = $dom->loadXML( $contents, LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $dom->documentElement ) :
		return new WP_Error(
			'tcres_svg_invalid',
			__( 'The SVG file is not valid XML.', 'tms-core-essentials' )
		);
	endif;

	$root = $dom->documentElement;
	if ( strtolower( $root->localName ?: $root->nodeName ) !== 'svg' ) :
		return new WP_Error(
			'tcres_svg_root',
			__( 'The uploaded file is not an SVG document.', 'tms-core-essentials' )
		);
	endif;

	$disallowed = tcres_svg_uploads_get_disallowed_tags();
	$xpath      = new DOMXPath( $dom );

	// Remove disallowed elements (case-insensitive via local-name).
	foreach ( $disallowed as $tag ) :
		$nodes = $xpath->query( '//*[translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="' . $tag . '"]' );
		if ( ! $nodes ) continue;

		$to_remove = array();
		foreach ( $nodes as $node ) :
			$to_remove[] = $node;
		endforeach;

		foreach ( $to_remove as $node ) :
			if ( $node->parentNode ) :
				$node->parentNode->removeChild( $node );
			endif;
		endforeach;
	endforeach;

	// Scrub attributes on remaining elements.
	$elements = $dom->getElementsByTagName( '*' );
	$to_clean = array();
	foreach ( $elements as $element ) :
		$to_clean[] = $element;
	endforeach;

	foreach ( $to_clean as $element ) :
		if ( ! $element instanceof DOMElement ) continue;

		$remove_attrs = array();
		foreach ( $element->attributes ?? array() as $attr ) :
			if ( ! $attr instanceof DOMAttr ) continue;

			$name  = strtolower( $attr->localName ?: $attr->name );
			$value = (string) $attr->value;

			if ( str_starts_with( $name, 'on' ) ) :
				$remove_attrs[] = $attr->name;
				continue;
			endif;

			if ( in_array( $name, array( 'href', 'xlink:href', 'src', 'action', 'formaction', 'xlink:actuate' ), true )
				|| str_ends_with( $name, 'href' )
			) :
				if ( tcres_svg_uploads_is_unsafe_uri( $value ) ) :
					$remove_attrs[] = $attr->name;
				endif;
			endif;
		endforeach;

		foreach ( $remove_attrs as $attr_name ) :
			$element->removeAttribute( $attr_name );
		endforeach;
	endforeach;

	$sanitized = $dom->saveXML( $dom->documentElement );
	if ( ! is_string( $sanitized ) || trim( $sanitized ) === '' ) :
		return new WP_Error(
			'tcres_svg_sanitize_failed',
			__( 'The SVG file could not be sanitized.', 'tms-core-essentials' )
		);
	endif;

	return $sanitized;
}


/**
 * @param array{name?: string, type?: string, tmp_name?: string, error?: int|string, size?: int} $file
 * @return array{name?: string, type?: string, tmp_name?: string, error?: int|string, size?: int}
 */
function tcres_svg_uploads_handle_upload_prefilter( array $file ): array {
	$name = isset( $file['name'] )
		? (string) $file['name']
		: '';
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
	if ( $ext !== 'svg' ) return $file;

	if ( ! tcres_svg_uploads_is_enabled() || ! tcres_svg_uploads_current_user_can() ) :
		$file['error'] = __( 'You are not allowed to upload SVG files.', 'tms-core-essentials' );
		return $file;
	endif;

	$tmp = isset( $file['tmp_name'] )
		? (string) $file['tmp_name']
		: '';
	if ( $tmp === '' || ! is_readable( $tmp ) ) :
		$file['error'] = __( 'The SVG upload could not be read.', 'tms-core-essentials' );
		return $file;
	endif;

	$contents = file_get_contents( $tmp );
	if ( ! is_string( $contents ) ) :
		$file['error'] = __( 'The SVG upload could not be read.', 'tms-core-essentials' );
		return $file;
	endif;

	$sanitized = tcres_svg_uploads_sanitize_contents( $contents );
	if ( is_wp_error( $sanitized ) ) :
		$file['error'] = $sanitized->get_error_message();
		return $file;
	endif;

	if ( file_put_contents( $tmp, $sanitized ) === false ) :
		$file['error'] = __( 'The SVG file could not be sanitized.', 'tms-core-essentials' );
		return $file;
	endif;

	$file['type'] = 'image/svg+xml';

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'tcres_svg_uploads_handle_upload_prefilter' );
add_filter( 'wp_handle_sideload_prefilter', 'tcres_svg_uploads_handle_upload_prefilter' );


/**
 * Skip raster metadata generation for SVGs.
 *
 * @param array<string, mixed> $metadata
 * @return array<string, mixed>
 */
add_filter( 'wp_generate_attachment_metadata', function( array $metadata, int $attachment_id ): array {
	$mime = get_post_mime_type( $attachment_id );
	if ( $mime !== 'image/svg+xml' ) return $metadata;

	$file = get_attached_file( $attachment_id );
	if ( ! is_string( $file ) || $file === '' || ! is_readable( $file ) ) return $metadata;

	$contents = file_get_contents( $file );
	if ( ! is_string( $contents ) ) return $metadata;

	$width  = 0;
	$height = 0;

	if ( preg_match( '/viewBox=["\']\s*[0-9.\s\-]+\s+([0-9.]+)\s+([0-9.]+)\s*["\']/i', $contents, $m ) ) :
		$width  = (int) round( (float) $m[1] );
		$height = (int) round( (float) $m[2] );
	elseif ( preg_match( '/\bwidth=["\']([0-9.]+)/i', $contents, $mw )
		&& preg_match( '/\bheight=["\']([0-9.]+)/i', $contents, $mh )
	) :
		$width  = (int) round( (float) $mw[1] );
		$height = (int) round( (float) $mh[1] );
	endif;

	if ( $width > 0 && $height > 0 ) :
		$metadata['width']  = $width;
		$metadata['height'] = $height;
	endif;

	$metadata['file'] = _wp_relative_upload_path( $file );

	return $metadata;
}, 10, 2 );


/**
 * Prefer the SVG URL for attachment image sources (no generated sizes).
 *
 * @param array{0?: string, 1?: int, 2?: int, 3?: bool}|false $image
 * @return array{0?: string, 1?: int, 2?: int, 3?: bool}|false
 */
add_filter( 'image_downsize', function( $image, int $attachment_id, $size ) {
	unset( $size );

	if ( get_post_mime_type( $attachment_id ) !== 'image/svg+xml' ) return $image;

	$url = wp_get_attachment_url( $attachment_id );
	if ( ! is_string( $url ) || $url === '' ) return $image;

	$meta   = wp_get_attachment_metadata( $attachment_id );
	$width  = isset( $meta['width'] )
		? (int) $meta['width']
		: 0;
	$height = isset( $meta['height'] )
		? (int) $meta['height']
		: 0;

	return array( $url, $width, $height, false );
}, 10, 3 );


/**
 * Media library JS response: include dimensions for SVG attachments.
 *
 * @param array<string, mixed> $response
 * @return array<string, mixed>
 */
add_filter( 'wp_prepare_attachment_for_js', function( array $response, WP_Post $attachment ): array {
	if ( ( $response['mime'] ?? '' ) !== 'image/svg+xml' && get_post_mime_type( $attachment ) !== 'image/svg+xml' ) return $response;

	$meta   = wp_get_attachment_metadata( $attachment->ID );
	$width  = isset( $meta['width'] )
		? (int) $meta['width']
		: 0;
	$height = isset( $meta['height'] )
		? (int) $meta['height']
		: 0;

	if ( $width > 0 && $height > 0 ) :
		$response['width']  = $width;
		$response['height'] = $height;
		$response['sizes']  = array(
			'full' => array(
				'url'         => $response['url'] ?? wp_get_attachment_url( $attachment->ID ),
				'width'       => $width,
				'height'      => $height,
				'orientation' => $height > $width ? 'portrait' : 'landscape',
			),
		);
	endif;

	return $response;
}, 10, 2 );


add_action( 'admin_enqueue_scripts', function(): void {
	if ( ! tcres_svg_uploads_is_enabled() ) return;

	$screen = function_exists( 'get_current_screen' )
		? get_current_screen()
		: null;
	if ( ! $screen ) return;

	$ids = array( 'upload', 'media', 'attachment' );
	if ( ! in_array( $screen->id, $ids, true ) && $screen->base !== 'upload' ) return;

	$css = '.attachment .thumbnail img[src$=".svg"],
		.media-icon img[src$=".svg"],
		table.media .column-title .media-icon img[src$=".svg"] {
			width: 100%;
			height: auto;
		}';

	wp_register_style( 'tcres-svg-uploads-admin', false, array(), TCRES_PLUGIN_VERSION );
	wp_enqueue_style( 'tcres-svg-uploads-admin' );
	wp_add_inline_style( 'tcres-svg-uploads-admin', $css );
} );
