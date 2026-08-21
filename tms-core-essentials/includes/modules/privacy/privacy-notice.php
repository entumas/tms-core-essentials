<?php
/**
 * Includes -> Modules -> Privacy -> Privacy notice
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @return array<int, string>
 */
function tcres_privacy_notice_keys(): array {
	return array( 'contact', 'subscribe', 'comments', 'register', 'checkout' );
}


function tcres_privacy_notice_is_enabled(): bool {
	return (bool) tcres_option_get( 'privacy_notice', 'enable' );
}


function tcres_privacy_notice_default_trigger(): string {
	return __( 'Before submitting the form, take a look at the basic information on data protection here.', 'tms-core-essentials' );
}


/**
 * Trigger HTML for the current language (settings override or default).
 * Inline content only — collapse title must not wrap the label in <p>.
 */
function tcres_privacy_notice_get_trigger(): string {
	$stored = tcres_option_get( 'privacy_notice', 'trigger' );
	$stored = is_string( $stored )
		? trim( $stored )
		: '';

	if ( $stored !== '' && trim( wp_strip_all_tags( $stored ) ) !== '' ) :
		$html = wp_kses_post( $stored );
		$html = preg_replace( '#</?(p|div|h[1-6]|ul|ol|li|blockquote)(?:\s[^>]*)?>#i', '', $html );
		return trim( (string) $html );
	endif;

	return esc_html( tcres_privacy_notice_default_trigger() );
}


/**
 * Notice body HTML for a key (current language via tcres_settings_get merge).
 */
function tcres_privacy_notice_get_content( string $notice ): string {
	$notice = sanitize_key( $notice );
	if ( ! in_array( $notice, tcres_privacy_notice_keys(), true ) ) return '';

	$content = tcres_option_get( 'privacy_notice', $notice );
	$content = is_string( $content )
		? trim( $content )
		: '';
	if ( $content === '' || trim( wp_strip_all_tags( $content ) ) === '' ) return '';

	return tcres_format_editor_content( $content );
}


/**
 * @param array{notice?: string} $args
 */
