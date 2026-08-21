// --------------------------------------------------
//	Admin-post.js
// --------------------------------------------------


import { TCRES_SUBTITLE_EDITOR_ID_POST, tcresSubtitleInitEditors } 	from './modules/admin/subtitle-editors.js'
import { TCRES_HERO_EDITOR_IDS_POST, tcresHeroInitEditors } 		from './modules/admin/hero/editors.js'
import { tcresHeroInitButtons } 									from './modules/admin/hero/buttons.js'
import { tcresFeaturedVideoInit } 									from './modules/admin/featured-video.js'


;( () => {
	'use strict'

	const init = () => {
		tcresSubtitleInitEditors( TCRES_SUBTITLE_EDITOR_ID_POST )
		tcresHeroInitEditors( TCRES_HERO_EDITOR_IDS_POST )
		tcresHeroInitButtons()
		tcresFeaturedVideoInit()
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init )
	} else {
		init()
	}
} )()