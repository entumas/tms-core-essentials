<?php
/**
 * Includes -> Settings -> Content -> Social
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_social_render_social_menu_usage(): void {
	$example = 'echo tcres_social_menu_get();';
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
		<p><?php esc_html_e( 'When enabled, the plugin registers the “Social menu” theme location. Assign a menu under Appearance → Menus, then print it from the theme. The shortcode has no attributes and always uses the panel settings:', 'tms-core-essentials' ); ?></p>
		<pre><code><?php echo esc_html( $example ); ?></code></pre>
		<p class="description">
			<?php esc_html_e( 'Each menu item title (lowercased) must match an icon id in the sprite (e.g. facebook, x, bluesky). Markup options are configured in this panel only.', 'tms-core-essentials' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Shortcode:', 'tms-core-essentials' ); ?>
			<code>[tcres-social-menu]</code>
		</p>
	</details>
	<?php
}


function tcres_settings_social_render_social_menu_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['social_menu'] ) && is_array( $settings['social_menu'] )
		? $settings['social_menu']
		: array();
	$option   = TCRES_OPTION_NAME;
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Social menu', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-social-menu-enable',
						$option . '[social_menu][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Registers the Social menu theme location so you can assign a menu under Appearance → Menus. Output it with tcres_social_menu_get() / [tcres-social-menu].', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-social-menu-options',
						)
					);
					?>
					<div
						id="tcres-social-menu-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<label class="has-checkbox">
								<input
									type="checkbox"
									name="<?php echo esc_attr( $option . '[social_menu][show_network_name]' ); ?>"
									value="1"
									<?php checked( ! empty( $group['show_network_name'] ) ); ?> />
								<?php esc_html_e( 'Show network name', 'tms-core-essentials' ); ?>
							</label>
						</div>
						<?php tcres_settings_social_render_social_menu_usage(); ?>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


/**
 * Default share title shown as placeholder when the option is empty
 */
function tcres_settings_social_share_content_default_title(): string {
	return __( 'Share this', 'tms-core-essentials' );
}


/**
 * @return array<string, string> network_key => label
 */
function tcres_settings_social_share_content_networks(): array {
	return array(
		'network_facebook'  => 'Facebook',
		'network_x'         => 'X',
		'network_linkedin'  => 'LinkedIn',
		'network_pinterest' => 'Pinterest',
		'network_whatsapp'  => 'WhatsApp',
		'network_telegram'  => 'Telegram',
		'network_email'     => __( 'Mail', 'tms-core-essentials' ),
		'network_copy_link' => __( 'Copy link', 'tms-core-essentials' ),
	);
}


/**
 * Post types with a public archive (for hide-on rules)
 *
 * @return array<int, string>
 */
function tcres_settings_social_share_content_get_archive_post_types(): array {
	$post_types = function_exists( 'tcres_settings_content_get_module_post_types' )
		? tcres_settings_content_get_module_post_types()
		: array();
	$out = array();

	foreach ( $post_types as $post_type ) :
		$object = get_post_type_object( $post_type );
		if ( ! $object || empty( $object->has_archive ) ) continue;
		$out[] = $post_type;
	endforeach;

	return $out;
}


function tcres_settings_social_share_content_is_show_on_item( array $selected, string $item ): bool {
	if ( function_exists( 'tcres_share_content_is_show_on_item' ) ) :
		return tcres_share_content_is_show_on_item( $selected, $item );
	endif;

	return empty( $selected ) || in_array( $item, $selected, true );
}


