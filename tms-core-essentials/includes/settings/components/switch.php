<?php
/**
 * Includes -> Settings -> Components -> Switch
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param array{
 *   label?: string,
 *   description?: string,
 *   aria_label?: string,
 *   toggle_target_id?: string,
 *   heading_tag?: string,
 *   text_first?: bool,
 *   is_title?: bool
 * } $args
 */
function tcres_settings_switch_render_field(
	string $input_id,
	string $name,
	bool $checked,
	array $args = array()
): void {
	$args = wp_parse_args(
		$args,
		array(
			'label'            => '',
			'description'      => '',
			'aria_label'       => '',
			'toggle_target_id' => '',
			'heading_tag'      => 'h3',
			'text_first'       => false,
			'is_title'         => false,
		)
	);

	$label            = (string) $args['label'];
	$description      = (string) $args['description'];
	$aria_label       = (string) $args['aria_label'];
	$toggle_target_id = (string) $args['toggle_target_id'];
	$heading_tag      = (string) $args['heading_tag'];
	$text_first       = ! empty( $args['text_first'] );
	$is_title         = ! empty( $args['is_title'] );

	if ( $aria_label === '' ) :
		$aria_label = $label !== ''
			? $label
			: ( $description !== '' ? $description : __( 'Toggle', 'tms-core-essentials' ) );
	endif;

	$heading_tag = strtolower( $heading_tag );
	if ( ! in_array( $heading_tag, array( 'h2', 'h3' ), true ) ) :
		$heading_tag = 'h3';
	endif;

	$toggle_attrs = '';
	if ( $toggle_target_id !== '' ) :
		$toggle_attrs  = ' data-tcres-toggle-target="' . esc_attr( $toggle_target_id ) . '"';
		$toggle_attrs .= ' aria-controls="' . esc_attr( $toggle_target_id ) . '"';
		$toggle_attrs .= ' aria-expanded="' . ( $checked ? 'true' : 'false' ) . '"';
	endif;

	$switch_class = 'tcres-settings-switch';
	if ( $text_first ) :
		$switch_class .= ' is-text-first';
	endif;
	if ( $is_title ) :
		$switch_class .= ' is-title';
	endif;
	?>
	<div class="<?php echo esc_attr( $switch_class ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0" />
		<input type="checkbox"
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			value="1"
			role="switch"
			aria-checked="<?php echo $checked ? 'true' : 'false'; ?>"
			aria-label="<?php echo esc_attr( $aria_label ); ?>"
			<?php echo $toggle_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_attr above. ?>
			<?php checked( $checked ); ?> />
		<?php if ( $label !== '' ) : ?>
			<<?php echo esc_html( $heading_tag ); ?> class="label">
				<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
			</<?php echo esc_html( $heading_tag ); ?>>
		<?php endif; ?>
	</div>
	<?php if ( $description !== '' ) : ?>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
	<?php
}
