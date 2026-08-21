// --------------------------------------------------
//	Modules: Settings page: Output location
// --------------------------------------------------


const syncOutputLocationSelect = ( select ) => {
	if ( ! select || ! select.classList || ! select.classList.contains( 'js-tcres-output-location-select' ) ) return
	const location = select.value || 'manual'
	const usageId = select.getAttribute( 'data-tcres-output-usage' )
	const targetId = select.getAttribute( 'data-tcres-output-target' )
	if ( usageId ) {
		const usage = document.getElementById( usageId )
		if ( usage ) usage.hidden = location !== 'manual'
	}
	if ( targetId ) {
		const target = document.getElementById( targetId )
		if ( target ) {
			const needsTarget = location === 'get_template_part' || location === 'custom'
			target.hidden = ! needsTarget
			target.querySelectorAll( '[data-tcres-output-help]' ).forEach( ( help ) => {
				help.hidden = help.getAttribute( 'data-tcres-output-help' ) !== location
			} )
			const input = target.querySelector( '.js-tcres-output-location-target-input' )
			if ( input ) {
				const gtp = input.getAttribute( 'data-tcres-placeholder-gtp' ) || 'content'
				const custom = input.getAttribute( 'data-tcres-placeholder-custom' ) || 'my_theme_after_header'
				input.placeholder = location === 'custom' ? custom : gtp
			}
		}
	}
}

export const initOutputLocationSelects = ( wrap ) => {
	wrap.querySelectorAll( 'select.js-tcres-output-location-select' ).forEach( ( select ) => {
		syncOutputLocationSelect( select )
	} )

	wrap.addEventListener( 'change', ( event ) => {
		const select = event.target
		if ( ! select || ! select.classList || ! select.classList.contains( 'js-tcres-output-location-select' ) ) return
		syncOutputLocationSelect( select )
	} )
}