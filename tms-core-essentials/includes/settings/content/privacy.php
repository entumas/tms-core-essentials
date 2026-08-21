<?php
/**
 * Includes -> Settings -> Content -> Privacy
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @return array<int, string>
 */
function tcres_settings_privacy_notice_keys(): array {
	return array( 'contact', 'subscribe', 'comments', 'register', 'checkout' );
}


/**
 * Contact Form 7 active?
 */
function tcres_settings_privacy_is_cf7_active(): bool {
	if ( ! function_exists( 'is_plugin_active' ) ) :
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	endif;

	return function_exists( 'wpcf7_add_form_tag' )
		|| defined( 'WPCF7_VERSION' )
		|| class_exists( 'WPCF7' )
		|| is_plugin_active( 'contact-form-7/wp-contact-form-7.php' )
		|| (
			is_multisite()
			&& function_exists( 'is_plugin_active_for_network' )
			&& is_plugin_active_for_network( 'contact-form-7/wp-contact-form-7.php' )
		);
}


/**
 * @return array<int, WP_Post>
 */
function tcres_settings_privacy_get_wpforms_forms(): array {
	if ( ! post_type_exists( 'wpforms' ) ) return array();

	$forms = get_posts(
		array(
			'post_type'      => 'wpforms',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	return is_array( $forms ) ? $forms : array();
}


/**
 * @param array<int, WP_Post> $forms
 * @param array<int, int>     $selected_ids
 */
function tcres_settings_privacy_render_wpforms_checkboxes(
	string $name,
	string $id_prefix,
	array $forms,
	array $selected_ids
): void {
	if ( empty( $forms ) ) :
		?>
		<p class="description"><?php esc_html_e( 'No WPForms forms were found.', 'tms-core-essentials' ); ?></p>
		<?php
		return;
	endif;
	?>
	<h4><?php esc_html_e( 'WPForms forms', 'tms-core-essentials' ); ?></h4>
	<ul class="checks" id="<?php echo esc_attr( $id_prefix ); ?>">
		<?php foreach ( $forms as $form ) : ?>
			<?php
			if ( ! $form instanceof WP_Post ) continue;
			$form_id  = (int) $form->ID;
			$input_id = $id_prefix . '-' . $form_id;
			$label    = $form->post_title !== ''
				? $form->post_title
				: '#' . $form_id;
			?>
			<li>
				<label class="has-checkbox" for="<?php echo esc_attr( $input_id ); ?>">
				<input
					type="checkbox"
					id="<?php echo esc_attr( $input_id ); ?>"
					name="<?php echo esc_attr( $name ); ?>[]"
					value="<?php echo esc_attr( (string) $form_id ); ?>"
					<?php checked( in_array( $form_id, $selected_ids, true ) ); ?> />
				<?php echo esc_html( $label ); ?>
			</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}


function tcres_settings_privacy_render_cf7_shortcode_hint( string $notice ): void {
	if ( ! tcres_settings_privacy_is_cf7_active() ) return;

	$shortcode = '[tcres-privacy-notice notice="' . $notice . '"]';
	?>
	<p class="description">
		<?php esc_html_e( 'Contact Form 7 shortcode:', 'tms-core-essentials' ); ?>
		<code><?php echo esc_html( $shortcode ); ?></code>
	</p>
	<?php
}


add_filter(
	'tcres_settings_editor_tabs',
	static function ( array $tabs ): array {
		$tabs[] = 'privacy';
		return $tabs;
	}
);


function tcres_settings_privacy_render_privacy_notice_usage(): void {
	$example = "echo tcres_privacy_notice_get( array(\n\t'notice' => 'contact',\n) );";
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
			<p><?php esc_html_e( 'Print a privacy notice on the frontend. Settings from this panel are the defaults; function args override them:', 'tms-core-essentials' ); ?></p>
			<pre><code><?php echo esc_html( $example ); ?></code></pre>
			<ul>
				<li>
					<code>notice</code>
					&mdash; <?php esc_html_e( 'Notice key: contact, subscribe, comments, register, or checkout.', 'tms-core-essentials' ); ?>
				</li>
			</ul>
			<p class="description">
				<?php esc_html_e( 'Shortcode:', 'tms-core-essentials' ); ?>
				<code>[tcres-privacy-notice notice="contact"]</code>
			</p>
	</details>
	<?php
}


function tcres_settings_privacy_render_privacy_notice_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['privacy_notice'] ) && is_array( $settings['privacy_notice'] )
		? $settings['privacy_notice']
		: array();
	$option   = TCRES_OPTION_NAME;

	$wpforms_forms         = tcres_settings_privacy_get_wpforms_forms();
	$wpforms_contact_ids   = isset( $group['wpforms_contact_ids'] ) && is_array( $group['wpforms_contact_ids'] )
		? array_map( 'absint', $group['wpforms_contact_ids'] )
		: array();
	$wpforms_subscribe_ids = isset( $group['wpforms_subscribe_ids'] ) && is_array( $group['wpforms_subscribe_ids'] )
		? array_map( 'absint', $group['wpforms_subscribe_ids'] )
		: array();
	$has_wpforms           = ! empty( $wpforms_forms ) || post_type_exists( 'wpforms' );
	$trigger               = isset( $group['trigger'] )
		? (string) $group['trigger']
		: '';
	$trigger_placeholder   = function_exists( 'tcres_privacy_notice_default_trigger' )
		? tcres_privacy_notice_default_trigger()
		: __( 'Before submitting the form, take a look at the basic information on data protection here.', 'tms-core-essentials' );
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Privacy notice', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-privacy-notice-enable',
						$option . '[privacy_notice][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds collapsible privacy notices for forms, registration, and checkout.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-privacy-notice-options',
						)
					);
					?>
					<div
						id="tcres-privacy-notice-options"
						data-tcres-editors
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h3><?php esc_html_e( 'Trigger text', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_notice_trigger',
								$option . '[privacy_notice][trigger]',
								$trigger,
								array(
									'toolbar'       => 'minimal',
									'textarea_rows' => 3,
									'placeholder'   => $trigger_placeholder,
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Text shown before expanding the notice. Leave empty to use the default.', 'tms-core-essentials' ); ?></p>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Contact notice', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_notice_contact',
								$option . '[privacy_notice][contact]',
								isset( $group['contact'] ) ? (string) $group['contact'] : '',
								array(
									'toolbar' => 'basic',
								)
							);
							tcres_settings_privacy_render_cf7_shortcode_hint( 'contact' );
							?>
							<?php if ( $has_wpforms ) : ?>
								<?php
								tcres_settings_privacy_render_wpforms_checkboxes(
									$option . '[privacy_notice][wpforms_contact_ids]',
									'tcres-privacy-notice-wpforms-contact',
									$wpforms_forms,
									$wpforms_contact_ids
								);
								?>
							<?php endif; ?>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Subscribe notice', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_notice_subscribe',
								$option . '[privacy_notice][subscribe]',
								isset( $group['subscribe'] ) ? (string) $group['subscribe'] : '',
								array(
									'toolbar' => 'basic',
								)
							);
							tcres_settings_privacy_render_cf7_shortcode_hint( 'subscribe' );
							?>
							<?php if ( $has_wpforms ) : ?>
								<?php
								tcres_settings_privacy_render_wpforms_checkboxes(
									$option . '[privacy_notice][wpforms_subscribe_ids]',
									'tcres-privacy-notice-wpforms-subscribe',
									$wpforms_forms,
									$wpforms_subscribe_ids
								);
								?>
							<?php endif; ?>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Comments notice', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_notice_comments',
								$option . '[privacy_notice][comments]',
								isset( $group['comments'] ) ? (string) $group['comments'] : '',
								array(
									'toolbar' => 'basic',
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Automatic output on the comments form when this notice has content.', 'tms-core-essentials' ); ?></p>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Register notice', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_notice_register',
								$option . '[privacy_notice][register]',
								isset( $group['register'] ) ? (string) $group['register'] : '',
								array(
									'toolbar' => 'basic',
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Automatic output on the WordPress registration form and the WooCommerce registration form when this notice has content.', 'tms-core-essentials' ); ?></p>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Checkout notice', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_notice_checkout',
								$option . '[privacy_notice][checkout]',
								isset( $group['checkout'] ) ? (string) $group['checkout'] : '',
								array(
									'toolbar' => 'basic',
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'Automatic output on the WooCommerce checkout (classic shortcode and Checkout block) when this notice has content.', 'tms-core-essentials' ); ?></p>
						</div>

						<?php tcres_settings_privacy_render_privacy_notice_usage(); ?>
					</div>
				</td>
			</tr>
		</table>

	<?php
}


/**
 * Privacy consent checkbox panel.
 */
function tcres_settings_privacy_render_privacy_consent_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['privacy_consent'] ) && is_array( $settings['privacy_consent'] )
		? $settings['privacy_consent']
		: array();
	$option   = TCRES_OPTION_NAME;
	$label    = isset( $group['label'] ) ? (string) $group['label'] : '';
	$placeholder = function_exists( 'tcres_privacy_consent_default_label_placeholder' )
		? tcres_privacy_consent_default_label_placeholder()
		: __( 'I accept the [privacy_policy].', 'tms-core-essentials' );
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Privacy consent', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-privacy-consent-enable',
						$option . '[privacy_consent][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds a required privacy policy checkbox on selected forms.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-privacy-consent-options',
						)
					);
					?>
					<div
						id="tcres-privacy-consent-options"
						data-tcres-editors
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h3 id="tcres-privacy-consent-show-on"><?php esc_html_e( 'Show on', 'tms-core-essentials' ); ?></h3>
							<fieldset aria-labelledby="tcres-privacy-consent-show-on">
								<ul class="checks">
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											name="<?php echo esc_attr( $option . '[privacy_consent][comments]' ); ?>"
											value="1"
											<?php checked( ! empty( $group['comments'] ) ); ?> />
										<?php esc_html_e( 'Comments', 'tms-core-essentials' ); ?>
									</label>
								</li>
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											name="<?php echo esc_attr( $option . '[privacy_consent][register]' ); ?>"
											value="1"
											<?php checked( ! empty( $group['register'] ) ); ?> />
										<?php esc_html_e( 'Register (WordPress and WooCommerce)', 'tms-core-essentials' ); ?>
									</label>
								</li>
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											name="<?php echo esc_attr( $option . '[privacy_consent][checkout]' ); ?>"
											value="1"
											<?php checked( ! empty( $group['checkout'] ) ); ?> />
										<?php esc_html_e( 'Checkout (WooCommerce)', 'tms-core-essentials' ); ?>
									</label>
								</li>
							</ul>
							</fieldset>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Checkbox label', 'tms-core-essentials' ); ?></h3>
							<?php
							tcres_settings_editor_render(
								'tcres_privacy_consent_label',
								$option . '[privacy_consent][label]',
								$label,
								array(
									'toolbar'       => 'minimal',
									'textarea_rows' => 3,
									'placeholder'   => $placeholder,
								)
							);
							?>
							<p class="description">
								<?php esc_html_e( 'Leave empty to use the default. Use the token [privacy_policy] to insert a link to the WordPress privacy policy page.', 'tms-core-essentials' ); ?>
							</p>
						</div>
					</div>
				</td>
			</tr>
		</table>

	<?php
}


function tcres_settings_privacy_render_google_consent_mode_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['google_consent_mode'] ) && is_array( $settings['google_consent_mode'] )
		? $settings['google_consent_mode']
		: array();
	$option   = TCRES_OPTION_NAME;
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Google Consent Mode v2', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-google-consent-mode-enable',
						$option . '[google_consent_mode][enable]',
						! empty( $group['enable'] ),
						array(
							'description' => __( 'Compatible only with the GDPR Cookie Compliance plugin. Sets all Google consent signals to denied by default and updates them when the user accepts statistics or marketing cookies.', 'tms-core-essentials' ),
						)
					);
					?>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_privacy_render_tab_fields(): void {
	tcres_settings_render_multilingual_strings_notice();
	tcres_settings_privacy_render_privacy_consent_panel();
	echo '<hr>';
	tcres_settings_privacy_render_google_consent_mode_panel();
	echo '<hr>';
	tcres_settings_privacy_render_privacy_notice_panel();
}
