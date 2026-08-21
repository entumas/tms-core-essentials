<?php
/**
 * Includes -> Settings -> Page
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * HTML output for the settings page
 */
function tcres_settings_render_submit_row( string $submit_button_id ): void {
	$submit_button_id = sanitize_key( $submit_button_id );
	if ( $submit_button_id === '' ) $submit_button_id = 'tcres-submit';
	?>
	<p class="submit">
		<?php
		submit_button(
			__( 'Save Changes', 'tms-core-essentials' ),
			'primary',
			'submit',
			false,
			array( 'id' => $submit_button_id )
		);
		?>
		<span class="spinner" aria-hidden="true"></span>
	</p>
	<?php
}


function tcres_settings_render_tab_panel_content( string $tab ): void {
	$renderers = array(
		'content'    => 'tcres_settings_content_render_tab_fields',
		'fields'     => 'tcres_settings_fields_render_tab_fields',
		'login'      => 'tcres_settings_login_render_tab_fields',
		'social'     => 'tcres_settings_social_render_tab_fields',
		'privacy'    => 'tcres_settings_privacy_render_tab_fields',
		'admin'      => 'tcres_settings_admin_render_tab_fields',
		'security'   => 'tcres_settings_security_render_tab_fields',
		'extras'     => 'tcres_settings_extras_render_tab_fields',
		'developers' => 'tcres_settings_developers_render_tab_fields',
	);

	if ( isset( $renderers[ $tab ] ) && is_callable( $renderers[ $tab ] ) ) :
		$renderers[ $tab ]();
	endif;
}


function tcres_settings_render_tab_panel( string $tab ): void {
	if ( ! tcres_settings_tab_has_save_form( $tab ) ) :
		tcres_settings_render_tab_panel_content( $tab );
		return;
	endif;

	$form_id = 'tcres-settings-' . $tab;
	?>
	<form id="<?php echo esc_attr( $form_id ); ?>" action="options.php" method="post" class="tcres-settings-form tcres-settings-panel">
		<?php settings_fields( TCRES_SETTINGS_GROUP ); ?>
		<input type="hidden" name="<?php echo esc_attr( TCRES_SETTINGS_SUBMIT_TAB_FIELD ); ?>" value="<?php echo esc_attr( $tab ); ?>" />
		<?php
		if ( tcres_multilingual_is_active() ) :
			$i18n_lang = tcres_multilingual_get_current_language();
			if ( is_string( $i18n_lang ) && $i18n_lang !== '' ) :
				?>
				<input type="hidden" name="tcres_settings_i18n_lang" value="<?php echo esc_attr( $i18n_lang ); ?>" />
				<?php
			endif;
		endif;
		?>
		<?php tcres_settings_render_tab_panel_content( $tab ); ?>
		<?php tcres_settings_render_submit_row( 'tcres-submit-' . $tab ); ?>
	</form>
	<?php
}


function tcres_settings_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$tab = tcres_settings_get_current_tab();
	?>
	<div class="wrap tcres-settings">
		<h1><?php echo esc_html( tcres_plugin_get_name() ); ?></h1>
		<h2 class="nav-tab-wrapper wp-clearfix" role="tablist">
			<?php foreach ( tcres_settings_get_valid_tabs() as $tab_key ) : ?>
				<a href="<?php echo esc_url( tcres_settings_get_tab_url( $tab_key ) ); ?>"
					class="nav-tab<?php echo $tab === $tab_key ? ' nav-tab-active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo $tab === $tab_key ? 'true' : 'false'; ?>">
					<?php echo esc_html( tcres_settings_get_tab_label( $tab_key ) ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<?php tcres_settings_render_tab_panel( $tab ); ?>
	</div>
	<?php
}
