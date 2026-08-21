// --------------------------------------------------
//	Scripts.js
// --------------------------------------------------


import { tcresCollapseInit } 				from './modules/frontend/collapse.js'
import { tcresTabsInit } 					from './modules/frontend/tabs.js'
import { tcresPrivacyNoticeWatchCheckout } 	from './modules/frontend/privacy/notice-checkout.js'
import { tcresPrivacyConsentInit } 			from './modules/frontend/privacy/consent.js'
import { tcresShareContentInit } 			from './modules/frontend/share-content.js'
import { tcresChatsInit } 					from './modules/frontend/chats.js'
import { tcresScrollToTopInit } 			from './modules/frontend/scroll-to-top.js'


window.tcresCollapseInit = tcresCollapseInit
window.tcresTabsInit = tcresTabsInit
window.tcresPrivacyConsentInit = tcresPrivacyConsentInit
window.tcresShareContentInit = tcresShareContentInit
window.tcresChatsInit = tcresChatsInit
window.tcresScrollToTopInit = tcresScrollToTopInit


;( () => {
	'use strict'

	const init = () => {
		tcresCollapseInit()
		tcresTabsInit()
		tcresPrivacyConsentInit()
		tcresShareContentInit()
		tcresChatsInit()
		tcresScrollToTopInit()

		if (
			document.querySelector( '.woocommerce-checkout' ) ||
			document.querySelector( '.wp-block-woocommerce-checkout' ) ||
			document.querySelector( '.wc-block-checkout' ) ||
			document.getElementById( 'tcres-privacy-notice-checkout-template' )
		) {
			tcresPrivacyNoticeWatchCheckout( () => {
				tcresCollapseInit()
				tcresTabsInit()
				tcresPrivacyConsentInit()
			} )
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init )
	} else {
		init()
	}
} )()