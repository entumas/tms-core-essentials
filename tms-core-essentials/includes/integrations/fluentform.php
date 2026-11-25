<?php
/**
 * Includes -> Integrations -> Fluent Forms
 * Expand plugin shortcodes (tcres-*) inside Custom HTML fields,
 * and print the privacy notice before the submit button on selected forms.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Print the contact, subscribe, or hiring notice for a Fluent Forms form ID.
 * Contact wins, then subscribe, then hiring.
 */
function tcres_fluentform_privacy_notice_for_form( int $form_id ): void {
	if ( $form_id <= 0 || is_admin() || ! tcres_privacy_notice_is_enabled() ) return;

	$contact_ids = tcres_option_get( 'privacy_notice', 'fluentform_contact_ids' );
	$contact_ids = is_array( $contact_ids )
		? array_map( 'absint', $contact_ids )
		: array();
	if ( in_array( $form_id, $contact_ids, true ) ) :
		tcres_privacy_notice_render( 'contact' );
		return;
	endif;

	$subscribe_ids = tcres_option_get( 'privacy_notice', 'fluentform_subscribe_ids' );
	$subscribe_ids = is_array( $subscribe_ids )
		? array_map( 'absint', $subscribe_ids )
		: array();
	if ( in_array( $form_id, $subscribe_ids, true ) ) :
		tcres_privacy_notice_render( 'subscribe' );
		return;
	endif;

	$hiring_ids = tcres_option_get( 'privacy_notice', 'fluentform_hiring_ids' );
	$hiring_ids = is_array( $hiring_ids )
		? array_map( 'absint', $hiring_ids )
		: array();
	if ( in_array( $form_id, $hiring_ids, true ) ) :
		tcres_privacy_notice_render( 'hiring' );
	endif;
}


add_action(
	'fluentform/render_item_submit_button',
	static function ( $_submit_button, $form ): void {
		$form_id = 0;
		if ( is_object( $form ) && isset( $form->id ) ) :
			$form_id = absint( $form->id );
		elseif ( is_array( $form ) && isset( $form['id'] ) ) :
			$form_id = absint( $form['id'] );
		endif;

		tcres_fluentform_privacy_notice_for_form( $form_id );
	},
	9,
	2
);


add_filter(
	'fluentform/rendering_field_html_custom_html',
	static function ( $html ) {
		if ( ! is_string( $html ) || $html === '' ) return $html;
		if ( ! function_exists( 'tcres_do_tcres_shortcodes' ) ) return $html;

		return tcres_do_tcres_shortcodes( $html );
	}
);
