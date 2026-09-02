<?php
/**
 * Includes -> Modules -> Privacy -> Google Consent Mode
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_google_consent_mode_is_enabled(): bool {
	return (bool) tcres_option_get( 'google_consent_mode', 'enable' );
}


function tcres_google_consent_mode_get_default_script(): string {
	return <<<'JS'
	window.dataLayer = window.dataLayer || [];
	window.gtag = window.gtag || function() { dataLayer.push( arguments ); };
	window.gtag( 'consent', 'default', {
		ad_storage: 'denied',
		ad_user_data: 'denied',
		ad_personalization: 'denied',
		analytics_storage: 'denied'
	} );
	JS;
}


function tcres_google_consent_mode_get_update_script(): string {
	return <<<'JS'
	document.addEventListener( 'gdpr_cookie_compliance_subcategory_change', function( e ) {
		if ( ! e || ! e.detail ) return;

		const statsConsent = e.detail.statistics === true
			? 'granted'
			: 'denied';
		const marketingConsent = e.detail.marketing === true
			? 'granted'
			: 'denied';

		window.dataLayer = window.dataLayer || [];
		window.gtag = window.gtag || function() { dataLayer.push( arguments ); };
		window.gtag( 'consent', 'update', {
			analytics_storage: statsConsent,
			ad_storage: marketingConsent,
			ad_user_data: marketingConsent,
			ad_personalization: marketingConsent
		} );
	} );
	JS;
}


/**
 * Print default consent as early as possible in wp_head (before GTM/gtag).
 */
add_action( 'wp_head', function(): void {
	if ( is_admin() || ! tcres_google_consent_mode_is_enabled() ) return;

	wp_register_script(
		'tcres-google-consent-mode-default',
		false,
		array(),
		TCRES_PLUGIN_VERSION,
		false
	);
	wp_enqueue_script( 'tcres-google-consent-mode-default' );
	wp_add_inline_script(
		'tcres-google-consent-mode-default',
		tcres_google_consent_mode_get_default_script()
	);
	wp_print_scripts( 'tcres-google-consent-mode-default' );
}, 1 );


add_action( 'wp_enqueue_scripts', function(): void {
	if ( is_admin() || ! tcres_google_consent_mode_is_enabled() ) return;

	wp_register_script(
		'tcres-google-consent-mode-update',
		false,
		array(),
		TCRES_PLUGIN_VERSION,
		true
	);
	wp_enqueue_script( 'tcres-google-consent-mode-update' );
	wp_add_inline_script(
		'tcres-google-consent-mode-update',
		tcres_google_consent_mode_get_update_script()
	);
}, 1 );
