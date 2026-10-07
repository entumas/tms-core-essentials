<?php
/**
 * Includes -> Settings -> Content -> Extras
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_extras_render_assets_panel(): void {
	$settings = tcres_settings_get();
	$assets   = isset( $settings['assets'] ) && is_array( $settings['assets'] )
		? $settings['assets']
		: array();
	$option   = TCRES_OPTION_NAME;
	?>
		<table class="form-table" role="presentation">
			<tr>
				<td colspan="2">
					<h2><?php esc_html_e( 'Frontend assets', 'tms-core-essentials' ); ?></h2>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-assets-disable-frontend-css',
						$option . '[assets][disable_frontend_css]',
						! empty( $assets['disable_frontend_css'] ),
						array(
							'label'       => __( 'Disable frontend CSS', 'tms-core-essentials' ),
							'description' => __( 'Do not load plugin styles on the frontend.', 'tms-core-essentials' ),
						)
					);
					?>
					<hr>
					<?php
					tcres_settings_switch_render_field(
						'tcres-assets-disable-frontend-js',
						$option . '[assets][disable_frontend_js]',
						! empty( $assets['disable_frontend_js'] ),
						array(
							'label'       => __( 'Disable frontend JS', 'tms-core-essentials' ),
							'description' => __( 'Do not load plugin scripts on the frontend.', 'tms-core-essentials' ),
						)
					);
					?>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_extras_render_external_scripts_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['external_scripts'] ) && is_array( $settings['external_scripts'] )
		? $settings['external_scripts']
		: array();
	$option   = TCRES_OPTION_NAME;
	$items    = array(
		array(
			'id'          => 'tcres-external-scripts-swiper',
			'key'         => 'swiper',
			'title'       => 'Swiper.js',
			'description' => __( 'Loads Swiper.js (CSS + JS) on the frontend. Use window.Swiper from your theme.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-external-scripts-glightbox',
			'key'         => 'glightbox',
			'title'       => 'GLightbox.js',
			'description' => __( 'Loads GLightbox.js (CSS + JS) on the frontend. Use window.GLightbox from your theme.', 'tms-core-essentials' ),
		),
		array(
			'id'          => 'tcres-external-scripts-choices',
			'key'         => 'choices',
			'title'       => 'Choices.js',
			'description' => __( 'Loads Choices.js (CSS + JS) on the frontend. Use window.Choices from your theme.', 'tms-core-essentials' ),
		),
	);
	?>
		<table class="form-table" role="presentation">
			<tr>
				<td colspan="2">
					<h2><?php esc_html_e( 'External scripts', 'tms-core-essentials' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Optional bundled third-party libraries. Enable only what your theme needs; the plugin does not initialize them.', 'tms-core-essentials' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<?php foreach ( $items as $index => $item ) : ?>
						<?php if ( $index > 0 ) : ?>
							<hr>
						<?php endif; ?>
						<?php
						tcres_settings_switch_render_field(
							$item['id'],
							$option . '[external_scripts][' . $item['key'] . ']',
							! empty( $group[ $item['key'] ] ),
							array(
								'label'       => $item['title'],
								'description' => $item['description'],
							)
						);
						?>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_extras_render_smooth_scroll_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['smooth_scroll'] ) && is_array( $settings['smooth_scroll'] )
		? $settings['smooth_scroll']
		: array();
	$option   = TCRES_OPTION_NAME;

	$lerp = isset( $group['lerp'] )
		? (float) $group['lerp']
		: 0.05;
	if ( $lerp <= 0 ) $lerp = 0.05;

	$exclude = isset( $group['exclude_selectors'] )
		? (string) $group['exclude_selectors']
		: '';
	if ( function_exists( 'tcres_smooth_scroll_normalize_exclude_selectors' ) ) :
		$exclude = tcres_smooth_scroll_normalize_exclude_selectors( $exclude );
	endif;
	?>
		<table class="form-table" role="presentation">
			<tr>
				<td colspan="2">
					<h2><?php esc_html_e( 'Smooth scroll', 'tms-core-essentials' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Enables smooth scrolling on the frontend.', 'tms-core-essentials' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-smooth-scroll-enable',
						$option . '[smooth_scroll][enable]',
						! empty( $group['enable'] ),
						array(
							'label'            => 'Lenis.js',
							'description'      => __( 'Loads Lenis.js (CSS + JS) on the frontend. Initializes smooth scrolling automatically.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-smooth-scroll-options',
						)
					);
					?>
					<div
						id="tcres-smooth-scroll-options"
						<?php echo ! empty( $group['enable'] ) ? '' : ' hidden'; ?>>
						<div>
							<h4><?php esc_html_e( 'Motion', 'tms-core-essentials' ); ?></h4>
							<p class="inline">
								<label for="tcres-smooth-scroll-lerp"><?php esc_html_e( 'Lerp', 'tms-core-essentials' ); ?></label>
								<input
									type="number"
									min="0.01"
									max="1"
									step="0.01"
									name="<?php echo esc_attr( $option . '[smooth_scroll][lerp]' ); ?>"
									id="tcres-smooth-scroll-lerp"
									value="<?php echo esc_attr( (string) $lerp ); ?>" />
								<span class="description"><?php esc_html_e( 'Lower = smoother / heavier (default 0.05).', 'tms-core-essentials' ); ?></span>
							</p>
							<ul class="checks">
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											name="<?php echo esc_attr( $option . '[smooth_scroll][smooth_wheel]' ); ?>"
											value="1"
											<?php checked( ! empty( $group['smooth_wheel'] ) ); ?> />
										<?php esc_html_e( 'Smooth mouse wheel', 'tms-core-essentials' ); ?>
									</label>
								</li>
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											name="<?php echo esc_attr( $option . '[smooth_scroll][sync_touch]' ); ?>"
											value="1"
											<?php checked( ! empty( $group['sync_touch'] ) ); ?> />
										<?php esc_html_e( 'Smooth touch scrolling', 'tms-core-essentials' ); ?>
									</label>
								</li>
								<li>
									<label class="has-checkbox">
										<input
											type="checkbox"
											name="<?php echo esc_attr( $option . '[smooth_scroll][anchors]' ); ?>"
											value="1"
											<?php checked( ! empty( $group['anchors'] ) ); ?> />
										<?php esc_html_e( 'Smooth in-page anchor links', 'tms-core-essentials' ); ?>
									</label>
								</li>
							</ul>
						</div>

						<hr>

						<div>
							<h4><?php esc_html_e( 'Exclusions', 'tms-core-essentials' ); ?></h4>
							<p class="inline">
								<label for="tcres-smooth-scroll-exclude-selectors"><?php esc_html_e( 'Exclude selectors', 'tms-core-essentials' ); ?></label>
							</p>
							<textarea
								class="regular-text code"
								name="<?php echo esc_attr( $option . '[smooth_scroll][exclude_selectors]' ); ?>"
								id="tcres-smooth-scroll-exclude-selectors"
								rows="4"
								placeholder=".swiper&#10;.my-scrollable"><?php echo esc_textarea( $exclude ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One CSS selector per line. Matching elements get data-lenis-prevent. Themes can also add that attribute in markup.', 'tms-core-essentials' ); ?></p>
						</div>
					</div>
				</td>
			</tr>
		</table>
	<?php
}


function tcres_settings_extras_render_svg_icons_panel(): void {
	$settings      = tcres_settings_get();
	$group         = isset( $settings['svg_icons'] ) && is_array( $settings['svg_icons'] )
		? $settings['svg_icons']
		: array();
	$option        = TCRES_OPTION_NAME;

	$frontend_file = isset( $group['frontend_file'] )
		? (string) $group['frontend_file']
		: '';
	$admin_file    = isset( $group['admin_file'] )
		? (string) $group['admin_file']
		: '';
	?>
		<table class="form-table" role="presentation">
			<tr>
				<td colspan="2">
					<h2><?php esc_html_e( 'SVG icons', 'tms-core-essentials' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Default sprite URL used by tcres_svg_icon_get() when no file argument is passed. Leave empty to use the plugin sprite (assets/images/icons.svg).', 'tms-core-essentials' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<p class="inline">
						<label for="tcres-svg-icons-admin-file"><?php esc_html_e( 'Admin', 'tms-core-essentials' ); ?></label>
						<input
							type="url"
							class="regular-text"
							name="<?php echo esc_attr( $option . '[svg_icons][admin_file]' ); ?>"
							id="tcres-svg-icons-admin-file"
							value="<?php echo esc_attr( $admin_file ); ?>"
							placeholder="<?php echo esc_attr( TCRES_PLUGIN_URL . 'assets/images/icons.svg' ); ?>" />
						<span class="description"><?php esc_html_e( 'Full URL to the .svg sprite used in the admin.', 'tms-core-essentials' ); ?></span>
					</p>

					<hr>

					<p class="inline">
						<label for="tcres-svg-icons-frontend-file"><?php esc_html_e( 'Frontend', 'tms-core-essentials' ); ?></label>
						<input
							type="url"
							class="regular-text"
							name="<?php echo esc_attr( $option . '[svg_icons][frontend_file]' ); ?>"
							id="tcres-svg-icons-frontend-file"
							value="<?php echo esc_attr( $frontend_file ); ?>"
							placeholder="<?php echo esc_attr( TCRES_PLUGIN_URL . 'assets/images/icons.svg' ); ?>" />
						<span class="description"><?php esc_html_e( 'Full URL to the .svg sprite used on the frontend.', 'tms-core-essentials' ); ?></span>
					</p>
				</td>
			</tr>
		</table>
	<?php
}


/**
 * Standalone shortcode groups for the Extras → Shortcodes panel (UI reference).
 *
 * @return array<int, array{heading: string, items: array<int, array{tag: string, example: string, description: string}>}>
 */
