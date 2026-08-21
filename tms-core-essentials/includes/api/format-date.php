<?php
/**
 * Includes -> API -> Format date
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Format a date string using WordPress locale-aware formatting
 */
function tcres_date_format( string $date, string $format = '' ): string {
	if ( $format === '' ) $format = get_option( 'date_format' );

	$timestamp = tcres_date_format_parse_timestamp( $date );
	if ( false === $timestamp ) return '';

	return wp_date( $format, $timestamp );
}


/**
 * Parse a date string to a Unix timestamp
 */
function tcres_date_format_parse_timestamp( string $date ): int|false {
	$normalized = str_replace( '/', '-', trim( $date ) );
	if ( $normalized === '' ) return false;

	$timestamp = strtotime( $normalized );
	if ( false !== $timestamp ) return $timestamp;

	$timezone = wp_timezone();
	foreach ( array( 'Y-m-d H:i:s', 'Y-m-d', 'd-m-Y', 'm-d-Y' ) as $date_format ) :
		$parsed = DateTimeImmutable::createFromFormat( '!' . $date_format, $normalized, $timezone );
		if ( false === $parsed ) continue;

		$errors = DateTimeImmutable::getLastErrors();
		if ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) continue;

		return $parsed->getTimestamp();
	endforeach;

	return false;
}
