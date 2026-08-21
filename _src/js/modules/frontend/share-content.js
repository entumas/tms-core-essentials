// --------------------------------------------------
//	Modules: Frontend: Share content
// --------------------------------------------------


export const tcresShareContentInit = () => {
	document.querySelectorAll( '[data-tcres-share-copy]' ).forEach( ( el ) => {
		if ( !( el instanceof HTMLElement ) ) return
		if ( el.getAttribute( 'data-tcres-share-copy-bound' ) === '1' ) return

		el.setAttribute( 'data-tcres-share-copy-bound', '1' )

		el.addEventListener( 'click', async ( event ) => {
			event.preventDefault()

			const url = el.getAttribute( 'data-tcres-share-copy' ) || el.getAttribute( 'href' ) || ''
			if ( ! url ) return

			const doneLabel = el.getAttribute( 'data-tcres-share-copy-done' ) || 'Copied',
				nameEl      = el.querySelector( '.tcres-share-content-button-name' ),
				prevName    = nameEl ? nameEl.textContent : ''

			try {
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					await navigator.clipboard.writeText( url )
				} else {
					const input = document.createElement( 'input' )
					input.value = url
					input.setAttribute( 'readonly', '' )
					input.style.position = 'absolute'
					input.style.left = '-9999px'
					document.body.appendChild( input )
					input.select()
					document.execCommand( 'copy' )
					document.body.removeChild( input )
				}

				el.classList.add( 'is-copied' )
				if ( nameEl ) nameEl.textContent = doneLabel

				window.setTimeout( () => {
					el.classList.remove( 'is-copied' )
					if ( nameEl ) nameEl.textContent = prevName
				}, 2000 )
			} catch ( error ) {
				// Keep silent; link href remains usable as fallback.
			}
		} )
	} )
}