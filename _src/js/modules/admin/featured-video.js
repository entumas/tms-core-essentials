// --------------------------------------------------
//	Modules: Admin: Featured video
// --------------------------------------------------


import {
	tcresMediaBindField,
	tcresMediaOpenFrame,
} from '../wp/media-frame.js'


const tcresFeaturedVideoUpdateUi = ( root, hasVideo ) => {
	const preview  = root.querySelector( '[data-tcres-featured-video-preview]' ),
		remove     = root.querySelector( '[data-tcres-featured-video-remove]' ),
		removeWrap = root.querySelector( '.tcres-featured-video-remove' ),
		select     = root.querySelector( '[data-tcres-featured-video-select]' ),
		i18n       = window.tcresI18n

	if ( preview ) {
		if ( hasVideo ) preview.removeAttribute( 'hidden' )
		else preview.setAttribute( 'hidden', '' )
	}

	if ( removeWrap ) {
		if ( hasVideo ) removeWrap.removeAttribute( 'hidden' )
		else removeWrap.setAttribute( 'hidden', '' )
	} else if ( remove ) {
		if ( hasVideo ) remove.removeAttribute( 'hidden' )
		else remove.setAttribute( 'hidden', '' )
	}

	if ( select && i18n ) {
		select.textContent = hasVideo
			? ( i18n.replaceVideo || 'Replace video' )
			: ( i18n.selectVideo || 'Select video' )
	}
}


const tcresFeaturedVideoOpenFrame = ( root ) => {
	const input = root.querySelector( '[data-tcres-featured-video-input]' ),
		preview = root.querySelector( '[data-tcres-featured-video-preview]' )
	if ( ! input || ! preview ) return

	const i18n = window.tcresI18n
	tcresMediaOpenFrame( {
		title: i18n.selectFeaturedVideo || i18n.selectVideo,
		buttonText: i18n.useThisVideo,
		libraryType: 'video',
		onSelect: ( attachment ) => {
			if ( ! attachment.url ) return
			input.value = String( attachment.id )
			const typeAttr = attachment.mime ? ` type="${ attachment.mime }"` : ''
			preview.innerHTML = `<video class="tcres-featured-video-preview-player" muted preload="metadata" playsinline><source src="${ attachment.url }"${ typeAttr } /></video>`
			tcresFeaturedVideoUpdateUi( root, true )
		},
	} )
}


const tcresFeaturedVideoBind = ( root ) => {
	if ( root.dataset.tcresFeaturedVideoBound ) return
	root.dataset.tcresFeaturedVideoBound = '1'

	tcresMediaBindField( root, {
		selectSelector: '[data-tcres-featured-video-select]',
		removeSelector: '[data-tcres-featured-video-remove]',
		onSelectClick: () => tcresFeaturedVideoOpenFrame( root ),
		onRemoveClick: () => {
			const input = root.querySelector( '[data-tcres-featured-video-input]' )
			const preview = root.querySelector( '[data-tcres-featured-video-preview]' )
			if ( input ) input.value = '0'
			if ( preview ) preview.innerHTML = ''
			tcresFeaturedVideoUpdateUi( root, false )
		},
	} )
}


export const tcresFeaturedVideoInit = () => {
	document.querySelectorAll( '[data-tcres-featured-video]' ).forEach( ( root ) => {
		tcresFeaturedVideoBind( root )
	} )
}
