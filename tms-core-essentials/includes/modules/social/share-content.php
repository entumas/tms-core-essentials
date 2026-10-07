<?php
/**
 * Includes -> Modules -> Social -> Share content
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;


function tcres_share_content_is_enabled(): bool {
	return (bool) tcres_option_get( 'share_content', 'enable' );
}


/**
 * Default share title when the option is empty
 */
function tcres_share_content_default_title(): string {
	if ( function_exists( 'tcres_settings_social_share_content_default_title' ) ) :
		return tcres_settings_social_share_content_default_title();
	endif;

	return __( 'Share this', 'tms-core-essentials' );
}


/**
 * Whether a list item is selected under Show on.
 * Empty list = show on all (fresh defaults / no restriction).
 *
 * @param array<int, string> $selected
 */
function tcres_share_content_is_show_on_item( array $selected, string $item ): bool {
	return empty( $selected ) || in_array( $item, $selected, true );
}


/**
 * @return array<string, mixed>
 */
function tcres_share_content_get_group(): array {
	$settings = tcres_settings_get();
	$group    = isset( $settings['share_content'] ) && is_array( $settings['share_content'] )
		? $settings['share_content']
		: array();

	return $group;
}


/**
 * Whether at least one network is enabled
 */
function tcres_share_content_has_networks( array $group ): bool {
	foreach (
		array(
			'network_facebook',
			'network_x',
			'network_linkedin',
			'network_pinterest',
			'network_whatsapp',
			'network_telegram',
			'network_email',
			'network_copy_link',
		) as $key
	) :
		if ( ! empty( $group[ $key ] ) ) return true;
	endforeach;

	return false;
}


/**
 * Whether the current request may show share buttons
 */
function tcres_share_content_should_display(): bool {
	if ( ! tcres_share_content_is_enabled() ) return false;

	$group = tcres_share_content_get_group();
	if ( ! tcres_share_content_has_networks( $group ) ) return false;

	if ( is_404() ) return false;

	if ( is_search() ) :
		return ! empty( $group['show_on_search'] );
	endif;

	if ( is_front_page() ) :
		return ! empty( $group['show_on_front_page'] );
	endif;

	if ( is_home() ) :
		return ! empty( $group['show_on_home'] );
	endif;

	if ( is_singular() ) :
		$templates = function_exists( 'tcres_template_get_info' )
			? tcres_template_get_info()
			: array();
		$show_templates = isset( $group['show_on_templates'] ) && is_array( $group['show_on_templates'] )
			? $group['show_on_templates']
			: array();

		foreach ( $templates as $template ) :
			if ( ! is_array( $template ) ) continue;
			$file = isset( $template['file'] )
				? (string) $template['file']
				: '';
			$slug = isset( $template['slug'] )
				? (string) $template['slug']
				: '';
			if ( $file === '' || $slug === '' ) continue;
			if ( is_page_template( $file ) ) :
				return tcres_share_content_is_show_on_item( $show_templates, $slug );
			endif;
		endforeach;

		$post_type = get_post_type();
		if ( ! is_string( $post_type ) || $post_type === '' ) return false;

		$show_post_types = isset( $group['show_on_post_types'] ) && is_array( $group['show_on_post_types'] )
			? $group['show_on_post_types']
			: array();

		return tcres_share_content_is_show_on_item( $show_post_types, $post_type );
	endif;

	if ( is_author() ) :
		return ! empty( $group['show_on_author'] );
	endif;

	if ( is_date() ) :
		return ! empty( $group['show_on_date'] );
	endif;

	if ( is_post_type_archive() ) :
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) :
			$post_type = reset( $post_type );
		endif;
		if ( ! is_string( $post_type ) || $post_type === '' ) :
			$object = get_queried_object();
			$post_type = $object instanceof WP_Post_Type
				? $object->name
				: '';
		endif;
		if ( $post_type === '' ) return false;

		$show_archives = isset( $group['show_on_post_type_archives'] ) && is_array( $group['show_on_post_type_archives'] )
			? $group['show_on_post_type_archives']
			: array();

		return tcres_share_content_is_show_on_item( $show_archives, $post_type );
	endif;

	if ( is_category() || is_tag() || is_tax() ) :
		$object = get_queried_object();
		$taxonomy = $object instanceof WP_Term
			? (string) $object->taxonomy
			: '';
		if ( $taxonomy === '' ) return false;

		$show_taxonomies = isset( $group['show_on_taxonomies'] ) && is_array( $group['show_on_taxonomies'] )
			? $group['show_on_taxonomies']
			: array();

		return tcres_share_content_is_show_on_item( $show_taxonomies, $taxonomy );
	endif;

	return false;
}