function tcres_privacy_notice_get( array $args = array() ): string {
	if ( ! tcres_privacy_notice_is_enabled() ) return '';

	$notice = isset( $args['notice'] )
		? sanitize_key( (string) $args['notice'] )
		: '';
	if ( $notice === '' || ! in_array( $notice, tcres_privacy_notice_keys(), true ) ) return '';

	$content = tcres_privacy_notice_get_content( $notice );
	if ( $content === '' ) return '';

	$trigger     = tcres_privacy_notice_get_trigger();
	$collapse_id = tcres_unique_id( 'tcres-collapse-' );

	ob_start();
	?>
	<div class="tcres-privacy-notice tcres-collapse">
		<div
			class="tcres-privacy-notice-trigger tcres-collapse-title"
			role="button"
			tabindex="0"
			aria-expanded="false"
			data-tcres-collapse-trigger
			data-tcres-collapse-id="<?php echo esc_attr( $collapse_id ); ?>">
			<?php echo $trigger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses'd above. ?>
		</div>
		<div
			class="tcres-privacy-notice-content tcres-collapse-content"
			data-tcres-collapse-target
			data-tcres-collapse-id="<?php echo esc_attr( $collapse_id ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses'd above. ?>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}


function tcres_privacy_notice_render( string $notice ): void {
	echo tcres_privacy_notice_get( array( 'notice' => $notice ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in getter.
}


// Shortcode ========================================

add_shortcode( 'tcres-privacy-notice', static function ( $atts ): string {
	$atts = shortcode_atts(
		array(
			'notice' => '',
		),
		$atts,
		'tcres-privacy-notice'
	);

	return tcres_privacy_notice_get(
		array(
			'notice' => (string) $atts['notice'],
		)
	);
} );


// WPForms selectors ========================================

add_action(
	'wpforms_display_submit_before',
	static function ( $form_data ): void {
		if ( is_admin() || ! tcres_privacy_notice_is_enabled() ) return;

		$form_id = 0;
		if ( is_array( $form_data ) && isset( $form_data['id'] ) ) :
			$form_id = absint( $form_data['id'] );
		endif;
		if ( $form_id <= 0 ) return;

		$contact_ids = tcres_option_get( 'privacy_notice', 'wpforms_contact_ids' );
		$contact_ids = is_array( $contact_ids )
			? array_map( 'absint', $contact_ids )
			: array();
		if ( in_array( $form_id, $contact_ids, true ) ) :
			tcres_privacy_notice_render( 'contact' );
			return;
		endif;

		$subscribe_ids = tcres_option_get( 'privacy_notice', 'wpforms_subscribe_ids' );
		$subscribe_ids = is_array( $subscribe_ids )
			? array_map( 'absint', $subscribe_ids )
			: array();
		if ( in_array( $form_id, $subscribe_ids, true ) ) :
			tcres_privacy_notice_render( 'subscribe' );
		endif;
	},
	10,
	1
);


// Automatic outputs ========================================

/**
 * Comments: before submit button (after consent @20 on the same filter).
 */
add_filter(
	'comment_form_submit_field',
	static function ( $submit_field ) {
		if ( ! tcres_privacy_notice_is_enabled() ) return $submit_field;

		$notice = tcres_privacy_notice_get( array( 'notice' => 'comments' ) );
		if ( $notice === '' ) return $submit_field;

		return $notice . $submit_field;
	},
	10
);

add_action(
	'register_form',
	static function (): void {
		if ( ! tcres_privacy_notice_is_enabled() ) return;
		tcres_privacy_notice_render( 'register' );
	},
	20
);

add_action(
	'woocommerce_register_form',
	static function (): void {
		if ( ! tcres_privacy_notice_is_enabled() ) return;
		tcres_privacy_notice_render( 'register' );
	},
	20
);


/**
 * Whether the Checkout page uses the WooCommerce Checkout block.
 */
function tcres_privacy_notice_checkout_is_block(): bool {
	if ( class_exists( '\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils' )
		&& method_exists( '\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils', 'is_checkout_block_default' )
	) :
		return (bool) \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
	endif;

	$page_id = (int) get_option( 'woocommerce_checkout_page_id' );
	if ( $page_id <= 0 || ! function_exists( 'has_block' ) ) return false;

	return has_block( 'woocommerce/checkout', $page_id );
}


/**
 * Classic shortcode checkout (order review / place order).
 */
add_action(
	'woocommerce_review_order_before_submit',
	static function (): void {
		if ( ! tcres_privacy_notice_is_enabled() ) return;
		if ( tcres_privacy_notice_checkout_is_block() ) return;
		tcres_privacy_notice_render( 'checkout' );
	}
);


/**
 * Block checkout: inject before the place-order actions block.
 */
add_filter(
	'render_block_woocommerce/checkout-actions-block',
	static function ( string $content ): string {
		if ( is_admin() || ! tcres_privacy_notice_is_enabled() ) return $content;

		$notice = tcres_privacy_notice_get( array( 'notice' => 'checkout' ) );
		if ( $notice === '' ) return $content;

		return $notice . $content;
	},
	10,
	1
);


/**
 * Block checkout fallback: keep markup available for JS if the block re-renders client-side.
 */
add_action(
	'wp_footer',
	static function (): void {
		if ( is_admin() || ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) return;
		if ( ! tcres_privacy_notice_is_enabled() ) return;
		if ( ! tcres_privacy_notice_checkout_is_block() ) return;

		$notice = tcres_privacy_notice_get( array( 'notice' => 'checkout' ) );
		if ( $notice === '' ) return;

		printf(
			'<template id="tcres-privacy-notice-checkout-template">%s</template>',
			$notice // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses'd in getter.
		);
	},
	5
);
