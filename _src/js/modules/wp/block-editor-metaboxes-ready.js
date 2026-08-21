// --------------------------------------------------
//	Modules: WP: Block editor metaboxes ready
// --------------------------------------------------


export const tcresOnBlockEditorMetaboxesReady = ( callback ) => {
	let started = false

	const hasEditPostStore = () => !!(
		window.wp
		&& window.wp.data
		&& window.wp.data.select
		&& window.wp.data.select( 'core/edit-post' )
	)

	const boot = () => {
		if ( started || ! hasEditPostStore() ) return
		started = true

		let done = false
		window.wp.data.subscribe( () => {
			if ( done ) return

			const editPost = window.wp.data.select( 'core/edit-post' )
			if ( ! editPost || typeof editPost.areMetaBoxesInitialized !== 'function' ) return
			if ( ! editPost.areMetaBoxesInitialized() ) return

			done = true
			window.setTimeout( callback, 100 )
		} )
	}

	const waitForStore = () => {
		let attempts = 0
		const timer = window.setInterval( () => {
			attempts += 1

			if ( hasEditPostStore() ) {
				window.clearInterval( timer )
				boot()
				return
			}

			if ( attempts >= 50 ) {
				window.clearInterval( timer )
			}
		}, 100 )
	}

	if ( hasEditPostStore() ) {
		boot()
		return
	}

	waitForStore()
}