function tcres_settings_shortcodes_get_groups(): array {
	return array(
		array(
			'heading' => __( 'Data / API', 'tms-core-essentials' ),
			'items'   => array(
				array(
					'tag'         => 'tcres-field',
					'example'     => '[tcres-field field="field"]',
					'description' => __( 'Print a post custom field value. Optional format: esc_html (default), html, wpautop.', 'tms-core-essentials' ),
				),
				array(
					'tag'         => 'tcres-tax-field',
					'example'     => '[tcres-tax-field tax="category" field="field"]',
					'description' => __( 'Print a taxonomy term custom field value. Optional format: esc_html (default), html, wpautop.', 'tms-core-essentials' ),
				),
				array(
					'tag'         => 'tcres-option',
					'example'     => '[tcres-option name="breadcrumbs" value="home_label"]',
					'description' => __( 'Print a plugin settings value. Optional format: esc_html (default), html, wysiwyg_title, wpautop.', 'tms-core-essentials' ),
				),
			),
		),
		array(
			'heading' => __( 'Markup / UI', 'tms-core-essentials' ),
			'items'   => array(
				array(
					'tag'         => 'tcres-button',
					'example'     => '[tcres-button link="https://" label="Label"]',
					'description' => __( 'Render a link styled as a button. Optional: class, title, blank="yes".', 'tms-core-essentials' ),
				),
				array(
					'tag'         => 'tcres-highlighted',
					'example'     => '[tcres-highlighted]Text[/tcres-highlighted]',
					'description' => __( 'Wrap content in highlighted markup.', 'tms-core-essentials' ),
				),
				array(
					'tag'         => 'tcres-svgicon',
					'example'     => '[tcres-svgicon icon="facebook"]',
					'description' => __( 'Print an SVG icon from the sprite.', 'tms-core-essentials' ),
				),
			),
		),
		array(
			'heading' => __( 'Embeds', 'tms-core-essentials' ),
			'items'   => array(
				array(
					'tag'         => 'tcres-youtube',
					'example'     => '[tcres-youtube id="video_id"]',
					'description' => __( 'Embed a YouTube video by ID.', 'tms-core-essentials' ),
				),
				array(
					'tag'         => 'tcres-vimeo',
					'example'     => '[tcres-vimeo id="video_id"]',
					'description' => __( 'Embed a Vimeo video by ID.', 'tms-core-essentials' ),
				),
			),
		),
	);
}


