// --------------------------------------------------
//	Modules: Settings page: Cards filter
// --------------------------------------------------


const VALID = [ 'all', 'active', 'inactive' ]
const HIDE_MS = 280

const getQueryVar = ( panel ) => {
	const scope = panel.getAttribute( 'data-tcres-cards-scope' ) || ''
	return scope
		? 'tcres_' + scope + '_cards'
		: ''
}

const parseFilterFromUrl = ( queryVar ) => {
	if ( ! queryVar ) return 'all'
	let value = 'all'
	try {
		const url = new URL( window.location.href )
		value = ( url.searchParams.get( queryVar ) || 'all' ).toLowerCase()
	} catch ( e ) {
		value = 'all'
	}
	return VALID.indexOf( value ) !== -1 ? value : 'all'
}

const cleanupCardTransitions = ( card ) => {
	if ( card._tcresLeaveHandler ) {
		card.removeEventListener( 'transitionend', card._tcresLeaveHandler )
		card._tcresLeaveHandler = null
	}
	if ( card._tcresLeaveFallback ) {
		window.clearTimeout( card._tcresLeaveFallback )
		card._tcresLeaveFallback = null
	}
	card.classList.remove( 'is-leaving' )
}

const cardShouldShow = ( card, filter ) => {
	const on = card.getAttribute( 'data-tcres-active' ) === '1'
	if ( filter === 'active' ) return on
	if ( filter === 'inactive' ) return ! on
	return true
}

const updateCounts = ( panel ) => {
	const cards = panel.querySelectorAll( '.tcres-settings-card.is-filterable' )
	let total  = cards.length,
		active = 0
	cards.forEach( ( card ) => {
		if ( card.getAttribute( 'data-tcres-active' ) === '1' ) active++
	} )
	const inactive = total - active
	const setParen = ( sel, n ) => {
		const el = panel.querySelector( sel )
		if ( el ) el.textContent = '(' + n + ')'
	}
	setParen( '.tcres-filter-count-all', total )
	setParen( '.tcres-filter-count-active', active )
	setParen( '.tcres-filter-count-inactive', inactive )
}

const applyCardVisibility = ( panel, filter ) => {
	const f = VALID.indexOf( filter ) !== -1 ? filter : 'all',
		cards = panel.querySelectorAll( '.tcres-settings-card.is-filterable' ),
		reduced = typeof window.matchMedia === 'function' && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches

	if ( reduced ) {
		cards.forEach( ( card ) => {
			cleanupCardTransitions( card )
			const show = cardShouldShow( card, f )
			card.classList.remove( 'is-leaving', 'is-gone', 'is-enter-prep' )
			card.hidden = ! show
			card.style.display = show ? '' : 'none'
		} )
		return
	}

	panel._tcresVisGen = ( panel._tcresVisGen || 0 ) + 1
	const gen = panel._tcresVisGen

	cards.forEach( ( card ) => {
		cleanupCardTransitions( card )
		const show  = cardShouldShow( card, f ),
			wasGone = card.classList.contains( 'is-gone' )

		if ( show ) {
			card.classList.remove( 'is-leaving' )
			if ( wasGone ) {
				card.classList.remove( 'is-gone' )
				card.style.display = ''
				card.hidden = false
				card.classList.add( 'is-enter-prep' )
				requestAnimationFrame( () => {
					requestAnimationFrame( () => {
						if ( panel._tcresVisGen !== gen ) return
						card.classList.remove( 'is-enter-prep' )
					} )
				} )
			} else {
				card.classList.remove( 'is-gone', 'is-enter-prep' )
				card.style.display = ''
				card.hidden = false
			}
			return
		}

		if ( wasGone && card.hidden ) return

		const finishHide = () => {
			if ( panel._tcresVisGen !== gen ) return
			card.classList.add( 'is-gone' )
			card.hidden = true
			card.style.display = 'none'
			card.classList.remove( 'is-leaving' )
			card._tcresLeaveHandler = null
		}

		const handler = ( e ) => {
			if ( e && e.propertyName && e.propertyName !== 'opacity' && e.propertyName !== 'transform' ) return
			card.removeEventListener( 'transitionend', handler )
			finishHide()
		}

		card._tcresLeaveHandler = handler
		card.addEventListener( 'transitionend', handler )
		card.classList.remove( 'is-gone', 'is-enter-prep' )
		card.hidden = false
		card.style.display = ''
		void card.offsetWidth
		requestAnimationFrame( () => {
			if ( panel._tcresVisGen !== gen ) return
			card.classList.add( 'is-leaving' )
		} )

		card._tcresLeaveFallback = window.setTimeout( () => {
			card.removeEventListener( 'transitionend', handler )
			finishHide()
		}, HIDE_MS )
	} )
}

