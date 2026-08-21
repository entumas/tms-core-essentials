<?php
/**
 * Includes -> Settings -> Content -> Login
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Logout redirect destination choices (labels keyed by value).
 *
 * @return array<string, string>
 */
function tcres_settings_logout_redirect_get_destination_choices(): array {
	$choices = array(
		'home'  => __( 'Home', 'tms-core-essentials' ),
		'login' => __( 'Login page', 'tms-core-essentials' ),
	);

	if ( class_exists( 'WooCommerce', false ) ) :
		$choices['wc_shop']      = __( 'Shop (WooCommerce)', 'tms-core-essentials' );
		$choices['wc_myaccount'] = __( 'My account (WooCommerce)', 'tms-core-essentials' );
		$choices['wc_cart']      = __( 'Cart (WooCommerce)', 'tms-core-essentials' );
	endif;

	$choices['custom'] = __( 'Custom URL', 'tms-core-essentials' );

	return $choices;
}


/**
 * @return array<int, string>
 */
function tcres_settings_logout_redirect_get_allowed_destinations(): array {
	return array_keys( tcres_settings_logout_redirect_get_destination_choices() );
}


function tcres_settings_login_render_logout_redirect_config( array $group, string $option_name ): void {
	$choices     = tcres_settings_logout_redirect_get_destination_choices();
	$destination = isset( $group['destination'] )
		? (string) $group['destination']
		: 'home';
	if ( ! isset( $choices[ $destination ] ) ) $destination = 'home';
	$custom_url = isset( $group['custom_url'] )
		? (string) $group['custom_url']
		: '';
	?>
	<p class="inline">
		<label for="tcres-logout-redirect-destination"><?php esc_html_e( 'Redirect to', 'tms-core-essentials' ); ?></label>
		<select
			name="<?php echo esc_attr( $option_name . '[logout_redirect][destination]' ); ?>"
			id="tcres-logout-redirect-destination"
			data-tcres-toggle-target="tcres-logout-redirect-custom-url-options"
			data-tcres-toggle-value="custom"
			aria-controls="tcres-logout-redirect-custom-url-options"
			aria-expanded="<?php echo $destination === 'custom' ? 'true' : 'false'; ?>">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $destination, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<div
		id="tcres-logout-redirect-custom-url-options"
		class="tcres-settings-suboptions"
		<?php echo $destination === 'custom' ? '' : ' hidden'; ?>>
		<p class="inline">
			<label for="tcres-logout-redirect-custom-url"><?php esc_html_e( 'Custom URL', 'tms-core-essentials' ); ?></label>
			<input
				type="url"
				class="regular-text"
				name="<?php echo esc_attr( $option_name . '[logout_redirect][custom_url]' ); ?>"
				id="tcres-logout-redirect-custom-url"
				value="<?php echo esc_attr( $custom_url ); ?>"
				placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>" />
		</p>
	</div>
	<?php
}


function tcres_settings_login_render_customization_panel(): void {
	$settings = tcres_settings_get();
	$option   = TCRES_OPTION_NAME;
	$group    = isset( $settings['login_customization'] ) && is_array( $settings['login_customization'] )
		? $settings['login_customization']
		: array();
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Custom login page', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-login-customization-enable',
						$option . '[login_customization][enable]',
						! empty( $group['enable'] ),
						array(
							'description' => __( 'Uses the Site Icon as the login logo and automatically points the logo link and title to the site home and site name.', 'tms-core-essentials' ),
						)
					);
					?>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_login_render_logout_redirect_panel(): void {
	$settings = tcres_settings_get();
	$option   = TCRES_OPTION_NAME;
	$logout   = isset( $settings['logout_redirect'] ) && is_array( $settings['logout_redirect'] )
		? $settings['logout_redirect']
		: array();
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Logout redirect', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-logout-redirect-enable',
						$option . '[logout_redirect][enable]',
						! empty( $logout['enable'] ),
						array(
							'description'      => __( 'After logout (admin or frontend), always redirect to a configured destination.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-logout-redirect-options',
						)
					);
					?>
					<div
						id="tcres-logout-redirect-options"
						<?php echo ! empty( $logout['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<?php tcres_settings_login_render_logout_redirect_config( $logout, $option ); ?>
						</div>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_login_render_tab_fields(): void {
	tcres_settings_login_render_customization_panel();
	echo '<hr>';
	tcres_settings_login_render_logout_redirect_panel();
}
