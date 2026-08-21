// --------------------------------------------------
//	Modules: WP: Media frame
// --------------------------------------------------


export const tcresMediaOpenFrame = ( {
	title,
	buttonText,
	libraryType,
	onSelect,
} ) => {
	if ( typeof window.wp === 'undefined' || ! window.wp.media || ! window.tcresI18n ) return

	const frame = window.wp.media( {
		title,
		button: { text: buttonText },
		library: { type: libraryType },
		multiple: false,
	} )

	frame.on( 'select', () => {
		const attachment = frame.state().get( 'selection' ).first().toJSON()
		if ( ! attachment || ! attachment.id ) return
		onSelect( attachment )
	} )

	frame.open()
}


export const tcresMediaBindField = ( root, {
	selectSelector,
	removeSelector,
	onSelectClick,
	onRemoveClick,
} ) => {
	root.addEventListener( 'click', ( event ) => {
		const select = event.target.closest( selectSelector )
		if ( select && root.contains( select ) ) {
			event.preventDefault()
			onSelectClick()
			return
		}

		const remove = event.target.closest( removeSelector )
		if ( remove && root.contains( remove ) ) {
			event.preventDefault()
			onRemoveClick()
		}
	} )
}