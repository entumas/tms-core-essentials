// --------------------------------------------------
//	Modules: Admin: Hero: Buttons
// --------------------------------------------------


const tcresHeroReindexButtons = ( list ) => {
	const rows = list.querySelectorAll( '[data-tcres-hero-button-row]' )
	rows.forEach( ( row, index ) => {
		row.querySelectorAll( 'input, select, textarea' ).forEach( ( input ) => {
			const name = input.getAttribute( 'name' )
			if ( ! name ) return
			input.setAttribute(
				'name',
				name.replace( /tcres_hero_buttons\[[^\]]+\]/, `tcres_hero_buttons[${ index }]` )
			)
		} )

		const urlInput = row.querySelector( '[data-tcres-hero-button-url]' )
		if ( urlInput ) urlInput.id = `tcres_hero_button_url_${ index }`
	} )
}


const tcresHeroGetWpLinkField = ( id ) => document.getElementById( id )


const tcresHeroApplyWpLinkToTarget = ( target ) => {
	if ( ! target ) return

	const attrs = typeof window.wpLink !== 'undefined' && window.wpLink.getAttrs
		? window.wpLink.getAttrs()
		: null

	const href = attrs && attrs.href
		? attrs.href
		: ( tcresHeroGetWpLinkField( 'wp-link-url' )?.value || '' ).trim()

	const text = ( tcresHeroGetWpLinkField( 'wp-link-text' )?.value || '' ).trim()

	const openInNewTab = attrs && Object.prototype.hasOwnProperty.call( attrs, 'target' )
		? attrs.target === '_blank'
		: !! tcresHeroGetWpLinkField( 'wp-link-target' )?.checked

	if ( href && target.url ) target.url.value = href
	if ( text && target.title ) target.title.value = text
	if ( target.target ) target.target.checked = openInNewTab
}


const tcresHeroPrefillWpLink = ( target ) => {
	const urlField  = tcresHeroGetWpLinkField( 'wp-link-url' ),
		textField   = tcresHeroGetWpLinkField( 'wp-link-text' ),
		targetField = tcresHeroGetWpLinkField( 'wp-link-target' )

	if ( urlField && target.url ) urlField.value = target.url.value || ''
	if ( textField && target.title ) textField.value = target.title.value || ''
	if ( targetField && target.target ) targetField.checked = !! target.target.checked
}


const tcresHeroBindLinkPicker = ( root ) => {
	root.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-tcres-hero-select-link]' )
		if ( ! button || ! root.contains( button ) ) return
		event.preventDefault()

		const row = button.closest( '[data-tcres-hero-button-row]' )
		if ( ! row ) return

		const urlInput  = row.querySelector( '[data-tcres-hero-button-url]' ),
			titleInput  = row.querySelector( '[data-tcres-hero-button-title]' ),
			targetInput = row.querySelector( '[data-tcres-hero-button-target]' )
		if ( ! urlInput || typeof window.wpLink === 'undefined' ) return

		window.tcresHeroLinkTarget = {
			url: urlInput,
			title: titleInput,
			target: targetInput,
		}

		window.wpLink.open( urlInput.id )
		tcresHeroPrefillWpLink( window.tcresHeroLinkTarget )
	} )

	if ( window.tcresHeroLinkPickerBound ) return
	window.tcresHeroLinkPickerBound = true

	document.addEventListener( 'click', ( event ) => {
		if ( ! event.target.closest( '#wp-link-submit' ) ) return
		if ( ! window.tcresHeroLinkTarget ) return

		const target = window.tcresHeroLinkTarget

		// Read dialog values before wpLink closes / clears them.
		tcresHeroApplyWpLinkToTarget( target )

		window.setTimeout( () => {
			tcresHeroApplyWpLinkToTarget( target )
			window.tcresHeroLinkTarget = null
		}, 0 )
	} )
}


export const tcresHeroInitButtons = () => {
	const roots = document.querySelectorAll( '[data-tcres-hero-buttons]' )
	if ( ! roots.length ) return

	roots.forEach( ( root ) => {
		const list    = root.querySelector( '[data-tcres-hero-buttons-list]' ),
			template  = root.querySelector( '[data-tcres-hero-button-template]' ),
			addButton = root.querySelector( '[data-tcres-hero-add-button]' )
		if ( ! list || ! template || ! addButton ) return

		addButton.addEventListener( 'click', ( event ) => {
			event.preventDefault()
			const index = list.querySelectorAll( '[data-tcres-hero-button-row]' ).length,
				html    = template.innerHTML.replace( /__INDEX__/g, String( index ) )
			list.insertAdjacentHTML( 'beforeend', html )
			tcresHeroReindexButtons( list )
		} )

		root.addEventListener( 'click', ( event ) => {
			const remove = event.target.closest( '[data-tcres-hero-remove-button]' )
			if ( ! remove || ! root.contains( remove ) ) return
			event.preventDefault()

			const row = remove.closest( '[data-tcres-hero-button-row]' )
			if ( ! row ) return
			row.remove()
			tcresHeroReindexButtons( list )
		} )

		tcresHeroBindLinkPicker( root )
	} )
}