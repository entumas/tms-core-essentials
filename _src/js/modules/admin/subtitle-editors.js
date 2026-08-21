// --------------------------------------------------
//	Modules: Admin: Subtitle editors
// --------------------------------------------------


import {
	tcresWpEditorInit,
	tcresWpEditorSettingsSubtitle,
} from '../wp/wp-editor.js'


export const TCRES_SUBTITLE_EDITOR_ID_POST = 'tcres_subtitle'
export const TCRES_SUBTITLE_EDITOR_ID_TERM = 'tcres_subtitle_term'


export const tcresSubtitleInitEditors = ( editorId ) => {
	tcresWpEditorInit( editorId, tcresWpEditorSettingsSubtitle )
}