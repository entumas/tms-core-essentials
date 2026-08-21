<?php
/**
 * Includes -> API -> Validate image size
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_image_validate_size( int $attachment_id, ?array $width = null, ?array $height = null ): bool {
	$image_meta = wp_get_attachment_metadata( $attachment_id );
	if ( ! is_array( $image_meta ) ) return false;

	if ( null !== $width && null !== $height ) :
		return tcres_image_compare_size_with_operator( (int) $image_meta['width'], (int) $width['size'], (string) $width['operator'] )
			&& tcres_image_compare_size_with_operator( (int) $image_meta['height'], (int) $height['size'], (string) $height['operator'] );
	endif;

	if ( null !== $width ) :
		return tcres_image_compare_size_with_operator( (int) $image_meta['width'], (int) $width['size'], (string) $width['operator'] );
	endif;

	if ( null !== $height ) :
		return tcres_image_compare_size_with_operator( (int) $image_meta['height'], (int) $height['size'], (string) $height['operator'] );
	endif;

	return false;
}


function tcres_image_compare_size_with_operator( int $actual_size, int $target_size, string $operator = '==' ): bool {
	return match ( $operator ) {
		'>'  => $actual_size > $target_size,
		'>=' => $actual_size >= $target_size,
		'<'  => $actual_size < $target_size,
		'<=' => $actual_size <= $target_size,
		'==' => $actual_size === $target_size,
		default => false,
	};
}
