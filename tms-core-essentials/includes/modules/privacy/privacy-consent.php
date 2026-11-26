<?php
/**
 * Includes -> Modules -> Privacy -> Privacy consent
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @return array<int, string>
 */
function tcres_privacy_consent_form_keys(): array {
	return array( 'comments', 'register', 'checkout' );
}


function tcres_privacy_consent_is_enabled(): bool {
	return (bool) tcres_option_get( 'privacy_consent', 'enable' );
}


function tcres_privacy_consent_is_enabled_for( string $form ): bool {
	$form = sanitize_key( $form );
	if ( ! in_array( $form, tcres_privacy_consent_form_keys(), true ) ) return false;
	if ( ! tcres_privacy_consent_is_enabled() ) return false;

	return (bool) tcres_option_get( 'privacy_consent', $form );
}


/**
 * Default label template ( %s = privacy policy link markup ).
 */
function tcres_privacy_consent_default_label_template(): string {
	/* translators: %s: HTML link to the WordPress privacy policy page */
	return __( 'I accept the %s.', 'tms-core-essentials' );
}


/**
 * Linked “privacy policy” label pointing at the WP privacy policy page.
 */
function tcres_privacy_consent_privacy_policy_link_html(): string {
	$url = '';
	if ( function_exists( 'get_privacy_policy_url' ) ) :
		$url = (string) get_privacy_policy_url();
	endif;

	if ( $url === '' ) :
		$page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $page_id > 0 ) :
			$permalink = get_permalink( $page_id );
			$url       = is_string( $permalink )
				? $permalink
				: '';
		endif;
	endif;

	$text = esc_html__( 'privacy policy', 'tms-core-essentials' );

	// Always render an <a> (Shotgun behaviour). Empty href if no page is set yet.
	return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
}


function tcres_privacy_consent_default_label_html(): string {
	return sprintf(
		tcres_privacy_consent_default_label_template(),
		tcres_privacy_consent_privacy_policy_link_html()
	);
}


/**
 * Plain placeholder for the settings editor (shows the [privacy_policy] token).
 */
function tcres_privacy_consent_default_label_placeholder(): string {
	return sprintf(
		tcres_privacy_consent_default_label_template(),
		'[privacy_policy]'
	);
}


/**
 * Replace [privacy_policy] with a link to the site privacy policy page.
 */
function tcres_privacy_consent_expand_tokens( string $html ): string {
	return str_replace( '[privacy_policy]', tcres_privacy_consent_privacy_policy_link_html(), $html );
}


/**
 * Checkbox label HTML (settings override or default).
 */
function tcres_privacy_consent_get_label_html(): string {
	$stored = tcres_option_get( 'privacy_consent', 'label' );
	$stored = is_string( $stored )
		? trim( $stored )
		: '';

	if ( $stored !== '' && trim( wp_strip_all_tags( $stored ) ) !== '' ) :
		return tcres_privacy_consent_expand_tokens( wp_kses_post( $stored ) );
	endif;

	return tcres_privacy_consent_default_label_html();
}


function tcres_privacy_consent_field_name(): string {
	return 'tcres_privacy_consent';
}


function tcres_privacy_consent_error_message(): string {
	return __( 'Please accept the privacy policy.', 'tms-core-essentials' );
}


/**
 * @param array{form?: string, id?: string, required?: bool} $args
 */
function tcres_privacy_consent_get( array $args = array() ): string {
	$form = isset( $args['form'] )
		? sanitize_key( (string) $args['form'] )
		: '';
	if ( $form === '' || ! tcres_privacy_consent_is_enabled_for( $form ) ) return '';

	$id = isset( $args['id'] )
		? sanitize_html_class( (string) $args['id'] )
		: '';
	if ( $id === '' ) :
		$id = 'tcres-privacy-consent-' . $form;
	endif;

	$name     = tcres_privacy_consent_field_name();
	$label    = tcres_privacy_consent_get_label_html();
	$required = ! array_key_exists( 'required', $args ) || ! empty( $args['required'] );
	$class    = 'tcres-privacy-consent comment-form-privacy-consent';
	// Checkout inline errors are appended to the closest .form-row.
	if ( 'checkout' === $form ) :
		$class .= ' form-row validate-required';
	endif;

	ob_start();
	?>
	<p class="<?php echo esc_attr( $class ); ?>">
		<input
			type="checkbox"
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			value="1"
			<?php if ( $required ) : ?>
			required="required"
			<?php endif; ?>
			aria-required="true"
			data-tcres-privacy-consent="1"
			data-tcres-privacy-consent-error="<?php echo esc_attr( tcres_privacy_consent_error_message() ); ?>" />
		<label for="<?php echo esc_attr( $id ); ?>">
			<?php tcres_echo_html( $label ); ?>
		</label>
	</p>
	<?php

	return (string) ob_get_clean();
}


