<?php
/**
 * Includes -> API -> Output location
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @return array<string, string> location => label
 */
function tcres_output_location_labels(): array {
	return array(
		'manual'             => __( 'Manual', 'tms-core-essentials' ),
		'the_content_before' => __( 'Before post content', 'tms-core-essentials' ),
		'the_content_after'  => __( 'After post content', 'tms-core-essentials' ),
		'loop_end'           => __( 'loop_end', 'tms-core-essentials' ),
		'get_template_part'  => __( 'get_template_part', 'tms-core-essentials' ),
		'custom'             => __( 'Custom', 'tms-core-essentials' ),
	);
}


/**
 * @param array<int, string> $keys
 * @return array<string, string>
 */
function tcres_output_location_choices_for( array $keys ): array {
	$labels  = tcres_output_location_labels();
	$choices = array();

	foreach ( $keys as $key ) :
		$key = sanitize_key( (string) $key );
		if ( $key === '' || ! isset( $labels[ $key ] ) ) continue;
		$choices[ $key ] = $labels[ $key ];
	endforeach;

	return $choices;
}


/**
 * @return array<int, string>
 */
function tcres_output_location_keys_related_content(): array {
	return array( 'manual', 'the_content_after', 'loop_end', 'get_template_part', 'custom' );
}


/**
 * @return array<int, string>
 */
function tcres_output_location_keys_share_content(): array {
	return array( 'manual', 'the_content_before', 'the_content_after', 'loop_end', 'get_template_part', 'custom' );
}


/**
 * @return array<int, string>
 */
function tcres_output_location_keys_breadcrumbs(): array {
	return array( 'manual', 'the_content_before', 'get_template_part', 'custom' );
}


function tcres_output_location_needs_target( string $location ): bool {
	return in_array( $location, array( 'get_template_part', 'custom' ), true );
}


/**
 * @param array<string, mixed> $submitted
 * @param array<int, string>   $allowed
 * @return array{output_location: string, output_target: string}
 */
function tcres_output_location_sanitize_pair( array $submitted, array $allowed ): array {
	$location = isset( $submitted['output_location'] )
		? sanitize_key( (string) $submitted['output_location'] )
		: 'manual';

	if ( ! in_array( $location, $allowed, true ) ) :
		$location = 'manual';
	endif;

	$target = '';
	if ( tcres_output_location_needs_target( $location ) ) :
		$raw = isset( $submitted['output_target'] )
			? trim( (string) $submitted['output_target'] )
			: '';
		if ( $raw !== '' && preg_match( '/^[A-Za-z0-9_\/-]+$/', $raw ) ) :
			$target = $raw;
		endif;
	endif;

	return array(
		'output_location' => $location,
		'output_target'   => $target,
	);
}


/**
 * @return array{output_location: string, output_target: string}
 */
function tcres_output_location_get_pair( string $group_key ): array {
	$location = (string) tcres_option_get( $group_key, 'output_location' );
	$target   = (string) tcres_option_get( $group_key, 'output_target' );

	if ( $location === '' ) :
		$location = 'manual';
	endif;

	return array(
		'output_location' => $location,
		'output_target'   => $target,
	);
}


/**
 * Sanitize module HTML before it is injected into frontend output.
 */
function tcres_output_location_sanitize_html( string $html ): string {
	if ( $html === '' ) return '';

	return tcres_kses_html( $html );
}


/**
 * Resolve printable HTML from a callback that returns string or echoes.
 *
 * @param callable $callback
 */
function tcres_output_location_capture( callable $callback ): string {
	$result = $callback();
	if ( ! is_string( $result ) || $result === '' ) return '';

	return tcres_output_location_sanitize_html( $result );
}


/**
 * Register automatic frontend output for a settings group.
 *
 * @param callable():string|void $callback
 */
function tcres_output_location_register( string $group_key, callable $callback ): void {
	$pair     = tcres_output_location_get_pair( $group_key );
	$location = $pair['output_location'];
	$target   = $pair['output_target'];

	if ( $location === 'manual' || $location === '' ) return;

	$echo_html = static function () use ( $callback ): void {
		$html = tcres_output_location_capture( $callback );
		if ( $html !== '' ) :
			tcres_echo_html( $html );
		endif;
	};

	$can_inject_content = static function (): bool {
		return is_singular() && in_the_loop() && is_main_query();
	};

	switch ( $location ) :
		case 'the_content_before':
			add_filter(
				'the_content',
				static function ( $content ) use ( $callback, $can_inject_content ) {
					if ( ! is_string( $content ) || ! $can_inject_content() ) return $content;
					$html = tcres_output_location_capture( $callback );
					return $html === ''
						? $content
						: $html . $content;
				},
				12
			);
			break;

		case 'the_content_after':
			add_filter(
				'the_content',
				static function ( $content ) use ( $callback, $can_inject_content ) {
					if ( ! is_string( $content ) || ! $can_inject_content() ) return $content;
					$html = tcres_output_location_capture( $callback );
					return $html === ''
						? $content
						: $content . $html;
				},
				20
			);
			break;

		case 'loop_end':
			add_action(
				'loop_end',
				static function ( $query ) use ( $echo_html ): void {
					if ( ! $query instanceof WP_Query || ! $query->is_main_query() ) return;
					$echo_html();
				}
			);
			break;

		case 'get_template_part':
			if ( $target === '' ) return;
			add_action( 'get_template_part_' . $target, $echo_html );
			break;

		case 'custom':
			if ( $target === '' ) return;
			add_action( $target, $echo_html );
			break;
	endswitch;
}


add_action( 'wp', function(): void {
	if ( is_admin() && ! wp_doing_ajax() ) return;

	tcres_output_location_register(
		'related_content',
		static function (): string {
			if ( ! function_exists( 'tcres_related_content_is_enabled' ) || ! tcres_related_content_is_enabled() ) :
				return '';
			endif;
			return tcres_related_content_get();
		}
	);
	tcres_output_location_register( 'share_content', 'tcres_share_content_get' );
	tcres_output_location_register( 'breadcrumbs', 'tcres_breadcrumb_get' );
}, 20 );
