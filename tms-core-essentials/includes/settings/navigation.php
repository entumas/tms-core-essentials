<?php
/**
 * Includes -> Settings -> Navigation
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_get_valid_tabs(): array {
	return array(
		'content',
		'fields',
		'social',
		'privacy',
		'admin',
		'login',
		'security',
		'extras',
		'developers',
	);
}


function tcres_settings_get_default_tab(): string {
	return 'content';
}


function tcres_settings_get_tab_label( string $tab ): string {
	$labels = array(
		'content'    => __( 'Content', 'tms-core-essentials' ),
		'fields'     => __( 'Fields', 'tms-core-essentials' ),
		'social'     => __( 'Social and contact', 'tms-core-essentials' ),
		'privacy'    => __( 'Privacy and forms', 'tms-core-essentials' ),
		'admin'      => __( 'Administration', 'tms-core-essentials' ),
		'login'      => __( 'Login', 'tms-core-essentials' ),
		'security'   => __( 'Security and performance', 'tms-core-essentials' ),
		'extras'     => __( 'Extras', 'tms-core-essentials' ),
		'developers' => __( 'Developers', 'tms-core-essentials' ),
	);

	return $labels[ $tab ] ?? $tab;
}


function tcres_settings_tab_has_save_form( string $tab ): bool {
	return $tab !== 'developers';
}


function tcres_settings_get_submit_tab_from_request(): string {
	if ( ! isset( $_POST['_wpnonce'] ) ) return '';

	$nonce = sanitize_text_field( wp_unslash( (string) $_POST['_wpnonce'] ) );
	if ( ! wp_verify_nonce( $nonce, TCRES_SETTINGS_GROUP . '-options' ) ) return '';

	if ( ! isset( $_POST[ TCRES_SETTINGS_SUBMIT_TAB_FIELD ] ) ) return '';

	$tab = sanitize_key( wp_unslash( (string) $_POST[ TCRES_SETTINGS_SUBMIT_TAB_FIELD ] ) );
	return in_array( $tab, tcres_settings_get_valid_tabs(), true )
		? $tab
		: '';
}


function tcres_settings_get_admin_url_base(): string {
	return add_query_arg(
		array(
			'page' => TCRES_SETTINGS_PAGE_SLUG,
		),
		admin_url( 'options-general.php' )
	);
}


function tcres_settings_get_tab_nonce_action( string $tab ): string {
	return 'tcres_settings_tab|' . sanitize_key( $tab );
}


function tcres_settings_get_tab_url( string $tab ): string {
	$tab = sanitize_key( $tab );
	if ( ! in_array( $tab, tcres_settings_get_valid_tabs(), true ) ) $tab = tcres_settings_get_default_tab();

	$url = add_query_arg(
		TCRES_SETTINGS_TAB_QUERY_VAR,
		$tab,
		tcres_settings_get_admin_url_base()
	);

	return wp_nonce_url( $url, tcres_settings_get_tab_nonce_action( $tab ) );
}


function tcres_settings_get_current_tab(): string {
	$default = tcres_settings_get_default_tab();

	if ( ! is_admin() ) return $default;
	if ( ! current_user_can( 'manage_options' ) ) return $default;

	$tab_var = TCRES_SETTINGS_TAB_QUERY_VAR;
	if ( ! isset( $_GET['_wpnonce'], $_GET[ $tab_var ] ) ) return $default;

	$nonce = sanitize_text_field( wp_unslash( (string) $_GET['_wpnonce'] ) );
	$raw   = sanitize_key( wp_unslash( (string) $_GET[ $tab_var ] ) );
	$tabs  = tcres_settings_get_valid_tabs();

	if ( ! in_array( $raw, $tabs, true ) ) return $default;

	if ( ! wp_verify_nonce( $nonce, tcres_settings_get_tab_nonce_action( $raw ) ) ) return $default;

	return $raw;
}


function tcres_settings_get_tab_card_scope_map(): array {
	return array(
		'security' => array( 'security', 'performance' ),
		'admin'    => array( 'disable_gutenberg', 'disable_features', 'posts_list', 'terms_list', 'utilities' ),
		'fields'   => array( 'fields' ),
	);
}


function tcres_settings_get_tab_card_scopes( string $tab ): array {
	$map = tcres_settings_get_tab_card_scope_map();

	return $map[ $tab ] ?? array();
}


function tcres_settings_get_scope_tab( string $scope ): string {
	foreach ( tcres_settings_get_tab_card_scope_map() as $tab => $scopes ) :
		if ( in_array( $scope, $scopes, true ) ) return $tab;
	endforeach;

	return '';
}


function tcres_settings_get_valid_card_scopes(): array {
	$scopes = array();

	foreach ( tcres_settings_get_tab_card_scope_map() as $tab_scopes ) :
		$scopes = array_merge( $scopes, $tab_scopes );
	endforeach;

	return $scopes;
}


function tcres_settings_get_valid_card_filters(): array {
	return array( 'all', 'active', 'inactive' );
}


function tcres_settings_get_cards_filter_query_var( string $scope ): string {
	return 'tcres_' . sanitize_key( $scope ) . '_cards';
}


function tcres_settings_get_current_card_filter( string $scope ): string {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) return 'all';
	if ( ! in_array( $scope, tcres_settings_get_valid_card_scopes(), true ) ) return 'all';

	$scope_tab = tcres_settings_get_scope_tab( $scope );
	if ( $scope_tab === '' || tcres_settings_get_current_tab() !== $scope_tab ) return 'all';

	$tab_var = TCRES_SETTINGS_TAB_QUERY_VAR;
	if ( ! isset( $_GET['_wpnonce'], $_GET[ $tab_var ] ) ) return 'all';

	$raw_tab = sanitize_key( wp_unslash( (string) $_GET[ $tab_var ] ) );
	$nonce   = sanitize_text_field( wp_unslash( (string) $_GET['_wpnonce'] ) );
	if ( $raw_tab !== $scope_tab ) return 'all';
	if ( ! wp_verify_nonce( $nonce, tcres_settings_get_tab_nonce_action( $raw_tab ) ) ) return 'all';

	$var = tcres_settings_get_cards_filter_query_var( $scope );
	if ( ! isset( $_GET[ $var ] ) ) return 'all';

	$filter = sanitize_key( wp_unslash( (string) $_GET[ $var ] ) );
	return in_array( $filter, tcres_settings_get_valid_card_filters(), true )
		? $filter
		: 'all';
}


function tcres_settings_get_tab_url_with_card_filter( string $tab, string $scope, string $filter ): string {
	$tab    = sanitize_key( $tab );
	$scope  = sanitize_key( $scope );
	$filter = sanitize_key( $filter );

	if ( ! in_array( $tab, tcres_settings_get_valid_tabs(), true ) ) :
		$tab = tcres_settings_get_default_tab();
	endif;
	if ( ! in_array( $scope, tcres_settings_get_tab_card_scopes( $tab ), true ) ) :
		$scope = tcres_settings_get_tab_card_scopes( $tab )[0] ?? 'security';
	endif;
	if ( ! in_array( $filter, tcres_settings_get_valid_card_filters(), true ) ) :
		$filter = 'all';
	endif;

	$url = tcres_settings_get_tab_url( $tab );

	foreach ( tcres_settings_get_tab_card_scopes( $tab ) as $card_scope ) :
		$var = tcres_settings_get_cards_filter_query_var( $card_scope );

		if ( $card_scope === $scope ) :
			if ( $filter === 'all' ) :
				$url = remove_query_arg( $var, $url );
			else :
				$url = add_query_arg( $var, $filter, $url );
			endif;
			continue;
		endif;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Reading filter state for URL construction, not form processing.
		if ( ! isset( $_GET[ $var ] ) ) continue;

		$existing = sanitize_key( wp_unslash( (string) $_GET[ $var ] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		if ( ! in_array( $existing, tcres_settings_get_valid_card_filters(), true ) || $existing === 'all' ) :
			$url = remove_query_arg( $var, $url );
		else :
			$url = add_query_arg( $var, $existing, $url );
		endif;
	endforeach;

	return $url;
}