function tcres_settings_social_render_share_content_usage(): void {
	$example_echo = 'tcres_share_content();';
	$example_get  = 'echo tcres_share_content_get();';
	?>
	<details class="tcres-settings-usage">
		<summary><?php esc_html_e( 'Usage', 'tms-core-essentials' ); ?></summary>
		<p><?php esc_html_e( 'When enabled, print the share buttons from the theme. The shortcode has no attributes, uses panel settings, and ignores the “Show on” filters (placement is intentional):', 'tms-core-essentials' ); ?></p>
		<pre><code><?php echo esc_html( $example_echo ); ?></code></pre>
		<pre><code><?php echo esc_html( $example_get ); ?></code></pre>
		<p class="description">
			<?php esc_html_e( 'Nothing is output when the module is disabled or all networks are off. Automatic output also respects “Show on”.', 'tms-core-essentials' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Shortcode:', 'tms-core-essentials' ); ?>
			<code>[tcres-share-content]</code>
		</p>
	</details>
	<?php
}


function tcres_settings_social_render_share_content_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['share_content'] ) && is_array( $settings['share_content'] )
		? $settings['share_content']
		: array();
	$option   = TCRES_OPTION_NAME;
	$title    = isset( $group['title'] )
		? (string) $group['title']
		: '';
	$templates = function_exists( 'tcres_template_get_info' )
		? tcres_template_get_info()
		: array();
	$post_types = function_exists( 'tcres_settings_content_get_module_post_types' )
		? tcres_settings_content_get_module_post_types()
		: array();
	$archive_post_types = tcres_settings_social_share_content_get_archive_post_types();
	$taxonomies = function_exists( 'tcres_settings_content_get_module_taxonomies' )
		? tcres_settings_content_get_module_taxonomies()
		: array();
	$show_templates = isset( $group['show_on_templates'] ) && is_array( $group['show_on_templates'] )
		? $group['show_on_templates']
		: array();
	$show_post_type_archives = isset( $group['show_on_post_type_archives'] ) && is_array( $group['show_on_post_type_archives'] )
		? $group['show_on_post_type_archives']
		: array();
	$show_taxonomies = isset( $group['show_on_taxonomies'] ) && is_array( $group['show_on_taxonomies'] )
		? $group['show_on_taxonomies']
		: array();
	$show_post_types = isset( $group['show_on_post_types'] ) && is_array( $group['show_on_post_types'] )
		? $group['show_on_post_types']
		: array();
	?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><h2><?php esc_html_e( 'Share content', 'tms-core-essentials' ); ?></h2></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-share-content-enable',
						$option . '[share_content][enable]',
						! empty( $group['enable'] ),
						array(
							'description'      => __( 'Adds social share buttons for the current page or post. Choose an automatic output location or print with tcres_share_content() / tcres_share_content_get() / [tcres-share-content].', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-share-content-options',
						)
					);
					?>
					<div
						id="tcres-share-content-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h3 id="tcres-share-content-networks"><?php esc_html_e( 'Networks', 'tms-core-essentials' ); ?></h3>
							<fieldset aria-labelledby="tcres-share-content-networks">
								<ul class="checks is-horizontal">
									<?php foreach ( tcres_settings_social_share_content_networks() as $network_key => $network_label ) : ?>
										<?php $id = 'tcres-share-content-' . sanitize_html_class( $network_key ); ?>
										<li>
											<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
												<input
													type="checkbox"
													id="<?php echo esc_attr( $id ); ?>"
													name="<?php echo esc_attr( $option . '[share_content][' . $network_key . ']' ); ?>"
													value="1"
													<?php checked( ! empty( $group[ $network_key ] ) ); ?> />
												<?php echo esc_html( $network_label ); ?>
											</label>
										</li>
									<?php endforeach; ?>
								</ul>
							</fieldset>
						</div>

						<hr>

						<div>
							<div>
								<label class="has-checkbox">
									<input
										type="checkbox"
										id="tcres-share-content-show-title"
										name="<?php echo esc_attr( $option . '[share_content][show_title]' ); ?>"
										value="1"
										data-tcres-toggle-target="tcres-share-content-title-options"
										aria-controls="tcres-share-content-title-options"
										aria-expanded="<?php echo ! empty( $group['show_title'] ) ? 'true' : 'false'; ?>"
										<?php checked( ! empty( $group['show_title'] ) ); ?> />
									<?php esc_html_e( 'Show title', 'tms-core-essentials' ); ?>
								</label>
								<div
									id="tcres-share-content-title-options"
									class="tcres-settings-suboptions"
									<?php echo ! empty( $group['show_title'] ) ? '' : ' hidden'; ?>>
									<p class="inline">
										<label for="tcres-share-content-title"><?php esc_html_e( 'Share title', 'tms-core-essentials' ); ?></label>
										<input
											type="text"
											class="regular-text"
											name="<?php echo esc_attr( $option . '[share_content][title]' ); ?>"
											id="tcres-share-content-title"
											value="<?php echo esc_attr( $title ); ?>"
											placeholder="<?php echo esc_attr( tcres_settings_social_share_content_default_title() ); ?>" />
										<span class="description"><?php esc_html_e( 'Leave empty to use the default title.', 'tms-core-essentials' ); ?></span>
										<?php if ( tcres_multilingual_is_active() ) : ?>
											<span class="description"><?php esc_html_e( 'Translatable: saved for the current admin language.', 'tms-core-essentials' ); ?></span>
										<?php endif; ?>
									</p>
								</div>
							</div>
						</div>

						<hr>

						<div>
							<h3 id="tcres-share-content-button-config"><?php esc_html_e( 'Button configuration', 'tms-core-essentials' ); ?></h3>
							<fieldset aria-labelledby="tcres-share-content-button-config">
								<ul class="checks">
									<li>
										<label class="has-checkbox">
											<input
												type="checkbox"
												name="<?php echo esc_attr( $option . '[share_content][button_show_icon]' ); ?>"
												value="1"
												<?php checked( ! empty( $group['button_show_icon'] ) ); ?> />
											<?php esc_html_e( 'Show network icon', 'tms-core-essentials' ); ?>
										</label>
									</li>
									<li>
										<label class="has-checkbox">
											<input
												type="checkbox"
												name="<?php echo esc_attr( $option . '[share_content][button_show_share_text]' ); ?>"
												value="1"
												<?php checked( ! empty( $group['button_show_share_text'] ) ); ?> />
											<?php esc_html_e( 'Show “share this on” before the network name', 'tms-core-essentials' ); ?>
										</label>
									</li>
									<li>
										<label class="has-checkbox">
											<input
												type="checkbox"
												name="<?php echo esc_attr( $option . '[share_content][button_show_name]' ); ?>"
												value="1"
												<?php checked( ! empty( $group['button_show_name'] ) ); ?> />
											<?php esc_html_e( 'Show network name', 'tms-core-essentials' ); ?>
										</label>
									</li>
									<li>
										<label class="has-checkbox">
											<input
												type="checkbox"
												name="<?php echo esc_attr( $option . '[share_content][buttons_vertical]' ); ?>"
												value="1"
												<?php checked( ! empty( $group['buttons_vertical'] ) ); ?> />
											<?php esc_html_e( 'Stack buttons vertically', 'tms-core-essentials' ); ?>
										</label>
									</li>
								</ul>
							</fieldset>
						</div>

						<hr>

						<div>
							<h3><?php esc_html_e( 'Show on', 'tms-core-essentials' ); ?></h3>
							<div>
								<h4 id="tcres-share-content-show-general"><?php esc_html_e( 'General', 'tms-core-essentials' ); ?></h4>
								<fieldset aria-labelledby="tcres-share-content-show-general">
									<ul class="checks is-horizontal">
										<li>
											<label class="has-checkbox">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $option . '[share_content][show_on_front_page]' ); ?>"
													value="1"
													<?php checked( ! empty( $group['show_on_front_page'] ) ); ?> />
												<?php esc_html_e( 'Front page (is_front_page)', 'tms-core-essentials' ); ?>
											</label>
										</li>
										<li>
											<label class="has-checkbox">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $option . '[share_content][show_on_home]' ); ?>"
													value="1"
													<?php checked( ! empty( $group['show_on_home'] ) ); ?> />
												<?php esc_html_e( 'Home (is_home)', 'tms-core-essentials' ); ?>
											</label>
										</li>
										<li>
											<label class="has-checkbox">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $option . '[share_content][show_on_search]' ); ?>"
													value="1"
													<?php checked( ! empty( $group['show_on_search'] ) ); ?> />
												<?php esc_html_e( 'Search (is_search)', 'tms-core-essentials' ); ?>
											</label>
										</li>
									</ul>
								</fieldset>
							</div>

							<?php if ( ! empty( $templates ) ) : ?>
								<div>
									<h4 id="tcres-share-content-show-templates"><?php esc_html_e( 'Page templates', 'tms-core-essentials' ); ?></h4>
									<fieldset aria-labelledby="tcres-share-content-show-templates">
										<ul class="checks is-horizontal">
											<?php foreach ( $templates as $template ) : ?>
												<?php
												if ( ! is_array( $template ) ) continue;
												$slug = isset( $template['slug'] )
													? (string) $template['slug']
													: '';
												$name = isset( $template['name'] )
													? (string) $template['name']
													: $slug;
												$file = isset( $template['file'] )
													? (string) $template['file']
													: '';
												if ( $slug === '' ) continue;
												$id = 'tcres-share-content-show-template-' . sanitize_html_class( $slug );
												?>
												<li>
													<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
														<input
															type="checkbox"
															id="<?php echo esc_attr( $id ); ?>"
															name="<?php echo esc_attr( $option . '[share_content][show_on_templates][]' ); ?>"
															value="<?php echo esc_attr( $slug ); ?>"
															<?php checked( tcres_settings_social_share_content_is_show_on_item( $show_templates, $slug ) ); ?> />
														<?php
														echo esc_html(
															sprintf(
																/* translators: 1: template name, 2: template file */
																__( '%1$s (%2$s)', 'tms-core-essentials' ),
																$name,
																$file
															)
														);
														?>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
									</fieldset>
								</div>
							<?php endif; ?>

							<div>
								<h4 id="tcres-share-content-show-archives"><?php esc_html_e( 'Archives', 'tms-core-essentials' ); ?></h4>
								<fieldset aria-labelledby="tcres-share-content-show-archives">
									<ul class="checks is-horizontal">
										<li>
											<label class="has-checkbox">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $option . '[share_content][show_on_author]' ); ?>"
													value="1"
													<?php checked( ! empty( $group['show_on_author'] ) ); ?> />
												<?php esc_html_e( 'Author (is_author)', 'tms-core-essentials' ); ?>
											</label>
										</li>
										<li>
											<label class="has-checkbox">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $option . '[share_content][show_on_date]' ); ?>"
													value="1"
													<?php checked( ! empty( $group['show_on_date'] ) ); ?> />
												<?php esc_html_e( 'Date (is_date)', 'tms-core-essentials' ); ?>
											</label>
										</li>
									</ul>
								</fieldset>
							</div>

							<?php if ( ! empty( $archive_post_types ) ) : ?>
								<div>
									<h4 id="tcres-share-content-show-pta"><?php esc_html_e( 'Post type archives', 'tms-core-essentials' ); ?></h4>
									<fieldset aria-labelledby="tcres-share-content-show-pta">
										<ul class="checks is-horizontal">
											<?php foreach ( $archive_post_types as $post_type ) : ?>
												<?php
												$object = get_post_type_object( $post_type );
												$label  = $object && isset( $object->labels->archives ) && (string) $object->labels->archives !== ''
													? (string) $object->labels->archives
													: (
														$object && isset( $object->labels->name )
															? $object->labels->name
															: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) )
													);
												$id = 'tcres-share-content-show-pta-' . sanitize_html_class( $post_type );
												?>
												<li>
													<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
														<input
															type="checkbox"
															id="<?php echo esc_attr( $id ); ?>"
															name="<?php echo esc_attr( $option . '[share_content][show_on_post_type_archives][]' ); ?>"
															value="<?php echo esc_attr( $post_type ); ?>"
															<?php checked( tcres_settings_social_share_content_is_show_on_item( $show_post_type_archives, $post_type ) ); ?> />
														<?php echo esc_html( $label ); ?>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
									</fieldset>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $taxonomies ) ) : ?>
								<div>
									<h4 id="tcres-share-content-show-taxonomies"><?php esc_html_e( 'Taxonomies', 'tms-core-essentials' ); ?></h4>
									<fieldset aria-labelledby="tcres-share-content-show-taxonomies">
										<ul class="checks is-horizontal">
											<?php foreach ( $taxonomies as $taxonomy ) : ?>
												<?php
												$object = get_taxonomy( $taxonomy );
												$label  = $object && isset( $object->labels->name )
													? $object->labels->name
													: ucwords( str_replace( array( '-', '_' ), ' ', $taxonomy ) );
												$id = 'tcres-share-content-show-tax-' . sanitize_html_class( $taxonomy );
												?>
												<li>
													<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
														<input
															type="checkbox"
															id="<?php echo esc_attr( $id ); ?>"
															name="<?php echo esc_attr( $option . '[share_content][show_on_taxonomies][]' ); ?>"
															value="<?php echo esc_attr( $taxonomy ); ?>"
															<?php checked( tcres_settings_social_share_content_is_show_on_item( $show_taxonomies, $taxonomy ) ); ?> />
														<?php echo esc_html( $label ); ?>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
									</fieldset>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $post_types ) ) : ?>
								<div>
									<h4 id="tcres-share-content-show-singles"><?php esc_html_e( 'Singles', 'tms-core-essentials' ); ?></h4>
									<fieldset aria-labelledby="tcres-share-content-show-singles">
										<ul class="checks is-horizontal">
											<?php foreach ( $post_types as $post_type ) : ?>
												<?php
												$object = get_post_type_object( $post_type );
												$label  = $object && isset( $object->labels->singular_name )
													? $object->labels->singular_name
													: (
														$object && isset( $object->labels->name )
															? $object->labels->name
															: ucwords( str_replace( array( '-', '_' ), ' ', $post_type ) )
													);
												$id = 'tcres-share-content-show-post-type-' . sanitize_html_class( $post_type );
												?>
												<li>
													<label class="has-checkbox" for="<?php echo esc_attr( $id ); ?>">
														<input
															type="checkbox"
															id="<?php echo esc_attr( $id ); ?>"
															name="<?php echo esc_attr( $option . '[share_content][show_on_post_types][]' ); ?>"
															value="<?php echo esc_attr( $post_type ); ?>"
															<?php checked( tcres_settings_social_share_content_is_show_on_item( $show_post_types, $post_type ) ); ?> />
														<?php echo esc_html( $label ); ?>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
									</fieldset>
								</div>
							<?php endif; ?>
						</div>

						<?php
						tcres_settings_render_output_location(
							array(
								'group_key'    => 'share_content',
								'option_name'  => $option,
								'id_prefix'    => 'tcres-share-content',
								'location'     => isset( $group['output_location'] ) ? (string) $group['output_location'] : 'manual',
								'target'       => isset( $group['output_target'] ) ? (string) $group['output_target'] : '',
								'choices'      => tcres_output_location_choices_for( tcres_output_location_keys_share_content() ),
								'usage_render' => 'tcres_settings_social_render_share_content_usage',
							)
						);
						?>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


