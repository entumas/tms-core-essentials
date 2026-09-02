<?php
/**
 * Includes -> Settings -> Components -> Editor
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @return array<int, string>
 */
function tcres_settings_editor_minimal_buttons(): array {
	return array(
		'bold', 'italic', 'underline', 'strikethrough',
		'link', 'unlink',
		'undo', 'redo'
	);
}


/**
 * Minimal + lists (and cleanup helpers for pasted content).
 *
 * @return array<int, string>
 */
function tcres_settings_editor_basic_buttons(): array {
	return array(
		'bold', 'italic', 'underline', 'strikethrough',
		'bullist', 'numlist', 'blockquote',
		'link', 'unlink',
		'removeformat',
		'undo', 'redo',
	);
}


/**
 * @return array<string, string> Map of editor_id => toolbar preset (minimal|basic).
 */
function tcres_settings_editor_registry( ?string $editor_id = null, string $toolbar = 'minimal' ): array {
	static $map = array();

	if ( $editor_id !== null ) :
		$editor_id = sanitize_key( $editor_id );
		if ( $editor_id !== '' ) :
			$toolbar = sanitize_key( $toolbar );
			if ( ! in_array( $toolbar, array( 'minimal', 'basic' ), true ) ) :
				$toolbar = 'minimal';
			endif;
			$map[ $editor_id ] = $toolbar;
		endif;
	endif;

	return $map;
}


/**
 * @return array<string, string> Map of editor_id => placeholder text.
 */
function tcres_settings_editor_placeholders( ?string $editor_id = null, string $placeholder = '' ): array {
	static $map = array();

	if ( $editor_id !== null ) :
		$editor_id = sanitize_key( $editor_id );
		if ( $editor_id !== '' ) :
			$map[ $editor_id ] = $placeholder;
		endif;
	endif;

	return $map;
}


function tcres_settings_editor_register_id( string $editor_id, string $toolbar = 'minimal' ): void {
	tcres_settings_editor_registry( $editor_id, $toolbar );
}


/**
 * @return array<int, string>
 */
function tcres_settings_editor_buttons_for_toolbar( string $toolbar ): array {
	if ( 'basic' === $toolbar ) return tcres_settings_editor_basic_buttons();

	return tcres_settings_editor_minimal_buttons();
}


/**
 * Quicktags button string for a toolbar preset.
 */
function tcres_settings_editor_quicktags_for_toolbar( string $toolbar ): string {
	if ( 'basic' === $toolbar ) return 'strong,em,del,ul,ol,li,block,link,close';

	return 'strong,em,del,link,close';
}


/**
 * Tabs that need editor assets on the settings screen.
 *
 * @return array<int, string>
 */
function tcres_settings_editor_get_tabs(): array {
	$tabs = apply_filters( 'tcres_settings_editor_tabs', array() );
	if ( ! is_array( $tabs ) ) return array();

	return array_values(
		array_unique(
			array_filter(
				array_map( 'sanitize_key', $tabs )
			)
		)
	);
}


function tcres_settings_editor_current_tab_needs_assets(): bool {
	if ( ! function_exists( 'tcres_settings_get_current_tab' ) ) return false;
	return in_array( tcres_settings_get_current_tab(), tcres_settings_editor_get_tabs(), true );
}


/**
 * Enqueue TinyMCE / Quicktags for the settings screen when needed.
 *
 * @return array<int, string> Script dependency handles to append.
 */
function tcres_settings_editor_enqueue_assets(): array {
	if ( ! tcres_settings_editor_current_tab_needs_assets() ) return array();

	wp_enqueue_editor();
	if ( wp_style_is( 'editor-buttons', 'registered' ) ) :
		wp_enqueue_style( 'editor-buttons' );
	endif;

	return array( 'editor' );
}


/**
 * @param array<int, string> $buttons
 * @return array<int, string>
 */
add_filter( 'teeny_mce_buttons', function( array $buttons, string $editor_id ): array {
	$map = tcres_settings_editor_registry();
	if ( ! isset( $map[ $editor_id ] ) ) return $buttons;

	return tcres_settings_editor_buttons_for_toolbar( $map[ $editor_id ] );
}, 10, 2 );


/**
 * Add placeholder attribute to the settings-editor textarea (Text tab).
 */
add_filter( 'the_editor', function( string $output ): string {
	$placeholders = tcres_settings_editor_placeholders();
	if ( empty( $placeholders ) ) return $output;

	foreach ( $placeholders as $editor_id => $placeholder ) :
		if ( $placeholder === '' ) continue;
		if ( false === strpos( $output, 'id="' . $editor_id . '"' ) ) continue;

		$output = preg_replace(
			'/<textarea([^>]*\bid="' . preg_quote( $editor_id, '/' ) . '"[^>]*)>/',
			'<textarea$1 placeholder="' . esc_attr( $placeholder ) . '">',
			$output,
			1
		);
	endforeach;

	return is_string( $output )
		? $output
		: '';
} );