/**
 * Post kses drops input. Consent markup needs the checkbox and its attributes.
 *
 * @return array<string, array<string, bool>>
 */
function tcres_privacy_consent_allowed_html(): array {
	$allowed = tcres_get_kses_allowed_html();

	$allowed['input'] = array(
		'type'                             => true,
		'id'                               => true,
		'name'                             => true,
		'value'                            => true,
		'required'                         => true,
		'aria-required'                    => true,
		'data-tcres-privacy-consent'       => true,
		'data-tcres-privacy-consent-error' => true,
	);

	return $allowed;
}


function tcres_privacy_consent_kses( string $html ): string {
	if ( $html === '' ) return '';

	return wp_kses( $html, tcres_privacy_consent_allowed_html() );
}


function tcres_privacy_consent_render( string $form, string $id = '', bool $required = true ): void {
	echo tcres_privacy_consent_kses(
		tcres_privacy_consent_get(
			array(
				'form'     => $form,
				'id'       => $id,
				'required' => $required,
			)
		)
	);
}


function tcres_privacy_consent_request_is_accepted(): bool {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- validated in form-specific handlers.
	if ( ! isset( $_POST[ tcres_privacy_consent_field_name() ] ) ) return false;

	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$value = wp_unslash( $_POST[ tcres_privacy_consent_field_name() ] );

	return ! empty( $value );
}


// Comments ========================================

/**
 * Inject before the submit button: consent, then notice, then button.
 * Priorities: notice @10 prepends first; consent @20 prepends before that.
 */
add_filter(
	'comment_form_submit_field',
	static function ( $submit_field ) {
		$consent = tcres_privacy_consent_get(
			array(
				'form' => 'comments',
				'id'   => 'tcres-privacy-consent-comments',
			)
		);

		return tcres_privacy_consent_kses( $consent ) . $submit_field;
	},
	20
);

add_filter(
	'preprocess_comment',
	static function ( $commentdata ) {
		if ( is_admin() || ! tcres_privacy_consent_is_enabled_for( 'comments' ) ) return $commentdata;

		if ( ! tcres_privacy_consent_request_is_accepted() ) :
			wp_die(
				esc_html( tcres_privacy_consent_error_message() ),
				esc_html__( 'Comment Submission Failure', 'tms-core-essentials' ),
				array( 'response' => 403, 'back_link' => true )
			);
		endif;

		return $commentdata;
	}
);

add_action(
	'comment_post',
	static function ( $comment_id ): void {
		if ( ! tcres_privacy_consent_is_enabled_for( 'comments' ) ) return;
		if ( ! tcres_privacy_consent_request_is_accepted() ) return;

		add_comment_meta( (int) $comment_id, 'tcres_privacy_consent', '1', true );
	}
);


// Register (WP + WooCommerce) ========================================

add_action(
	'register_form',
	static function (): void {
		tcres_privacy_consent_render( 'register', 'tcres-privacy-consent-register' );
	},
	10
);

add_action(
	'woocommerce_register_form',
	static function (): void {
		tcres_privacy_consent_render( 'register', 'tcres-privacy-consent-register-woo' );
	},
	10
);

add_filter(
	'registration_errors',
	static function ( $errors ) {
		if ( is_admin() || ! tcres_privacy_consent_is_enabled_for( 'register' ) ) return $errors;
		if ( ! ( $errors instanceof WP_Error ) ) $errors = new WP_Error();

		if ( ! tcres_privacy_consent_request_is_accepted() ) :
			$errors->add( 'tcres_privacy_consent', tcres_privacy_consent_error_message() );
		endif;

		return $errors;
	}
);

