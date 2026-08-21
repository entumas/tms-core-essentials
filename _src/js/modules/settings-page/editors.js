// --------------------------------------------------
//	Modules: Settings page: Editors
// --------------------------------------------------


const isEditorContentEmpty = ( textarea ) => {
	if ( ! textarea || ! textarea.id ) return true

	const editor = typeof window.tinymce !== 'undefined'
		? window.tinymce.get( textarea.id )
		: null

	if ( editor && typeof editor.isHidden === 'function' && ! editor.isHidden() ) {
		const text = typeof editor.getContent === 'function'
			? String( editor.getContent( { format: 'text' } ) || '' ).trim()
			: ''
		return text === ''
	}

	const tmp = document.createElement( 'div' )
	tmp.innerHTML = textarea.value || ''
	return String( tmp.textContent || tmp.innerText || '' ).trim() === ''
}

const positionEditorPlaceholder = ( wrap ) => {
	const ph = wrap.querySelector( '.tcres-settings-editor-placeholder' )
	if ( ! ph ) return

	const editArea = wrap.querySelector( '.mce-edit-area' )
	const container = wrap.querySelector( '.wp-editor-container' )
	const anchor = editArea || container
	if ( ! anchor ) return

	const wrapRect = wrap.getBoundingClientRect()
	const anchorRect = anchor.getBoundingClientRect()
	ph.style.top = Math.max( 0, Math.round( anchorRect.top - wrapRect.top + 10 ) ) + 'px'
}

const syncEditorPlaceholder = ( wrap ) => {
	if ( ! wrap || ! wrap.classList.contains( 'has-placeholder' ) ) return

	const textarea = wrap.querySelector( 'textarea.wp-editor-area' )
	const empty = isEditorContentEmpty( textarea )
	wrap.classList.toggle( 'is-empty', empty )
	if ( empty ) positionEditorPlaceholder( wrap )
}

export const bindEditorPlaceholder = ( wrap ) => {
	if ( ! wrap || wrap._tcresPlaceholderBound ) return
	wrap._tcresPlaceholderBound = true

	const textarea = wrap.querySelector( 'textarea.wp-editor-area' )
	if ( ! textarea || ! textarea.id ) return

	textarea.addEventListener( 'input', () => syncEditorPlaceholder( wrap ) )
	textarea.addEventListener( 'change', () => syncEditorPlaceholder( wrap ) )

	const bindTiny = () => {
		if ( typeof window.tinymce === 'undefined' ) return false
		const editor = window.tinymce.get( textarea.id )
		if ( ! editor || editor._tcresPlaceholderBound ) return !! editor
		editor._tcresPlaceholderBound = true
		editor.on( 'input keyup change SetContent NodeChange', () => {
			syncEditorPlaceholder( wrap )
		} )
		syncEditorPlaceholder( wrap )
		return true
	}

	if ( ! bindTiny() ) {
		let tries = 0
		const timer = window.setInterval( () => {
			tries += 1
			if ( bindTiny() || tries >= 40 ) window.clearInterval( timer )
		}, 100 )
	}

	wrap.querySelectorAll( '.wp-switch-editor' ).forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			window.setTimeout( () => syncEditorPlaceholder( wrap ), 50 )
		} )
	} )

	syncEditorPlaceholder( wrap )
}

export const refreshEditorsIn = ( container ) => {
	if ( ! container || container.hidden ) return
	if ( typeof window.tinymce !== 'undefined' ) {
		window.requestAnimationFrame( () => {
			container.querySelectorAll( 'textarea.wp-editor-area' ).forEach( ( textarea ) => {
				if ( ! textarea.id ) return
				const editor = window.tinymce.get( textarea.id )
				if ( ! editor ) return
				if ( typeof editor.show === 'function' ) editor.show()
				if ( typeof editor.fire === 'function' ) editor.fire( 'ResizeEditor' )
			} )
		} )
	}

	container.querySelectorAll( '.tcres-settings-editor.has-placeholder' ).forEach( ( wrap ) => {
		bindEditorPlaceholder( wrap )
		syncEditorPlaceholder( wrap )
	} )
}

export const initEditors = ( wrap ) => {
	wrap.querySelectorAll( '[data-tcres-editors]' ).forEach( ( container ) => {
		if ( ! container.hidden ) refreshEditorsIn( container )
	} )

	wrap.querySelectorAll( '.tcres-settings-editor.has-placeholder' ).forEach( ( editorWrap ) => {
		bindEditorPlaceholder( editorWrap )
	} )
}