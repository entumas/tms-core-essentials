// --------------------------------------------------
//	Modules: Admin: Hero: Editors
// --------------------------------------------------


import {
	tcresWpEditorInitMany,
	tcresWpEditorSettingsRich,
	tcresWpEditorSettingsSubtitle,
} from '../../wp/wp-editor.js'


export const TCRES_HERO_EDITOR_IDS_POST = [
	'tcres_hero_subtitle',
	'tcres_hero_description',
]

export const TCRES_HERO_EDITOR_IDS_TERM = [
	'tcres_hero_subtitle_term',
	'tcres_hero_description_term',
]


const tcresHeroGetEditorSettings = ( editorId ) => (
	editorId.indexOf( 'subtitle' ) !== -1
		? tcresWpEditorSettingsSubtitle
		: tcresWpEditorSettingsRich
)


export const tcresHeroInitEditors = ( editorIds ) => {
	tcresWpEditorInitMany( editorIds, tcresHeroGetEditorSettings )
}