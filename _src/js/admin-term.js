// --------------------------------------------------
//	Admin-term.js
// --------------------------------------------------


import { TCRES_SUBTITLE_EDITOR_ID_TERM, tcresSubtitleInitEditors } 	from './modules/admin/subtitle-editors.js'
import { TCRES_HERO_EDITOR_IDS_TERM, tcresHeroInitEditors } 		from './modules/admin/hero/editors.js'
import { tcresHeroInitButtons } 									from './modules/admin/hero/buttons.js'
import { tcresTermImageInit } 										from './modules/admin/term-image.js'
import { tcresFeaturedVideoInit } 									from './modules/admin/featured-video.js'


;( () => {
	'use strict'

	const init = () => {
		tcresSubtitleInitEditors( TCRES_SUBTITLE_EDITOR_ID_TERM )
		tcresHeroInitEditors( TCRES_HERO_EDITOR_IDS_TERM )
		tcresHeroInitButtons()
		tcresTermImageInit()
		tcresFeaturedVideoInit()
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init )
	} else {
		init()
	}
} )()