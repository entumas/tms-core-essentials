// --------------------------------------------------
//	Modules: Frontend: Collapse
// --------------------------------------------------


const bindTrigger = ( trigger ) => {
	if ( !( trigger instanceof Element ) ) return
	if ( trigger.getAttribute( 'data-tcres-collapse-bound' ) === '1' ) return

	const toggle = () => {
		const triggerId = trigger.getAttribute( 'data-tcres-collapse-id' )
		if ( ! triggerId ) return

		const target = document.querySelector(
			`[data-tcres-collapse-target][data-tcres-collapse-id="${triggerId}"]`
		)
		if ( ! target ) return

		const accordion = trigger.closest( '[data-tcres-collapse-accordion]' )
		if ( accordion ) {
			accordion.querySelectorAll( '[data-tcres-collapse-trigger].is-open' ).forEach( ( openTrigger ) => {
				if ( openTrigger === trigger ) return
				openTrigger.classList.remove( 'is-open' )
				openTrigger.setAttribute( 'aria-expanded', 'false' )
			} )
			accordion.querySelectorAll( '[data-tcres-collapse-target].is-open' ).forEach( ( openTarget ) => {
				if ( openTarget === target ) return
				openTarget.classList.remove( 'is-open' )
			} )
		}

		const open = ! trigger.classList.contains( 'is-open' )
		trigger.classList.toggle( 'is-open', open )
		target.classList.toggle( 'is-open', open )
		trigger.setAttribute( 'aria-expanded', open ? 'true' : 'false' )
	}

	trigger.addEventListener( 'click', ( event ) => {
		if ( ! trigger.hasAttribute( 'data-tcres-collapse-is-anchor' ) ) {
			event.preventDefault()
		}
		toggle()
	} )

	trigger.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Enter' && event.key !== ' ' ) return
		event.preventDefault()
		toggle()
	} )

	trigger.setAttribute( 'data-tcres-collapse-bound', '1' )
}


export const tcresCollapseInit = ( root = document ) => {
	const scope = root instanceof Element || root instanceof Document ? root : document
	scope.querySelectorAll( '[data-tcres-collapse-trigger]' ).forEach( bindTrigger )
}