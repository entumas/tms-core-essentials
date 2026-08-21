// --------------------------------------------------
//	Modules: Settings page: Form spinners
// --------------------------------------------------


export const initFormSubmitSpinners = ( wrap ) => {
	wrap.querySelectorAll( 'form.tcres-settings-form' ).forEach( ( form ) => {
		form.addEventListener( 'submit', () => {
			if ( typeof window.tinymce !== 'undefined' && typeof window.tinymce.triggerSave === 'function' ) {
				window.tinymce.triggerSave()
			}

			const sp = form.querySelector( 'p.submit .spinner' )
			const btn = form.querySelector(
				'p.submit input[type="submit"], p.submit button[type="submit"]'
			)
			if ( sp ) sp.classList.add( 'is-active' )
			if ( btn ) btn.disabled = true
		} )
	} )
}