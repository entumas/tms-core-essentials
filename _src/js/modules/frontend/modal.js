// --------------------------------------------------
//	Modules: Frontend: Modal
// --------------------------------------------------


const OPEN_CLASS = 'is-open'
const ENTERING_CLASS = 'is-entering'
const LEAVING_CLASS = 'is-leaving'
const BODY_CLASS = 'tcres-is-modal-open'
const lastFocusByModal = new WeakMap()
let globalBound = false


const bindGlobalOnce = () => {
	if ( globalBound ) return

	globalBound = true
	document.addEventListener( 'click', onDocumentClick )
	document.addEventListener( 'keydown', onDocumentKeydown )
}


const getActiveModals = () => Array.from(
	document.querySelectorAll( `.tcres-modal.${ OPEN_CLASS }, .tcres-modal.${ ENTERING_CLASS }, .tcres-modal.${ LEAVING_CLASS }` )
)


const getOpenModals = () => Array.from( document.querySelectorAll( `.tcres-modal.${ OPEN_CLASS }` ) )


const syncBodyLock = () => {
	document.body.classList.toggle( BODY_CLASS, getActiveModals().length > 0 )
}


const getFocusable = ( el ) => Array.from( el.querySelectorAll(
	'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
) ).filter( ( node ) => node.offsetParent !== null || node === document.activeElement )


const resolveModal = ( target ) => {
	if ( ! target ) return null

	if ( target instanceof Element ) {
		return target.classList.contains( 'tcres-modal' )
			? target
			: target.closest( '.tcres-modal' )
	}

	const value = String( target ).trim()
	if ( value === '' ) return null

	if ( value.startsWith( '#' ) ) {
		return document.querySelector( value )
	}

	return document.getElementById( value ) || document.querySelector( value )
}


const parseTransitionTime = ( value ) => {
	if ( ! value || value === 'none' ) return 0

	return value
		.split( ',' )
		.map( ( part ) => part.trim() )
		.reduce( ( max, part ) => {
			const amount = parseFloat( part )
			if ( Number.isNaN( amount ) ) return max

			const unit = part.replace( /[\d.\s]/g, '' ).toLowerCase()
			const ms = unit === 'ms'
				? amount
				: amount * 1000

			return Math.max( max, ms )
		}, 0 )
}


const getModalTransitionMs = ( modal ) => {
	const dialog = modal.querySelector( '.tcres-modal-dialog' )
	const backdrop = modal.querySelector( '.tcres-modal-backdrop' )
	const nodes = [ dialog, backdrop ].filter( Boolean )

	if ( ! nodes.length ) return 300

	return nodes.reduce( ( max, node ) => {
		const style = window.getComputedStyle( node )
		const duration = parseTransitionTime( style.transitionDuration )
		const delay = parseTransitionTime( style.transitionDelay )

		return Math.max( max, duration + delay )
	}, 0 )
}


const waitForModalTransition = ( modal, callback ) => {
	const dialog = modal.querySelector( '.tcres-modal-dialog' )
	const backdrop = modal.querySelector( '.tcres-modal-backdrop' )
	const targets = [ dialog, backdrop ].filter( Boolean )
	let done = false

	const finish = () => {
		if ( done ) return
		done = true

		targets.forEach( ( target ) => {
			target.removeEventListener( 'transitionend', onTransitionEnd )
		} )
		clearTimeout( fallbackTimer )
		callback()
	}

	const onTransitionEnd = ( event ) => {
		if ( ! targets.includes( event.target ) ) return
		finish()
	}

	targets.forEach( ( target ) => {
		target.addEventListener( 'transitionend', onTransitionEnd )
	} )

	const fallbackTimer = window.setTimeout( finish, getModalTransitionMs( modal ) + 50 )
}


const onDocumentClick = ( event ) => {
	const closer = event.target.closest( '[data-tcres-modal-close]' )
	if ( ! closer ) return

	const modal = closer.closest( '.tcres-modal' )
	if ( modal ) tcresHideModal( modal )
}