/**
 * Inject TinyMCE placeholder + CSS (Visual mode); WP does not style it by default.
 *
 * @param array<string, mixed> $mce_init
 * @return array<string, mixed>
 */
function tcres_settings_editor_before_init( array $mce_init, string $editor_id ): array {
	$map = tcres_settings_editor_placeholders();
	if ( empty( $map[ $editor_id ] ) ) return $mce_init;

	$mce_init['placeholder'] = $map[ $editor_id ];

	$style = 'body.mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before{content:attr(data-mce-placeholder);color:#646970;font-style:italic;line-height:1.5;position:absolute;left:8px;right:8px;top:6px;pointer-events:none;}body.mce-content-body{position:relative;}';

	if ( ! empty( $mce_init['content_style'] ) && is_string( $mce_init['content_style'] ) ) :
		$mce_init['content_style'] .= ' ' . $style;
	else :
		$mce_init['content_style'] = $style;
	endif;

	return $mce_init;
}
add_filter( 'teeny_mce_before_init', 'tcres_settings_editor_before_init', 10, 2 );
add_filter( 'tiny_mce_before_init', 'tcres_settings_editor_before_init', 10, 2 );


/**
 * Render a settings WYSIWYG.
 *
 * @param array{
 *   toolbar?: string,
 *   placeholder?: string,
 *   textarea_rows?: int,
 *   wpautop?: bool,
 *   quicktags?: bool|array<string, mixed>
 * } $args
 */
function tcres_settings_editor_render( string $editor_id, string $name, string $content = '', array $args = array() ): void {
	$editor_id = sanitize_key( $editor_id );
	if ( $editor_id === '' ) return;

	$toolbar = isset( $args['toolbar'] )
		? sanitize_key( (string) $args['toolbar'] )
		: 'minimal';
	if ( ! in_array( $toolbar, array( 'minimal', 'basic' ), true ) ) :
		$toolbar = 'minimal';
	endif;

	$placeholder = isset( $args['placeholder'] )
		? (string) $args['placeholder']
		: '';

	tcres_settings_editor_register_id( $editor_id, $toolbar );
	tcres_settings_editor_placeholders( $editor_id, $placeholder );

	$rows     = isset( $args['textarea_rows'] )
		? absint( $args['textarea_rows'] )
		: 8;
	$wpautop  = ! isset( $args['wpautop'] ) || ! empty( $args['wpautop'] );
	$buttons  = tcres_settings_editor_buttons_for_toolbar( $toolbar );
	$toolbar1 = implode( ',', $buttons );

	$quicktags = array(
		'buttons' => tcres_settings_editor_quicktags_for_toolbar( $toolbar ),
	);
	if ( isset( $args['quicktags'] ) ) :
		if ( $args['quicktags'] === false ) :
			$quicktags = false;
		elseif ( is_array( $args['quicktags'] ) ) :
			$quicktags = $args['quicktags'];
		endif;
	endif;

	$tinymce = array(
		'resize'   => true,
		'wpautop'  => $wpautop,
		'toolbar1' => $toolbar1,
		'toolbar2' => '',
	);
	if ( $placeholder !== '' ) $tinymce['placeholder'] = $placeholder;

	$is_empty = trim( wp_strip_all_tags( $content ) ) === '';
	$classes  = 'tcres-settings-editor';
	if ( $placeholder !== '' ) :
		$classes .= ' has-placeholder';
		if ( $is_empty ) :
			$classes .= ' is-empty';
		endif;
	endif;

	printf(
		'<div class="%s"%s>',
		esc_attr( $classes ),
		$placeholder !== ''
			? ' data-tcres-editor-placeholder="' . esc_attr( $placeholder ) . '"'
			: ''
	);

	if ( $placeholder !== '' ) :
		printf(
			'<div class="tcres-settings-editor-placeholder" aria-hidden="true">%s</div>',
			esc_html( $placeholder )
		);
	endif;

	wp_editor(
		$content,
		$editor_id,
		array(
			'textarea_name'    => $name,
			'textarea_rows'    => max( 3, $rows ),
			'media_buttons'    => false,
			'drag_drop_upload' => false,
			'teeny'            => true,
			'quicktags'        => $quicktags,
			'tinymce'          => $tinymce,
		)
	);
	echo '</div>';
}
