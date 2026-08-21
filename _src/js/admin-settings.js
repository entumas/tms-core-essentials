// --------------------------------------------------
//	Admin-settings.js
// --------------------------------------------------


import { initCardPanels } 				from './modules/settings-page/cards-filter.js'
import { initFormSubmitSpinners } 		from './modules/settings-page/form-spinners.js'
import { initEditors } 					from './modules/settings-page/editors.js'
import { initToggleTargets } 			from './modules/settings-page/toggle-targets.js'
import { initOutputLocationSelects } 	from './modules/settings-page/output-location.js'


;( () => {
	'use strict'

	const init = () => {
		initCardPanels( document )
		initFormSubmitSpinners( document )
		initEditors( document )
		initToggleTargets( document )
		initOutputLocationSelects( document )
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init )
	} else {
		init()
	}
} )()