const onDocumentKeydown = ( event ) => {
	const openModals = getOpenModals()
	if ( ! openModals.length ) return

	const modal = openModals[ openModals.length - 1 ]

	if ( event.key === 'Escape' ) {
		event.preventDefault()
		tcresHideModal( modal )
		return
	}

	if ( event.key !== 'Tab' ) return

	const focusable = getFocusable( modal )
	if ( ! focusable.length ) {
		event.preventDefault()
		return
	}

	const first = focusable[ 0 ]
	const last = focusable[ focusable.length - 1 ]

	if ( event.shiftKey && document.activeElement === first ) {
		event.preventDefault()
		last.focus()
	} else if ( ! event.shiftKey && document.activeElement === last ) {
		event.preventDefault()
		first.focus()
	}
}


const bindOpenTrigger = ( trigger ) => {
	if ( !( trigger instanceof Element ) ) return
	if ( trigger.getAttribute( 'data-tcres-modal-bound' ) === '1' ) return

	trigger.addEventListener( 'click', ( event ) => {
		event.preventDefault()

		const modal = resolveModal( trigger.getAttribute( 'data-tcres-modal-open' ) )
		if ( modal ) tcresShowModal( modal )
	} )

	trigger.setAttribute( 'data-tcres-modal-bound', '1' )
}


/**
 * Bind declarative open triggers inside a root node.
 *
 * @param {Document|Element} root
 */
export const tcresModalInit = ( root = document ) => {
	bindGlobalOnce()

	const scope = root instanceof Element || root instanceof Document ? root : document
	scope.querySelectorAll( '[data-tcres-modal-open]' ).forEach( bindOpenTrigger )
}


/**
 * Open a `.tcres-modal` element.
 *
 * @param {Element|string} target Modal element or selector / id.
 * @returns {Element|null}
 */
export const tcresShowModal = ( target ) => {
	const el = resolveModal( target )
	if ( ! el ) return null

	bindGlobalOnce()

	el.classList.remove( LEAVING_CLASS )
	lastFocusByModal.set( el, document.activeElement )
	el.setAttribute( 'aria-hidden', 'false' )

	const focusDialog = () => {
		const dialog = el.querySelector( '.tcres-modal-dialog' )
		if ( ! dialog ) return

		if ( ! dialog.hasAttribute( 'tabindex' ) ) {
			dialog.setAttribute( 'tabindex', '-1' )
		}
		dialog.focus()
	}

	if ( el.classList.contains( OPEN_CLASS ) ) {
		focusDialog()
		return el
	}

	el.classList.add( ENTERING_CLASS )
	syncBodyLock()

	// Enter transition: paint closed state, then open on the next frame.
	void el.offsetWidth

	window.requestAnimationFrame( () => {
		el.classList.remove( ENTERING_CLASS )
		el.classList.add( OPEN_CLASS )
		syncBodyLock()
		focusDialog()
	} )

	return el
}


/**
 * Close a `.tcres-modal` element.
 *
 * @param {Element|string} target Modal element or selector / id.
 */
export const tcresHideModal = ( target ) => {
	const el = resolveModal( target )
	if ( ! el || ! el.classList.contains( OPEN_CLASS ) || el.classList.contains( LEAVING_CLASS ) ) return

	el.classList.remove( OPEN_CLASS )
	el.classList.add( LEAVING_CLASS )

	waitForModalTransition( el, () => {
		el.classList.remove( LEAVING_CLASS )
		el.setAttribute( 'aria-hidden', 'true' )
		syncBodyLock()
		el.dispatchEvent( new CustomEvent( 'tcres-modal-hide', { bubbles: true } ) )

		const lastFocused = lastFocusByModal.get( el )
		if ( lastFocused && typeof lastFocused.focus === 'function' ) {
			lastFocused.focus()
		}
	} )
}
