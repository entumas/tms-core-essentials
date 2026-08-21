// --------------------------------------------------
//	Modules: Frontend: Privacy: Consent
// --------------------------------------------------


const ERROR_CLASS = 'tcres-privacy-consent-error'


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


const bindForm = ( form ) => {
	if ( ! form || form._tcresPrivacyConsentBound ) return

	const checkbox = form.querySelector( '[data-tcres-privacy-consent]' )
	if ( ! ( checkbox instanceof HTMLInputElement ) ) return

	form._tcresPrivacyConsentBound = true

	checkbox.addEventListener( 'change', () => {
		if ( checkbox.checked ) {
			clearError( checkbox.closest( '.tcres-privacy-consent' ) || checkbox.parentElement )
		}
	} )

	form.addEventListener( 'submit', ( event ) => {
		if ( checkbox.checked ) return
		event.preventDefault()
		event.stopPropagation()
		showError( checkbox )
		checkbox.focus()
	}, true )
}


export const tcresPrivacyConsentInit = ( root = document ) => {
	if ( ! root || ! root.querySelectorAll ) return

	root.querySelectorAll( 'form' ).forEach( ( form ) => {
		if ( form.querySelector( '[data-tcres-privacy-consent]' ) ) {
			bindForm( form )
		}
	} )
}