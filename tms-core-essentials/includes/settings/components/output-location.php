<?php
/**
 * Includes -> Settings -> Components -> Output location
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param array{
 *   group_key: string,
 *   option_name: string,
 *   id_prefix: string,
 *   location?: string,
 *   target?: string,
 *   choices: array<string, string>,
 *   usage_render?: callable|null
 * } $args
 */
function tcres_settings_render_output_location( array $args ): void {
	$group_key    = isset( $args['group_key'] )
		? sanitize_key( (string) $args['group_key'] )
		: '';
	$option_name  = isset( $args['option_name'] )
		? (string) $args['option_name']
		: TCRES_OPTION_NAME;
	$id_prefix    = isset( $args['id_prefix'] )
		? (string) $args['id_prefix']
		: 'tcres-output';
	$choices      = isset( $args['choices'] ) && is_array( $args['choices'] ) ? $args['choices'] : array();
	$location     = isset( $args['location'] )
		? sanitize_key( (string) $args['location'] )
		: 'manual';
	$target       = isset( $args['target'] )
		? (string) $args['target']
		: '';
	$usage_render = isset( $args['usage_render'] ) && is_callable( $args['usage_render'] )
		? $args['usage_render']
		: null;

	if ( $group_key === '' || empty( $choices ) ) return;

	if ( ! isset( $choices[ $location ] ) ) :
		$choice_keys = array_keys( $choices );
		$location    = isset( $choices['manual'] )
			? 'manual'
			: (string) ( $choice_keys[0] ?? 'manual' );
	endif;

	$needs_target = function_exists( 'tcres_output_location_needs_target' )
		&& tcres_output_location_needs_target( $location );
	$is_manual    = $location === 'manual';
	$is_gtp       = $location === 'get_template_part';
	$is_custom    = $location === 'custom';

	$select_id = $id_prefix . '-output-location';
	$target_id = $id_prefix . '-output-target-options';
	$usage_id  = $id_prefix . '-output-usage';
	?>
	<hr>
	<div>
		<p class="inline">
			<label for="<?php echo esc_attr( $select_id ); ?>"><?php esc_html_e( 'Output location', 'tms-core-essentials' ); ?></label>
			<select
				name="<?php echo esc_attr( $option_name . '[' . $group_key . '][output_location]' ); ?>"
				id="<?php echo esc_attr( $select_id ); ?>"
				class="js-tcres-output-location-select"
				data-tcres-output-usage="<?php echo esc_attr( $usage_id ); ?>"
				data-tcres-output-target="<?php echo esc_attr( $target_id ); ?>">
				<?php foreach ( $choices as $value => $label ) : ?>
					<option value="<?php echo esc_attr( (string) $value ); ?>" <?php selected( $location, (string) $value ); ?>><?php echo esc_html( (string) $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<div
			id="<?php echo esc_attr( $target_id ); ?>"
			class="tcres-settings-suboptions"
			<?php echo $needs_target ? '' : ' hidden'; ?>>
			<p class="inline">
				<label for="<?php echo esc_attr( $id_prefix . '-output-target' ); ?>"><?php esc_html_e( 'Target', 'tms-core-essentials' ); ?></label>
				<input
					type="text"
					class="regular-text code js-tcres-output-location-target-input"
					name="<?php echo esc_attr( $option_name . '[' . $group_key . '][output_target]' ); ?>"
					id="<?php echo esc_attr( $id_prefix . '-output-target' ); ?>"
					value="<?php echo esc_attr( $target ); ?>"
					placeholder="<?php echo esc_attr( $is_custom ? 'my_theme_after_header' : 'content' ); ?>"
					data-tcres-placeholder-gtp="content"
					data-tcres-placeholder-custom="my_theme_after_header" />
			</p>
			<p class="description" data-tcres-output-help="get_template_part" <?php echo $is_gtp ? '' : ' hidden'; ?>>
				<?php esc_html_e( 'Template part slug. Hooks into get_template_part_{slug}.', 'tms-core-essentials' ); ?>
			</p>
			<p class="description" data-tcres-output-help="custom" <?php echo $is_custom ? '' : ' hidden'; ?>>
				<?php esc_html_e( 'Action hook name from your theme or plugin.', 'tms-core-essentials' ); ?>
			</p>
		</div>

		<?php if ( is_callable( $usage_render ) ) : ?>
			<div
				id="<?php echo esc_attr( $usage_id ); ?>"
				<?php echo $is_manual ? '' : ' hidden'; ?>>
				<?php $usage_render(); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
