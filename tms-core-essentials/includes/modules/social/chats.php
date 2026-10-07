<?php
/**
 * Includes -> Modules -> Social -> Chats
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_chats_is_enabled(): bool {
	return (bool) tcres_option_get( 'chats', 'enable' );
}


/**
 * @return array<string, mixed>
 */
function tcres_chats_get_group(): array {
	$settings = tcres_settings_get();
	$group    = isset( $settings['chats'] ) && is_array( $settings['chats'] )
		? $settings['chats']
		: array();

	return $group;
}


function tcres_chats_default_general_title(): string {
	if ( function_exists( 'tcres_settings_social_chats_default_general_title' ) ) :
		return tcres_settings_social_chats_default_general_title();
	endif;

	return __( 'Do you need help?', 'tms-core-essentials' );
}


function tcres_chats_default_suggested_message(): string {
	if ( function_exists( 'tcres_settings_social_chats_default_suggested_message' ) ) :
		return tcres_settings_social_chats_default_suggested_message();
	endif;

	return __( 'Hello, I need more information about your products/services.', 'tms-core-essentials' );
}


/**
 * Resolve title attribute text (empty string when nothing to show).
 *
 * @param array<string, mixed> $group
 */
function tcres_chats_get_title_text( array $group ): string {
	$title = isset( $group['general_title'] )
		? trim( (string) $group['general_title'] )
		: '';
	if ( $title === '' ) $title = tcres_chats_default_general_title();

	return $title;
}


/**
 * @param array<string, mixed> $group
 * @return array<int, array{id: string, href: string, label: string}>
 */
function tcres_chats_get_active_buttons( array $group ): array {
	$buttons = array();
	$default_message = tcres_chats_default_suggested_message();

	if ( ! empty( $group['whatsapp_enable'] ) ) :
		$number = isset( $group['whatsapp_number'] )
			? preg_replace( '/\D+/', '', (string) $group['whatsapp_number'] )
			: '';
		$number = is_string( $number )
			? $number
			: '';
		if ( $number !== '' ) :
			$message = isset( $group['whatsapp_message'] )
				? trim( (string) $group['whatsapp_message'] )
				: '';
			if ( $message === '' ) :
				$message = $default_message;
			endif;
			$href = 'https://wa.me/' . $number;
			if ( $message !== '' ) :
				$href .= '?text=' . rawurlencode( $message );
			endif;
			$buttons[] = array(
				'id'    => 'whatsapp',
				'href'  => $href,
				'label' => 'WhatsApp',
			);
		endif;
	endif;

	if ( ! empty( $group['telegram_enable'] ) ) :
		$username = isset( $group['telegram_username'] )
			? ltrim( trim( (string) $group['telegram_username'] ), '@' )
			: '';
		$username = preg_replace( '/[^A-Za-z0-9_]/', '', $username );
		$username = is_string( $username )
			? $username
			: '';
		if ( $username !== '' ) :
			$message = isset( $group['telegram_message'] )
				? trim( (string) $group['telegram_message'] )
				: '';
			if ( $message === '' ) :
				$message = $default_message;
			endif;
			$href = 'https://t.me/' . rawurlencode( $username );
			if ( $message !== '' ) :
				$href .= '?text=' . rawurlencode( $message );
			endif;
			$buttons[] = array(
				'id'    => 'telegram',
				'href'  => $href,
				'label' => 'Telegram',
			);
		endif;
	endif;

	if ( ! empty( $group['messenger_enable'] ) ) :
		$page_id = isset( $group['messenger_fbpageid'] )
			? trim( (string) $group['messenger_fbpageid'] )
			: '';
		$page_id = preg_replace( '/[^A-Za-z0-9._-]/', '', $page_id );
		$page_id = is_string( $page_id )
			? $page_id
			: '';
		if ( $page_id !== '' ) :
			$buttons[] = array(
				'id'    => 'messenger',
				'href'  => 'https://m.me/' . rawurlencode( $page_id ),
				'label' => 'Messenger',
			);
		endif;
	endif;

	return $buttons;
}


/**
 * Build one chat button markup
 */
function tcres_chats_get_button_html( string $id, string $href, string $label, string $extra_class, string $title ): string {
	$classes = trim( 'tcres-chat-button tcres-chat-button-' . $id . ' ' . $extra_class );
	$icon    = tcres_svg_icon_get(
		array(
			'icon' => $id,
		)
	);

	$title_attr = $title !== ''
		? ' title="' . esc_attr( $title ) . '"'
		: '';

	return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $href ) . '"' . $title_attr
		. ' target="_blank" rel="noopener noreferrer"'
		. ' aria-label="' . esc_attr( $label ) . '">'
		. $icon
		. '</a>';
}


/**
 * Whether chats should print in the footer
 */
function tcres_chats_should_display(): bool {
	if ( ! tcres_chats_is_enabled() ) return false;
	if ( is_admin() ) return false;

	$group = tcres_chats_get_group();
	return ! empty( tcres_chats_get_active_buttons( $group ) );
}


/**
 * Build chats markup for the footer
 */
function tcres_chats_get(): string {
	if ( ! tcres_chats_should_display() ) return '';

	$group   = tcres_chats_get_group();
	$buttons = tcres_chats_get_active_buttons( $group );
	if ( empty( $buttons ) ) return '';

	$title       = tcres_chats_get_title_text( $group );
	$is_multiple = count( $buttons ) > 1;
	$extra_class = $is_multiple
		? ''
		: 'tcres-chat';
	$buttons_html = '';

	foreach ( $buttons as $button ) :
		$buttons_html .= tcres_chats_get_button_html(
			$button['id'],
			$button['href'],
			$button['label'],
			$extra_class,
			$title
		);
	endforeach;
	if ( ! $is_multiple ) return $buttons_html;

	$trigger_icon = tcres_svg_icon_get(
		array(
			'icon' => 'comments',
		)
	);
	$title_attr = $title !== ''
		? ' title="' . esc_attr( $title ) . '"'
		: '';

	$output  = '<div id="tcres-chat" class="tcres-chat">';
	$output .= '<button type="button" class="tcres-chat-trigger"' . $title_attr
		. ' aria-expanded="false" aria-controls="tcres-chat-buttons"'
		. ' aria-label="' . esc_attr( $title !== '' ? $title : __( 'Open chat options', 'tms-core-essentials' ) ) . '">'
		. $trigger_icon
		. '</button>';
	$output .= '<div id="tcres-chat-buttons" class="tcres-chat-buttons">' . $buttons_html . '</div>';
	$output .= '</div>';

	return $output;
}


function tcres_chats_render(): void {
	tcres_echo_html( tcres_chats_get() );
}