/**
 * Context data for share URLs (title, permalink, excerpt, image)
 *
 * @return array{title: string, permalink: string, excerpt: string, image: string, blogname: string}
 */
function tcres_share_content_get_context(): array {
	$blogname = (string) get_bloginfo( 'name' );
	$title    = '';
	$permalink = '';
	$excerpt  = '';
	$image    = '';

	if ( is_home() && ! is_front_page() ) :
		$title     = $blogname;
		$permalink = (string) get_bloginfo( 'url' );
		$excerpt   = (string) get_bloginfo( 'description' );
	elseif ( is_front_page() && is_page() ) :
		$title     = get_the_title();
		$permalink = get_permalink() ?: home_url( '/' );
		$excerpt   = has_excerpt()
			? get_the_excerpt()
			: '';
		$image     = (string) ( get_the_post_thumbnail_url( null, 'full' ) ?: '' );
	elseif ( is_singular() ) :
		$title     = get_the_title();
		$permalink = get_permalink() ?: '';
		$excerpt   = has_excerpt()
			? get_the_excerpt()
			: '';
		$image     = (string) ( get_the_post_thumbnail_url( null, 'full' ) ?: '' );
	elseif ( is_archive() ) :
		$title = wp_strip_all_tags( get_the_archive_title() );
		global $wp;
		$request   = isset( $wp->request )
			? (string) $wp->request
			: '';
		$permalink = $request !== ''
			? home_url( user_trailingslashit( $request ) )
			: home_url( '/' );
		$excerpt   = wp_strip_all_tags( (string) get_the_archive_description() );
	elseif ( is_search() ) :
		$title     = sprintf(
			/* translators: %s: search query */
			__( 'Search results for “%s”', 'tms-core-essentials' ),
			get_search_query()
		);
		$permalink = get_search_link();
	else :
		$title     = $blogname;
		$permalink = home_url( '/' );
		$excerpt   = (string) get_bloginfo( 'description' );
	endif;

	return array(
		'title'     => $title,
		'permalink' => $permalink,
		'excerpt'   => $excerpt,
		'image'     => $image,
		'blogname'  => $blogname,
	);
}


/**
 * Build one share button
 *
 * @param array<string, mixed> $group
 * @param bool                 $show_share_prefix When false, the “Share this on” span stays screen-reader-only (e.g. Copy link).
 */
function tcres_share_content_get_button(
	array $group,
	string $network_class,
	string $icon_id,
	string $label,
	string $href,
	array $attrs = array(),
	bool $show_share_prefix = true
): string {
	$show_icon = ! empty( $group['button_show_icon'] );
	$show_text = $show_share_prefix && ! empty( $group['button_show_share_text'] );
	$show_name = ! empty( $group['button_show_name'] );

	$icon_html = '';
	if ( $show_icon ) :
		$icon_html = tcres_svg_icon_get(
			array(
				'icon' => $icon_id,
			)
		);
	endif;

	// Always print both text spans; hide visually with .screen-reader-text when disabled.
	$share_prefix = $show_share_prefix
		? __( 'Share this on', 'tms-core-essentials' )
		: '';
	$prefix_class = $show_text
		? 'tcres-share-content-button-share-text'
		: 'tcres-share-content-button-share-text screen-reader-text';
	$name_class = $show_name
		? 'tcres-share-content-button-name'
		: 'tcres-share-content-button-name screen-reader-text';

	$text_html  = '<span class="' . esc_attr( $prefix_class ) . '">' . esc_html( $share_prefix ) . '</span>';
	$text_html .= '<span class="' . esc_attr( $name_class ) . '">' . esc_html( $label ) . '</span>';

	$attr_html = '';
	foreach ( $attrs as $attr_key => $attr_value ) :
		if ( $attr_value === '' ) continue;
		$attr_html .= ' ' . esc_attr( (string) $attr_key ) . '="' . esc_attr( (string) $attr_value ) . '"';
	endforeach;

	$classes = trim( 'tcres-share-content-button ' . $network_class );

	return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $href ) . '"' . $attr_html . '>'
		. $icon_html
		. $text_html
		. '</a>';
}


/**
 * Build share buttons markup
 *
 * @param bool $respect_show_on When false (shortcode/manual), only checks enable + networks.
 */
