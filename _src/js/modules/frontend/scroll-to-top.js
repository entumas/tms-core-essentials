// --------------------------------------------------
//	Modules: Frontend: Scroll to top
// --------------------------------------------------


export function tcresScrollToTopInit() {
	const btn = document.getElementById( 'tcres-scroll-to-top' )
	if ( ! ( btn instanceof HTMLElement ) ) return

	const VISIBLE_CLASS = 'is-visible',
		threshold       = Math.max( 0.1, parseFloat( btn.getAttribute( 'data-tcres-scroll-threshold' ) || '1' ) || 1 ),
		avoidFooter     = btn.getAttribute( 'data-tcres-scroll-avoid-footer' ) === '1',
		footerGap       = Math.max( 0, parseInt( btn.getAttribute( 'data-tcres-scroll-footer-gap' ) || '16', 10 ) || 16 ),
		footerSelector  = ( btn.getAttribute( 'data-tcres-scroll-footer-selector' ) || '' ).trim()

	let footer = null
	if ( avoidFooter && footerSelector ) {
		try {
			footer = document.querySelector( footerSelector )
		} catch ( e ) {
			footer = null
		}
	}

	let isHiding = false

	const getViewportHeight = () => window.innerHeight

	const onTransitionEnd = ( e ) => {
		if ( e.target !== btn || e.propertyName !== 'transform' ) return
		btn.removeEventListener( 'transitionend', onTransitionEnd )
		isHiding = false
		btn.hidden = true
	}

	const updateVisibility = () => {
		const viewportHeight = getViewportHeight()
		const scrollY = window.scrollY || document.documentElement.scrollTop
		const isPastThreshold = scrollY >= viewportHeight * threshold
		const isVisible = btn.classList.contains( VISIBLE_CLASS )

		if ( isPastThreshold ) {
			isHiding = false
			btn.hidden = false
			requestAnimationFrame( () => btn.classList.add( VISIBLE_CLASS ) )
		} else if ( isVisible ) {
			isHiding = true
			btn.classList.remove( VISIBLE_CLASS )
			btn.addEventListener( 'transitionend', onTransitionEnd )
		} else if ( ! isHiding ) {
			btn.hidden = true
		}
	}

	const updatePosition = () => {
		if ( ! footer ) {
			btn.style.bottom = ''
			return
		}

		const footerRect = footer.getBoundingClientRect()
		const viewportHeight = getViewportHeight()

		if ( footerRect.top < viewportHeight ) {
			btn.style.bottom = `${viewportHeight - footerRect.top + footerGap}px`
		} else {
			btn.style.bottom = ''
		}
	}

	const update = () => {
		updateVisibility()
		updatePosition()
	}

	btn.addEventListener( 'click', () => {
		const lenis = window.tcresSmoothScrollInstance
		if ( lenis && typeof lenis.scrollTo === 'function' ) {
			lenis.scrollTo( 0 )
			return
		}
		window.scrollTo( { top: 0, left: 0, behavior: 'auto' } )
	} )

	window.addEventListener( 'scroll', update, { passive: true } )
	window.addEventListener( 'resize', update )

	update()
}