const setCurrentFilterClass = ( panel, filter ) => {
	const fl = VALID.indexOf( filter ) !== -1
		? filter
		: 'all'
	panel.querySelectorAll( '.subsubsub a[data-tcres-filter]' ).forEach( ( a ) => {
		const isCur = a.getAttribute( 'data-tcres-filter' ) === fl
		a.classList.toggle( 'current', isCur )
		if ( isCur ) a.setAttribute( 'aria-current', 'page' )
		else a.removeAttribute( 'aria-current' )
	} )
}

const runFilter = ( panel, filter ) => {
	const f = VALID.indexOf( filter ) !== -1
		? filter
		: 'all'
	updateCounts( panel )
	applyCardVisibility( panel, f )
	setCurrentFilterClass( panel, f )
}

const onSwitchChange = ( ev ) => {
	const input = ev.target
	if ( ! input || input.type !== 'checkbox' || ! input.closest( '.tcres-settings-switch' ) ) return

	const card = input.closest( '.tcres-settings-card.is-filterable' )
	if ( ! card ) return

	const panel = input.closest( '.tcres-settings-cards-panel' )
	if ( ! panel ) return

	const on = !! input.checked
	card.setAttribute( 'data-tcres-active', on ? '1' : '0' )
	input.setAttribute( 'aria-checked', on ? 'true' : 'false' )
	const config = card.querySelector( ':scope > section' )
	if ( config ) {
		config.hidden = ! on
		config.classList.toggle( 'hidden', ! on )
		if ( on ) {
			config.style.removeProperty( 'display' )
		} else {
			config.style.display = 'none'
		}
	}
	runFilter( panel, parseFilterFromUrl( getQueryVar( panel ) ) )
}

const bindPanel = ( panel ) => {
	const queryVar = getQueryVar( panel )
	panel.querySelectorAll( '.subsubsub a[data-tcres-filter]' ).forEach( ( a ) => {
		a.addEventListener( 'click', ( e ) => {
			const href = a.getAttribute( 'href' )
			if ( ! href ) return
			e.preventDefault()
			const filter = a.getAttribute( 'data-tcres-filter' ) || 'all',
				next     = VALID.indexOf( filter ) !== -1 ? filter : 'all'
			if ( window.history && typeof window.history.pushState === 'function' ) {
				window.history.pushState( { tcresCardFilter: queryVar + ':' + next }, '', href )
			} else {
				window.location.assign( href )
				return
			}
			runFilter( panel, next )
		} )
	} )
}

export const initCardPanels = ( wrap ) => {
	wrap.addEventListener( 'change', onSwitchChange )

	const panels = wrap.querySelectorAll( '.tcres-settings-cards-panel' )
	panels.forEach( ( panel ) => {
		const queryVar = getQueryVar( panel )
		const initial = parseFilterFromUrl( queryVar )
		bindPanel( panel )
		runFilter( panel, initial )
	} )

	window.addEventListener( 'popstate', () => {
		panels.forEach( ( panel ) => {
			runFilter( panel, parseFilterFromUrl( getQueryVar( panel ) ) )
		} )
	} )
}