function tcres_settings_extras_render_shortcodes_panel(): void {
	$settings = tcres_settings_get();
	$group    = isset( $settings['shortcodes'] ) && is_array( $settings['shortcodes'] )
		? $settings['shortcodes']
		: array();
	$option   = TCRES_OPTION_NAME;
	$enabled  = ! empty( $group['enable'] );
	?>
		<table class="form-table" role="presentation">
			<tr>
				<td colspan="2">
					<h2><?php esc_html_e( 'Shortcodes', 'tms-core-essentials' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Standalone shortcodes for fields, markup helpers, and media embeds. Shortcodes that belong to a module stay in that module.', 'tms-core-essentials' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"></th>
				<td>
					<?php
					tcres_settings_switch_render_field(
						'tcres-shortcodes-enable',
						$option . '[shortcodes][enable]',
						$enabled,
						array(
							'label'            => __( 'Enable shortcodes', 'tms-core-essentials' ),
							'description'      => __( 'Registers the standalone shortcodes listed below.', 'tms-core-essentials' ),
							'toggle_target_id' => 'tcres-shortcodes-options',
						)
					);
					?>
					<div
						id="tcres-shortcodes-options"
						<?php echo $enabled ? '' : ' hidden'; ?>>
						<?php foreach ( array_values( tcres_settings_shortcodes_get_groups() ) as $section_index => $section ) : ?>
							<?php if ( $section_index > 0 ) : ?>
								<hr>
							<?php endif; ?>
							<div>
								<h4><?php echo esc_html( $section['heading'] ); ?></h4>
								<ul>
									<?php foreach ( $section['items'] as $item ) : ?>
										<li>
											<code><?php echo esc_html( $item['example'] ); ?></code>
											&mdash; <?php echo esc_html( $item['description'] ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</td>
			</tr>
		</table>

	<?php
}


function tcres_settings_extras_render_tab_fields(): void {
	tcres_settings_extras_render_assets_panel();
	echo '<hr>';
	tcres_settings_extras_render_external_scripts_panel();
	echo '<hr>';
	tcres_settings_extras_render_smooth_scroll_panel();
	echo '<hr>';
	tcres_settings_extras_render_svg_icons_panel();
	echo '<hr>';
	tcres_settings_extras_render_shortcodes_panel();
}