function tcres_share_content_get( bool $respect_show_on = true ): string {
	if ( $respect_show_on ) :
		if ( ! tcres_share_content_should_display() ) :
			return '';
		endif;
	else :
		if ( ! tcres_share_content_is_enabled() ) :
			return '';
		endif;
		$group_check = tcres_share_content_get_group();
		if ( ! tcres_share_content_has_networks( $group_check ) ) :
			return '';
		endif;
	endif;

	$group = tcres_share_content_get_group();
	$ctx   = tcres_share_content_get_context();

	$title     = $ctx['title'];
	$permalink = $ctx['permalink'];
	$excerpt   = $ctx['excerpt'];
	$image     = $ctx['image'];
	$blogname  = $ctx['blogname'];

	if ( $permalink === '' ) return '';

	$title_enc     = rawurlencode( $title );
	$permalink_enc = rawurlencode( $permalink );
	$excerpt_enc   = rawurlencode( $excerpt );
	$image_enc     = rawurlencode( $image );

	$buttons = '';

	if ( ! empty( $group['network_facebook'] ) ) :
		$buttons .= tcres_share_content_get_button(
			$group,
			'facebook',
			'facebook',
			'Facebook',
			'https://www.facebook.com/sharer/sharer.php?u=' . $permalink_enc,
			array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			)
		);
	endif;

	if ( ! empty( $group['network_x'] ) ) :
		$buttons .= tcres_share_content_get_button(
			$group,
			'x',
			'x',
			'X',
			'https://x.com/intent/tweet?url=' . $permalink_enc . '&text=' . $title_enc,
			array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			)
		);
	endif;

	if ( ! empty( $group['network_linkedin'] ) ) :
		$buttons .= tcres_share_content_get_button(
			$group,
			'linkedin',
			'linkedin',
			'LinkedIn',
			'https://www.linkedin.com/shareArticle?mini=true&url=' . $permalink_enc . '&title=' . $title_enc . '&summary=' . $excerpt_enc,
			array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			)
		);
	endif;

	if ( ! empty( $group['network_pinterest'] ) ) :
		$buttons .= tcres_share_content_get_button(
			$group,
			'pinterest',
			'pinterest',
			'Pinterest',
			'https://pinterest.com/pin/create/button/?url=' . $permalink_enc . '&media=' . $image_enc . '&description=' . $title_enc,
			array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			)
		);
	endif;

	if ( ! empty( $group['network_whatsapp'] ) ) :
		$wa_text = $title . ' ' . __( 'in', 'tms-core-essentials' ) . ' ' . $blogname . ' – ' . $permalink;
		$buttons .= tcres_share_content_get_button(
			$group,
			'whatsapp',
			'whatsapp',
			'WhatsApp',
			'https://wa.me/?text=' . rawurlencode( $wa_text ),
			array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			)
		);
	endif;

	if ( ! empty( $group['network_telegram'] ) ) :
		$buttons .= tcres_share_content_get_button(
			$group,
			'telegram',
			'telegram',
			'Telegram',
			'https://t.me/share/url?url=' . $permalink_enc . '&text=' . $title_enc,
			array(
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
			)
		);
	endif;

	if ( ! empty( $group['network_email'] ) ) :
		$subject = $title !== ''
			? $title
			: $blogname;
		$body    = $title !== ''
			? $title . "\n\n" . $permalink
			: $permalink;
		$buttons .= tcres_share_content_get_button(
			$group,
			'mail',
			'mail',
			__( 'Mail', 'tms-core-essentials' ),
			'mailto:?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( $body )
		);
	endif;

	if ( ! empty( $group['network_copy_link'] ) ) :
		$buttons .= tcres_share_content_get_button(
			$group,
			'copy',
			'copy',
			__( 'Copy link', 'tms-core-essentials' ),
			$permalink,
			array(
				'data-tcres-share-copy'      => $permalink,
				'data-tcres-share-copy-done' => __( 'Copied', 'tms-core-essentials' ),
			),
			false
		);
	endif;

	if ( $buttons === '' ) return '';

	$buttons_class = 'tcres-share-content-buttons';
	if ( ! empty( $group['buttons_vertical'] ) ) :
		$buttons_class .= ' is-vertical';
	endif;

	$output = '<aside class="tcres-share-content">';

	if ( ! empty( $group['show_title'] ) ) :
		$share_title = isset( $group['title'] )
			? trim( (string) $group['title'] )
			: '';
		if ( $share_title === '' ) :
			$share_title = tcres_share_content_default_title();
		endif;
		$output .= '<p class="tcres-share-content-title">' . esc_html( $share_title ) . '</p>';
	endif;

	$output .= '<div class="' . esc_attr( $buttons_class ) . '">' . $buttons . '</div>';
	$output .= '</aside>';

	return $output;
}


function tcres_share_content(): void {
	tcres_echo_html( tcres_share_content_get() );
}


function tcres_share_content_shortcode(): string {
	return tcres_kses_html( tcres_share_content_get( false ) );
}


add_action( 'init', function(): void {
	add_shortcode( 'tcres-share-content', 'tcres_share_content_shortcode' );
} );
