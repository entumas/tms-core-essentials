// --------------------------------------------------
//	Modules: Frontend: Privacy: Consent
// --------------------------------------------------


const ERROR_CLASS = 'error'


const getErrorMessage = ( checkbox ) => {
	const custom = checkbox.getAttribute( 'data-tcres-privacy-consent-error' )
	if ( custom ) return custom
	return 'Please accept the privacy policy.'
}


const clearError = ( wrap ) => {
	if ( ! wrap ) return
	wrap.querySelectorAll( '.' + ERROR_CLASS ).forEach( ( el ) => el.remove() )
}


const showError = ( checkbox ) => {
	const wrap = checkbox.closest( '.tcres-privacy-consent' ) || checkbox.parentElement
	if ( ! wrap ) return

	clearError( wrap )

	const msg = document.createElement( 'span' )
	msg.className = ERROR_CLASS
	msg.setAttribute( 'role', 'alert' )
	msg.textContent = getErrorMessage( checkbox )
	wrap.appendChild( msg )
}


const findCheckbox = ( form ) => {
	const checkbox = form.querySelector( '[data-tcres-privacy-consent]' )
	return checkbox instanceof HTMLInputElement ? checkbox : null
}


const rejectUnchecked = ( form ) => {
	const checkbox = findCheckbox( form )
	if ( ! checkbox || checkbox.checked ) return false

	showError( checkbox )
	checkbox.focus()
	return true
}


const bindForm = ( form ) => {
	if ( ! form || form._tcresPrivacyConsentBound ) return
	// WooCommerce checkout posts by AJAX and prints its own notice.
	// Cancelling checkout_place_order skips that request, so nothing happens and no error shows.
	if ( form.classList.contains( 'checkout' ) ) return
	if ( ! findCheckbox( form ) ) return

	form._tcresPrivacyConsentBound = true

	form.addEventListener( 'change', ( event ) => {
		const target = event.target
		if ( ! ( target instanceof HTMLInputElement ) ) return
		if ( ! target.matches( '[data-tcres-privacy-consent]' ) ) return
		if ( ! target.checked ) return
		clearError( target.closest( '.tcres-privacy-consent' ) || target.parentElement )
	} )

	form.addEventListener( 'submit', ( event ) => {
		if ( ! rejectUnchecked( form ) ) return
		event.preventDefault()
		event.stopPropagation()
	}, true )
}


export const tcresPrivacyConsentInit = ( root = document ) => {
	if ( ! root || ! root.querySelectorAll ) return

	root.querySelectorAll( 'form' ).forEach( ( form ) => {
		bindForm( form )
	} )
}
