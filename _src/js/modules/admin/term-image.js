// --------------------------------------------------
//	Modules: Admin: Term image
// --------------------------------------------------


import {
	tcresMediaBindField,
	tcresMediaOpenFrame,
} from '../wp/media-frame.js'


const tcresTermImageUpdateUi = ( root, hasImage ) => {
	const preview  = root.querySelector( '[data-tcres-term-image-preview]' ),
		remove     = root.querySelector( '[data-tcres-term-image-remove]' ),
		removeWrap = root.querySelector( '.tcres-term-image-remove' ),
		select     = root.querySelector( '[data-tcres-term-image-select]' ),
		i18n       = window.tcresI18n

	if ( preview ) {
		if ( hasImage ) preview.removeAttribute( 'hidden' )
		else preview.setAttribute( 'hidden', '' )
	}

	if ( removeWrap ) {
		if ( hasImage ) removeWrap.removeAttribute( 'hidden' )
		else removeWrap.setAttribute( 'hidden', '' )
	} else if ( remove ) {
		if ( hasImage ) remove.removeAttribute( 'hidden' )
		else remove.setAttribute( 'hidden', '' )
	}

	if ( select && i18n ) {
		select.textContent = hasImage
			? i18n.replaceImage
			: i18n.selectImage
	}
}


const tcresTermImageOpenFrame = ( root ) => {
	const input = root.querySelector( '[data-tcres-term-image-input]' ),
		preview = root.querySelector( '[data-tcres-term-image-preview]' )
	if ( ! input || ! preview ) return

	const i18n = window.tcresI18n
	tcresMediaOpenFrame( {
		title: i18n.selectFeaturedImage,
		buttonText: i18n.useThisImage,
		libraryType: 'image',
		onSelect: ( attachment ) => {
			input.value = String( attachment.id )
			const url = ( attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url )
				|| attachment.url
				|| ''
			preview.innerHTML = url
				? `<img src="${ url }" alt="" />`
				: ''
			tcresTermImageUpdateUi( root, Boolean( url ) )
		},
	} )
}


const tcresTermImageBind = ( root ) => {
	tcresMediaBindField( root, {
		selectSelector: '[data-tcres-term-image-select]',
		removeSelector: '[data-tcres-term-image-remove]',
		onSelectClick: () => tcresTermImageOpenFrame( root ),
		onRemoveClick: () => {
			const input = root.querySelector( '[data-tcres-term-image-input]' )
			const preview = root.querySelector( '[data-tcres-term-image-preview]' )
			if ( input ) input.value = '0'
			if ( preview ) preview.innerHTML = ''
			tcresTermImageUpdateUi( root, false )
		},
	} )
}


export const tcresTermImageInit = () => {
	document.querySelectorAll( '[data-tcres-term-image]' ).forEach( ( root ) => {
		tcresTermImageBind( root )
	} )
}