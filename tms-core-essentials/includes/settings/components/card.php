<?php
/**
 * Includes -> Settings -> Components -> Card
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_settings_cards_panel_render_start( string $scope ): void {
	$scope = sanitize_key( $scope );
	?>
	<div class="tcres-settings-cards-panel" data-tcres-cards-scope="<?php echo esc_attr( $scope ); ?>">
	<?php
}


function tcres_settings_cards_panel_render_end(): void {
	?>
	</div>
	<?php
}


function tcres_settings_cards_count_active( array $items, array $group ): int {
	$active = 0;
	foreach ( $items as $item ) :
		if ( ! empty( $group[ $item['key'] ] ) ) $active++;
	endforeach;

	return $active;
}


function tcres_settings_cards_render_filter( string $tab, string $scope, string $current_filter, int $total, int $active_count, int $inactive_count ): void {
	$current_filter = in_array( $current_filter, tcres_settings_get_valid_card_filters(), true )
		? $current_filter
		: 'all';

	$url_all      = tcres_settings_get_tab_url_with_card_filter( $tab, $scope, 'all' );
	$url_active   = tcres_settings_get_tab_url_with_card_filter( $tab, $scope, 'active' );
	$url_inactive = tcres_settings_get_tab_url_with_card_filter( $tab, $scope, 'inactive' );
	$scope_class  = 'tcres-cards-' . sanitize_html_class( $scope );
	?>
	<ul class="subsubsub">
		<li class="all">
			<a href="<?php echo esc_url( $url_all ); ?>"
				class="<?php echo $current_filter === 'all' ? 'current' : ''; ?>"
				<?php echo $current_filter === 'all' ? ' aria-current="page"' : ''; ?>
				data-tcres-filter="all">
				<?php esc_html_e( 'All', 'tms-core-essentials' ); ?>
				<span class="count tcres-filter-count-all">(<?php echo (int) $total; ?>)</span>
			</a> |
		</li>
		<li class="<?php echo esc_attr( $scope_class ); ?>-active">
			<a href="<?php echo esc_url( $url_active ); ?>"
				class="<?php echo $current_filter === 'active' ? 'current' : ''; ?>"
				<?php echo $current_filter === 'active' ? ' aria-current="page"' : ''; ?>
				data-tcres-filter="active">
				<?php esc_html_e( 'Active', 'tms-core-essentials' ); ?>
				<span class="count tcres-filter-count-active">(<?php echo (int) $active_count; ?>)</span>
			</a> |
		</li>
		<li class="<?php echo esc_attr( $scope_class ); ?>-inactive">
			<a href="<?php echo esc_url( $url_inactive ); ?>"
				class="<?php echo $current_filter === 'inactive' ? 'current' : ''; ?>"
				<?php echo $current_filter === 'inactive' ? ' aria-current="page"' : ''; ?>
				data-tcres-filter="inactive">
				<?php esc_html_e( 'Inactive', 'tms-core-essentials' ); ?>
				<span class="count tcres-filter-count-inactive">(<?php echo (int) $inactive_count; ?>)</span>
			</a>
		</li>
	</ul>
	<br class="clear" />
	<?php
}


function tcres_settings_cards_render_grid_start( string $aria_label ): void {
	?>
	<div class="tcres-settings-grid" role="group" aria-label="<?php echo esc_attr( $aria_label ); ?>">
	<?php
}


function tcres_settings_cards_render_grid_end(): void {
	?>
	</div>
	<?php
}


function tcres_settings_card_render_field( string $input_id, string $name, bool $checked, string $title, string $description = '', $config_render = null ): void {
	/* translators: %s: Settings card title. */
	$enable_aria = sprintf( __( 'Enable %s', 'tms-core-essentials' ), $title );
	$has_config  = is_callable( $config_render );
	?>
	<div class="card tcres-settings-card is-filterable" data-tcres-active="<?php echo $checked ? '1' : '0'; ?>">
		<header>
			<h3 class="title"><?php echo esc_html( $title ); ?></h3>
			<?php
			tcres_settings_switch_render_field(
				$input_id,
				$name,
				$checked,
				array(
					'aria_label'       => $enable_aria,
					'toggle_target_id' => $has_config ? $input_id . '-config' : '',
				)
			);
			?>
		</header>
		<?php if ( $description !== '' ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php if ( $has_config ) : ?>
			<section id="<?php echo esc_attr( $input_id . '-config' ); ?>"
				<?php echo $checked ? '' : ' hidden'; ?>>
				<hr>
				<?php $config_render(); ?>
			</section>
		<?php endif; ?>
	</div>
	<?php
}
