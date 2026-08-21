// --------------------------------------------------
//	Modules: WP: WP editor
// --------------------------------------------------


export const tcresWpEditorSettingsSubtitle = {
	tinymce: {
		wpautop: false,
		toolbar1: 'bold,italic,underline,strikethrough,link,unlink',
		toolbar2: '',
		resize: true,
		height: 52,
		forced_root_block: false,
		force_br_newlines: true,
		force_p_newlines: false,
	},
	quicktags: {
		buttons: 'strong,em,del,link,close',
	},
	mediaButtons: false,
}


export const tcresWpEditorSettingsRich = {
	tinymce: {
		wpautop: true,
		toolbar1: 'bold,italic,underline,strikethrough,bullist,numlist,link,unlink',
		toolbar2: '',
		resize: true,
		forced_root_block: 'p',
		force_br_newlines: false,
		force_p_newlines: true,
		wp_autoresize_on: true,
		autoresize_min_height: 200,
	},
	quicktags: {
		buttons: 'strong,em,del,ul,ol,li,link,close',
	},
	mediaButtons: false,
}


export const tcresWpEditorDestroy = ( editorId ) => {
	if ( typeof window.wp === 'undefined' || ! window.wp.editor ) return

	if ( typeof window.tinymce !== 'undefined' && window.tinymce.get( editorId ) ) {
		window.tinymce.get( editorId ).remove()
	}

	window.wp.editor.remove( editorId )
}


export const tcresWpEditorInit = ( editorId, settings ) => {
	const el = document.getElementById( editorId )
	if ( ! el || el.tagName !== 'TEXTAREA' ) return
	if ( typeof window.wp === 'undefined' || ! window.wp.editor ) return

	tcresWpEditorDestroy( editorId )
	window.wp.editor.initialize( editorId, settings )
}


export const tcresWpEditorInitMany = ( editorIds, getSettings ) => {
	if ( typeof window.wp === 'undefined' || ! window.wp.editor ) return

	editorIds.forEach( ( editorId ) => {
		tcresWpEditorInit( editorId, getSettings( editorId ) )
	} )
}