// --------------------------------------------------
//	Login-scripts.js
// --------------------------------------------------


import { tcresCollapseInit } 		from './modules/frontend/collapse.js'
import { tcresPrivacyConsentInit } 	from './modules/frontend/privacy/consent.js'


window.tcresCollapseInit = tcresCollapseInit
window.tcresPrivacyConsentInit = tcresPrivacyConsentInit


;( () => {
	'use strict'

	const init = () => {
		if ( document.querySelector( '[data-tcres-collapse-trigger]' ) ) {
			tcresCollapseInit()
		}
		tcresPrivacyConsentInit()
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init )
	} else {
		init()
	}
} )()