<?php
/**
 * Includes -> Multilingual
 * WPML and Polylang compatibility for plugin options
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Whether Polylang or WPML is active with languages
 */
function tcres_multilingual_is_active(): bool {
	if ( defined( 'ICL_SITEPRESS_VERSION' ) || has_filter( 'wpml_current_language' ) ) return true;

	if ( function_exists( 'pll_languages_list' ) ) :
		$languages = pll_languages_list();
		return is_array( $languages ) && ! empty( $languages );
	endif;

	return false;
}


/**
 * Default content language slug, or null
 */
function tcres_multilingual_get_default_language(): ?string {
	if ( function_exists( 'pll_default_language' ) ) :
		$lang = pll_default_language( 'slug' );
		if ( is_string( $lang ) && $lang !== '' ) :
			return sanitize_key( $lang );
		endif;
	endif;

	if ( has_filter( 'wpml_default_language' ) ) :
		$lang = apply_filters( 'wpml_default_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML official hook.
		if ( is_string( $lang ) && $lang !== '' ) :
			return sanitize_key( $lang );
		endif;
	endif;

	return null;
}


/**
 * Current content language code, or null when monolingual / unresolved
 */
function tcres_multilingual_get_current_language(): ?string {
	if ( function_exists( 'pll_current_language' ) ) :
		$lang = pll_current_language( 'slug' );
		if ( is_string( $lang ) && $lang !== '' ) :
			return sanitize_key( $lang );
		endif;
	endif;

	if ( defined( 'ICL_LANGUAGE_CODE' ) ) :
		$code = (string) ICL_LANGUAGE_CODE;
		if ( $code === 'all' ) :
			return tcres_multilingual_get_default_language();
		endif;
		if ( $code !== '' ) :
			return sanitize_key( $code );
		endif;
	endif;

	if ( has_filter( 'wpml_current_language' ) ) :
		$lang = apply_filters( 'wpml_current_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML official hook.
		if ( is_string( $lang ) && $lang !== '' && $lang !== 'all' ) :
			return sanitize_key( $lang );
		endif;
	endif;

	return null;
}


/**
 * Language slug from settings form POST, falling back to current language
 */
function tcres_multilingual_get_language_from_request(): ?string {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce checked by WordPress Options API in settings save callback.
	if ( isset( $_POST['tcres_settings_i18n_lang'] ) ) :
		$lang = sanitize_key( wp_unslash( (string) $_POST['tcres_settings_i18n_lang'] ) );
		if ( $lang !== '' ) :
			return $lang;
		endif;
	endif;
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	return tcres_multilingual_get_current_language();
}


/**
 * Human-readable language name for admin notices
 */
function tcres_multilingual_get_lang_label( ?string $lang = null ): string {
	if ( $lang === null ) $lang = tcres_multilingual_get_current_language();
	if ( $lang === null || $lang === '' ) return '';

	if ( function_exists( 'PLL' ) ) :
		$pll = PLL();
		if ( is_object( $pll ) && isset( $pll->model ) && method_exists( $pll->model, 'get_language' ) ) :
			$language = $pll->model->get_language( $lang );
			if ( $language && is_object( $language ) && ! empty( $language->name ) ) :
				return (string) $language->name;
			endif;
		endif;
	endif;

	global $sitepress;
	if ( isset( $sitepress ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_language_details' ) ) :
		$details = $sitepress->get_language_details( $lang );
		if ( is_array( $details ) ) :
			$name = (string) ( $details['display_name'] ?? $details['native_name'] ?? '' );
			if ( $name !== '' ) return $name;
		endif;
	endif;

	$wpml_languages = apply_filters( 'wpml_active_languages', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML official hook.
	if ( is_array( $wpml_languages ) && isset( $wpml_languages[ $lang ] ) ) :
		$row = $wpml_languages[ $lang ];
		$name = (string) ( $row['native_name'] ?? $row['translated_name'] ?? '' );
		if ( $name !== '' ) return $name;
	endif;

	return $lang;
}


/**
 * Option name for language-specific string overrides
 */
function tcres_multilingual_get_lang_option_name( string $lang ): string {
	return TCRES_OPTION_NAME . '_' . sanitize_key( $lang );
}


/**
 * Merge language-specific string overrides into an existing lang option and save
 *
 * @param array<string, mixed> $overrides Nested fragment (e.g. breadcrumbs.home_label)
 */
function tcres_multilingual_save_string_overrides( string $lang, array $overrides ): void {
	$lang = sanitize_key( $lang );
	if ( $lang === '' || empty( $overrides ) ) return;

	$option   = tcres_multilingual_get_lang_option_name( $lang );
	$existing = tcres_storage_option_get( $option, array() );
	if ( ! is_array( $existing ) ) $existing = array();

	$merged = array_replace_recursive( $existing, $overrides );
	tcres_storage_option_update( $option, $merged );
}


/**
 * Translate a post ID to the current language (Polylang / WPML)
 */
function tcres_multilingual_translate_post_id( int $post_id ): int {
	if ( $post_id <= 0 ) return 0;

	if ( function_exists( 'pll_get_post' ) ) :
		$translated = pll_get_post( $post_id );
		if ( is_numeric( $translated ) && (int) $translated > 0 ) :
			return (int) $translated;
		endif;
	endif;

	if ( has_filter( 'wpml_object_id' ) ) :
		$post_type = get_post_type( $post_id );
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML official hook.
		$translated = apply_filters(
			'wpml_object_id',
			$post_id,
			is_string( $post_type ) && $post_type !== '' ? $post_type : 'post',
			true
		);
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		if ( is_numeric( $translated ) && (int) $translated > 0 ) :
			return (int) $translated;
		endif;
	endif;

	return $post_id;
}


/**
 * Translate a term ID to the current language (Polylang / WPML)
 */
function tcres_multilingual_translate_term_id( int $term_id, string $taxonomy ): int {
	if ( $term_id <= 0 || $taxonomy === '' ) return 0;

	if ( function_exists( 'pll_get_term' ) ) :
		$translated = pll_get_term( $term_id );
		if ( is_numeric( $translated ) && (int) $translated > 0 ) :
			return (int) $translated;
		endif;
	endif;

	if ( has_filter( 'wpml_object_id' ) ) :
		$translated = apply_filters( 'wpml_object_id', $term_id, $taxonomy, true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML official hook.
		if ( is_numeric( $translated ) && (int) $translated > 0 ) :
			return (int) $translated;
		endif;
	endif;

	return $term_id;
}


/**
 * Merge language-specific option overrides into the main settings array
 */
function tcres_multilingual_merge_settings( array $settings ): array {
	$lang = tcres_multilingual_get_current_language();
	if ( null === $lang ) return $settings;

	$translated = tcres_storage_option_get( tcres_multilingual_get_lang_option_name( $lang ), array() );
	if ( ! is_array( $translated ) || empty( $translated ) ) return $settings;

	// Keep only keys already present in $settings (defaults structure)
	return tcres_settings_merge_defaults( $settings, $translated );
}


/**
 * Admin notice: which language is being edited for translatable strings
 */
function tcres_settings_render_multilingual_strings_notice(): void {
	if ( ! tcres_multilingual_is_active() ) return;

	$lang_label = tcres_multilingual_get_lang_label();
	if ( $lang_label === '' ) return;
	?>
	<div class="notice notice-info inline" style="margin: 0 0 1em;">
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: language name */
					__( 'You are currently editing the %s version of translatable text fields. Switch the admin language to edit another language.', 'tms-core-essentials' ),
					'<strong>' . esc_html( $lang_label ) . '</strong>'
				),
				array( 'strong' => array() )
			);
			?>
		</p>
	</div>
	<?php
}