add_filter(
	'woocommerce_registration_errors',
	static function ( $errors ) {
		if ( ! tcres_privacy_consent_is_enabled_for( 'register' ) ) return $errors;
		if ( ! ( $errors instanceof WP_Error ) ) $errors = new WP_Error();

		if ( ! tcres_privacy_consent_request_is_accepted() ) :
			$errors->add( 'tcres_privacy_consent', tcres_privacy_consent_error_message() );
		endif;

		return $errors;
	}
);

add_action(
	'user_register',
	static function ( $user_id ): void {
		if ( ! tcres_privacy_consent_is_enabled_for( 'register' ) ) return;
		if ( ! tcres_privacy_consent_request_is_accepted() ) return;

		update_user_meta( (int) $user_id, 'tcres_privacy_consent', '1' );
	}
);


// Checkout (classic) ========================================

function tcres_privacy_consent_checkout_is_block(): bool {
	if ( function_exists( 'tcres_privacy_notice_checkout_is_block' ) ) :
		return tcres_privacy_notice_checkout_is_block();
	endif;

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
 * Above the privacy notice on the same hook (notice @10).
 */
add_action(
	'woocommerce_review_order_before_submit',
	static function (): void {
		if ( ! tcres_privacy_consent_is_enabled_for( 'checkout' ) ) return;
		if ( tcres_privacy_consent_checkout_is_block() ) return;
		tcres_privacy_consent_render( 'checkout', 'tcres-privacy-consent-checkout', false );
	},
	5
);

add_action(
	'woocommerce_after_checkout_validation',
	static function ( $data, $errors ): void {
		unset( $data );
		if ( ! tcres_privacy_consent_is_enabled_for( 'checkout' ) ) return;
		if ( tcres_privacy_consent_checkout_is_block() ) return;
		if ( ! ( $errors instanceof WP_Error ) ) return;
		if ( tcres_privacy_consent_request_is_accepted() ) return;

		$errors->add(
			'tcres_privacy_consent',
			tcres_privacy_consent_error_message(),
			array( 'id' => 'tcres-privacy-consent-checkout' )
		);
	},
	10,
	2
);

add_action(
	'woocommerce_checkout_create_order',
	static function ( $order ): void {
		if ( ! tcres_privacy_consent_is_enabled_for( 'checkout' ) ) return;
		if ( ! tcres_privacy_consent_request_is_accepted() ) return;
		if ( ! is_object( $order ) || ! method_exists( $order, 'update_meta_data' ) ) return;

		$order->update_meta_data( '_tcres_privacy_consent', '1' );
	}
);


// Checkout (block / additional fields API) ========================================

add_action(
	'woocommerce_init',
	static function (): void {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) return;
		if ( ! tcres_privacy_consent_is_enabled_for( 'checkout' ) ) return;

		$label_plain = trim( wp_strip_all_tags( tcres_privacy_consent_get_label_html() ) );
		if ( $label_plain === '' ) :
			$label_plain = __( 'I accept the privacy policy.', 'tms-core-essentials' );
		endif;

		woocommerce_register_additional_checkout_field(
			array(
				'id'                => 'tcres/privacy-consent',
				'label'             => $label_plain,
				'location'          => 'order',
				'type'              => 'checkbox',
				'required'          => true,
				'validate_callback' => static function ( $field_value ) {
					if ( empty( $field_value ) ) :
						return new WP_Error( 'tcres_privacy_consent', tcres_privacy_consent_error_message() );
					endif;
					return true;
				},
			)
		);
	}
);

add_action(
	'woocommerce_set_additional_field_value',
	static function ( $key, $value, $group, $wc_object ): void {
		if ( 'tcres/privacy-consent' !== $key ) return;
		if ( ! tcres_privacy_consent_is_enabled_for( 'checkout' ) ) return;
		if ( empty( $value ) ) return;
		if ( ! is_object( $wc_object ) || ! method_exists( $wc_object, 'update_meta_data' ) ) return;

		$wc_object->update_meta_data( '_tcres_privacy_consent', '1' );
	},
	10,
	4
);