/**
 * Default chats link title shown as placeholder when the option is empty
 */
function tcres_settings_social_chats_default_general_title(): string {
	return __( '💬 Do you need help?', 'tms-core-essentials' );
}


/**
 * Default suggested chat message shown as placeholder when the option is empty
 */
function tcres_settings_social_chats_default_suggested_message(): string {
	return __( 'Hello, I need more information about your products/services.', 'tms-core-essentials' );
}


function tcres_settings_social_render_chats_panel(): void {
	$settings            = tcres_settings_get();
	$group               = isset( $settings['chats'] ) && is_array( $settings['chats'] )
		? $settings['chats']
		: array();
	$option              = TCRES_OPTION_NAME;
	$general_title       = isset( $group['general_title'] )
		? (string) $group['general_title']
		: '';
	$whatsapp_number     = isset( $group['whatsapp_number'] )
		? (string) $group['whatsapp_number']
		: '';
	$whatsapp_message    = isset( $group['whatsapp_message'] )
		? (string) $group['whatsapp_message']
		: '';
	$telegram_username   = isset( $group['telegram_username'] )
		? (string) $group['telegram_username']
		: '';
	$telegram_message    = isset( $group['telegram_message'] )
		? (string) $group['telegram_message']
		: '';
	$messenger_id        = isset( $group['messenger_fbpageid'] )
		? (string) $group['messenger_fbpageid']
		: '';
	$message_placeholder = tcres_settings_social_chats_default_suggested_message();
	$whatsapp_on         = ! empty( $group['whatsapp_enable'] );
	$telegram_on         = ! empty( $group['telegram_enable'] );
	$messenger_on        = ! empty( $group['messenger_enable'] );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><h2><?php esc_html_e( 'Chats', 'tms-core-essentials' ); ?></h2></th>
			<td>
				<?php
				tcres_settings_switch_render_field(
					'tcres-chats-enable',
					$option . '[chats][enable]',
					! empty( $group['enable'] ),
					array(
						'description'      => __( 'Adds floating WhatsApp, Telegram and/or Messenger buttons in the site footer.', 'tms-core-essentials' ),
						'toggle_target_id' => 'tcres-chats-options',
					)
				);
				?>
				<div
					id="tcres-chats-options"
					<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
					<div>
						<h3><?php esc_html_e( 'General', 'tms-core-essentials' ); ?></h3>
						<p class="inline">
							<label for="tcres-chats-general-title"><?php esc_html_e( 'Link attribute title', 'tms-core-essentials' ); ?></label>
							<input
								type="text"
								class="regular-text"
								name="<?php echo esc_attr( $option . '[chats][general_title]' ); ?>"
								id="tcres-chats-general-title"
								value="<?php echo esc_attr( $general_title ); ?>"
								placeholder="<?php echo esc_attr( tcres_settings_social_chats_default_general_title() ); ?>" />
							<span class="description"><?php esc_html_e( 'Tooltip / title attribute for the chat trigger and buttons. Leave empty to use the default.', 'tms-core-essentials' ); ?></span>
							<?php if ( tcres_multilingual_is_active() ) : ?>
								<span class="description"><?php esc_html_e( 'Translatable: saved for the current admin language.', 'tms-core-essentials' ); ?></span>
							<?php endif; ?>
						</p>
					</div>

					<hr>

					<div>
						<h3><?php esc_html_e( 'WhatsApp', 'tms-core-essentials' ); ?></h3>
						<div>
							<label class="has-checkbox">
								<input
									type="checkbox"
									id="tcres-chats-whatsapp-enable"
									name="<?php echo esc_attr( $option . '[chats][whatsapp_enable]' ); ?>"
									value="1"
									data-tcres-toggle-target="tcres-chats-whatsapp-options"
									aria-controls="tcres-chats-whatsapp-options"
									aria-expanded="<?php echo $whatsapp_on ? 'true' : 'false'; ?>"
									<?php checked( $whatsapp_on ); ?> />
								<?php esc_html_e( 'Enable WhatsApp', 'tms-core-essentials' ); ?>
							</label>
							<div
								id="tcres-chats-whatsapp-options"
								class="tcres-settings-suboptions"
								<?php echo $whatsapp_on ? '' : ' hidden'; ?>>
								<p class="inline">
									<label for="tcres-chats-whatsapp-number"><?php esc_html_e( 'Phone number', 'tms-core-essentials' ); ?></label>
									<input
										type="text"
										class="regular-text"
										name="<?php echo esc_attr( $option . '[chats][whatsapp_number]' ); ?>"
										id="tcres-chats-whatsapp-number"
										value="<?php echo esc_attr( $whatsapp_number ); ?>"
										placeholder="34600111222"
										autocomplete="tel" />
									<span class="description"><?php esc_html_e( 'Country code + number, digits only (no spaces or +).', 'tms-core-essentials' ); ?></span>
								</p>
								<p class="inline">
									<label for="tcres-chats-whatsapp-message"><?php esc_html_e( 'Suggested message', 'tms-core-essentials' ); ?></label>
									<input
										type="text"
										class="regular-text"
										name="<?php echo esc_attr( $option . '[chats][whatsapp_message]' ); ?>"
										id="tcres-chats-whatsapp-message"
										value="<?php echo esc_attr( $whatsapp_message ); ?>"
										placeholder="<?php echo esc_attr( $message_placeholder ); ?>" />
									<span class="description"><?php esc_html_e( 'Prefills the WhatsApp compose field. Leave empty to use the default.', 'tms-core-essentials' ); ?></span>
									<?php if ( tcres_multilingual_is_active() ) : ?>
										<span class="description"><?php esc_html_e( 'Translatable: saved for the current admin language.', 'tms-core-essentials' ); ?></span>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>

					<hr>

					<div>
						<h3><?php esc_html_e( 'Telegram', 'tms-core-essentials' ); ?></h3>
						<div>
							<label class="has-checkbox">
								<input
									type="checkbox"
									id="tcres-chats-telegram-enable"
									name="<?php echo esc_attr( $option . '[chats][telegram_enable]' ); ?>"
									value="1"
									data-tcres-toggle-target="tcres-chats-telegram-options"
									aria-controls="tcres-chats-telegram-options"
									aria-expanded="<?php echo $telegram_on ? 'true' : 'false'; ?>"
									<?php checked( $telegram_on ); ?> />
								<?php esc_html_e( 'Enable Telegram', 'tms-core-essentials' ); ?>
							</label>
							<div
								id="tcres-chats-telegram-options"
								class="tcres-settings-suboptions"
								<?php echo $telegram_on ? '' : ' hidden'; ?>>
								<p class="inline">
									<label for="tcres-chats-telegram-username"><?php esc_html_e( 'Username', 'tms-core-essentials' ); ?></label>
									<input
										type="text"
										class="regular-text"
										name="<?php echo esc_attr( $option . '[chats][telegram_username]' ); ?>"
										id="tcres-chats-telegram-username"
										value="<?php echo esc_attr( $telegram_username ); ?>"
										placeholder="yourusername" />
									<span class="description"><?php esc_html_e( 'Telegram username without @.', 'tms-core-essentials' ); ?></span>
								</p>
								<p class="inline">
									<label for="tcres-chats-telegram-message"><?php esc_html_e( 'Suggested message', 'tms-core-essentials' ); ?></label>
									<input
										type="text"
										class="regular-text"
										name="<?php echo esc_attr( $option . '[chats][telegram_message]' ); ?>"
										id="tcres-chats-telegram-message"
										value="<?php echo esc_attr( $telegram_message ); ?>"
										placeholder="<?php echo esc_attr( $message_placeholder ); ?>" />
									<span class="description"><?php esc_html_e( 'Prefills the Telegram compose field when supported. Leave empty to use the default.', 'tms-core-essentials' ); ?></span>
									<?php if ( tcres_multilingual_is_active() ) : ?>
										<span class="description"><?php esc_html_e( 'Translatable: saved for the current admin language.', 'tms-core-essentials' ); ?></span>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>

					<hr>

					<div>
						<h3><?php esc_html_e( 'Messenger', 'tms-core-essentials' ); ?></h3>
						<div>
							<label class="has-checkbox">
								<input
									type="checkbox"
									id="tcres-chats-messenger-enable"
									name="<?php echo esc_attr( $option . '[chats][messenger_enable]' ); ?>"
									value="1"
									data-tcres-toggle-target="tcres-chats-messenger-options"
									aria-controls="tcres-chats-messenger-options"
									aria-expanded="<?php echo $messenger_on ? 'true' : 'false'; ?>"
									<?php checked( $messenger_on ); ?> />
								<?php esc_html_e( 'Enable Messenger', 'tms-core-essentials' ); ?>
							</label>
							<div
								id="tcres-chats-messenger-options"
								class="tcres-settings-suboptions"
								<?php echo $messenger_on ? '' : ' hidden'; ?>>
								<p class="inline">
									<label for="tcres-chats-messenger-fbpageid"><?php esc_html_e( 'Facebook page ID', 'tms-core-essentials' ); ?></label>
									<input
										type="text"
										class="regular-text"
										name="<?php echo esc_attr( $option . '[chats][messenger_fbpageid]' ); ?>"
										id="tcres-chats-messenger-fbpageid"
										value="<?php echo esc_attr( $messenger_id ); ?>"
										placeholder="yourpage" />
									<span class="description"><?php esc_html_e( 'Page username or ID for m.me links.', 'tms-core-essentials' ); ?></span>
								</p>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>
	<?php
}


function tcres_settings_social_render_tab_fields(): void {
	tcres_settings_social_render_social_menu_panel();
	echo '<hr>';
	tcres_settings_social_render_share_content_panel();
	echo '<hr>';
	tcres_settings_social_render_chats_panel();
}
