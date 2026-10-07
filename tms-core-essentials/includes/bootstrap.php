<?php
/**
 * Includes -> Bootstrap
 * Plugin bootstrap loader
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'plugins_loaded', function (): void {

	// Admin chrome ========================================

	if ( is_admin() ) :
		tcres_include_file( 'admin/plugin-list.php' );
	endif;


	// API: discovery & utilities (no settings) ========================================

	tcres_include_file( 'api/included-post-types.php' );
	tcres_include_file( 'api/included-taxonomies.php' );
	tcres_include_file( 'api/get-templates-info.php' );
	tcres_include_file( 'api/get-registered-image-sizes.php' );
	tcres_include_file( 'api/format-date.php' );
	tcres_include_file( 'api/clean-wysiwyg-title.php' );
	tcres_include_file( 'api/validate-image-size.php' );
	tcres_include_file( 'api/convert-slug-to-id.php' );

	tcres_include_file( 'api/field-helpers.php' );
	tcres_include_file( 'api/get-field.php' );
	tcres_include_file( 'api/get-tax-field.php' );
	tcres_include_file( 'api/update-post-meta.php' );
	tcres_include_file( 'api/update-term-meta.php' );


	// Settings defaults helpers ========================================
	// option.php defaults call posts-list / terms-list getters.

	tcres_include_file( 'modules/admin/posts-list.php' );
	tcres_include_file( 'modules/admin/terms-list.php' );


	// Settings storage + option API ========================================

	tcres_include_file( 'settings/option.php' );
	tcres_include_file( 'api/get-option.php' );
	tcres_include_file( 'api/get-option-for-post-types.php' );
	tcres_include_file( 'api/get-option-for-post-types-diff.php' );
	tcres_include_file( 'api/get-option-for-taxonomies.php' );
	tcres_include_file( 'api/svg-icon.php' );
	tcres_include_file( 'api/output-location.php' );


	// Settings UI ========================================

	tcres_include_file( 'settings/components/switch.php' );
	tcres_include_file( 'settings/components/card.php' );
	tcres_include_file( 'settings/components/editor.php' );
	tcres_include_file( 'settings/components/output-location.php' );

	tcres_include_file( 'settings/content/security.php' );
	tcres_include_file( 'settings/content/admin.php' );
	tcres_include_file( 'settings/content/login.php' );
	tcres_include_file( 'settings/content/content.php' );
	tcres_include_file( 'settings/content/fields.php' );
	tcres_include_file( 'settings/content/privacy.php' );
	tcres_include_file( 'settings/content/social.php' );
	tcres_include_file( 'settings/content/extras.php' );
	tcres_include_file( 'settings/content/developers.php' );

	tcres_include_file( 'settings/navigation.php' );
	tcres_include_file( 'settings/page.php' );


	// Feature modules ========================================

	tcres_include_file( 'modules/security/security.php' );
	tcres_include_file( 'modules/security/performance.php' );

	tcres_include_file( 'modules/admin/disable-gutenberg.php' );
	tcres_include_file( 'modules/admin/disable-features.php' );
	tcres_include_file( 'modules/admin/duplicate-posts.php' );
	tcres_include_file( 'modules/admin/svg-uploads.php' );

	tcres_include_file( 'modules/login/login-customization.php' );
	tcres_include_file( 'modules/login/logout-redirect.php' );

	tcres_include_file( 'modules/fields/bodyclass.php' );
	tcres_include_file( 'modules/fields/subtitle.php' );
	tcres_include_file( 'modules/fields/hero.php' );
	tcres_include_file( 'modules/fields/featured-video.php' );
	tcres_include_file( 'modules/fields/term-image.php' );

	tcres_include_file( 'modules/content/breadcrumbs.php' );
	tcres_include_file( 'modules/content/sitemap.php' );
	tcres_include_file( 'modules/content/related-content.php' );
	tcres_include_file( 'modules/content/extend-search.php' );
	tcres_include_file( 'modules/content/scroll-to-top.php' );

	tcres_include_file( 'modules/privacy/privacy-consent.php' );
	tcres_include_file( 'modules/privacy/privacy-notice.php' );
	tcres_include_file( 'modules/privacy/google-consent-mode.php' );

	tcres_include_file( 'modules/social/social-menu.php' );
	tcres_include_file( 'modules/social/share-content.php' );
	tcres_include_file( 'modules/social/chats.php' );

	tcres_include_file( 'modules/extras/external-scripts.php' );
	tcres_include_file( 'modules/extras/smooth-scroll.php' );
	tcres_include_file( 'modules/extras/shortcodes.php' );


	// Sanitize + register settings (after modules) ========================================

	tcres_include_file( 'settings/sanitize.php' );
	tcres_include_file( 'settings/hooks.php' );


	// Integrations ========================================

	tcres_include_file( 'integrations/contact-form-7.php' );

}, 20 );
