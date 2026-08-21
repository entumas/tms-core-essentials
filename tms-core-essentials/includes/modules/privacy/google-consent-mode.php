<?php
/**
 * Includes -> Modules -> Privacy -> Google Consent Mode
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_google_consent_mode_is_enabled(): bool {
	return (bool) tcres_option_get( 'google_consent_mode', 'enable' );
}


function tcres_google_consent_mode_render_default_script(): void {
	if ( is_admin() || ! tcres_google_consent_mode_is_enabled() ) return;
	?>
	<script>
		window.dataLayer = window.dataLayer || [];
		window.gtag = window.gtag || function() { dataLayer.push( arguments ); };
		window.gtag( 'consent', 'default', {
			ad_storage: 'denied',
			ad_user_data: 'denied',
			ad_personalization: 'denied',
			analytics_storage: 'denied'
		} );
	</script>
	<?php
}


function tcres_google_consent_mode_render_update_script(): void {
	if ( is_admin() || ! tcres_google_consent_mode_is_enabled() ) return;
	?>
	<script>
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
	</script>
	<?php
}


add_action( 'wp_head', 'tcres_google_consent_mode_render_default_script', 1 );
add_action( 'wp_footer', 'tcres_google_consent_mode_render_update_script' );
