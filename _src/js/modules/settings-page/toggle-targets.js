// --------------------------------------------------
//	Modules: Settings page: Toggle targets
// --------------------------------------------------


import { refreshEditorsIn } from './editors.js'


const setToggleVisibility = ( target, show ) => {
	target.hidden = ! show
	target.classList.toggle( 'hidden', ! show )
	if ( show ) {
		target.style.removeProperty( 'display' )
	} else {
		target.style.display = 'none'
	}
}

const syncToggleTarget = ( control ) => {
	const targetId = control.getAttribute( 'data-tcres-toggle-target' )
	if ( ! targetId ) return
	const target = document.getElementById( targetId )
	if ( ! target ) return

	const isCheckable =
		control.type === 'checkbox' ||
		control.type === 'radio' ||
		control.getAttribute( 'role' ) === 'switch' ||
		( control.type === 'checkbox' && control.closest( '.tcres-settings-switch' ) )

	let show = false
	if ( isCheckable ) {
		show = !! control.checked
	} else {
		const unless = control.getAttribute( 'data-tcres-toggle-unless-value' )
		const match = control.getAttribute( 'data-tcres-toggle-value' )
		if ( unless ) {
			show = control.value !== unless
		} else if ( match ) {
			show = control.value === match
		} else {
			show = !! control.value
		}
	}

	setToggleVisibility( target, show )
	control.setAttribute( 'aria-expanded', show ? 'true' : 'false' )

	if ( show && target.hasAttribute( 'data-tcres-editors' ) ) {
		refreshEditorsIn( target )
	}
}

export const initToggleTargets = ( wrap ) => {
	wrap.querySelectorAll( '[data-tcres-toggle-target]' ).forEach( ( control ) => {
		syncToggleTarget( control )
	} )

	const onToggleEvent = ( event ) => {
		const control = event.target
		if ( ! control || ! control.getAttribute || ! control.getAttribute( 'data-tcres-toggle-target' ) ) return
		syncToggleTarget( control )
	}

	wrap.addEventListener( 'change', onToggleEvent )
	// Some admin UIs update the checked state on click before change; keep both in sync.
	wrap.addEventListener( 'input', onToggleEvent )
}