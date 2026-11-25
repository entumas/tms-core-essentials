// --------------------------------------------------
//	Modules: Frontend: Query listing
// --------------------------------------------------


const ATTR = 'data-tcres-query-listing-'

const attr = ( name ) => ATTR + name

const escapeAttr = ( value ) => {
	if ( window.CSS && typeof window.CSS.escape === 'function' ) return window.CSS.escape( value )
	return String( value ).replace( /"/g, '\\"' )
}

const reducedMotion = () => (
	typeof window.matchMedia === 'function'
	&& window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
)

const sameId = ( name, id ) => `[${attr( name )}][${attr( 'id' )}="${escapeAttr( id )}"]`

const pieces = ( id ) => ( {
	grid: document.querySelector( sameId( 'grid', id ) ),
	toolbars: Array.from( document.querySelectorAll( sameId( 'toolbar', id ) ) ),
	navs: Array.from( document.querySelectorAll( sameId( 'nav', id ) ) ),
} )

const controlSignature = ( filter ) => (
	( filter.getAttribute( attr( 'filter' ) ) || '' )
	+ '|'
	+ ( filter.getAttribute( attr( 'taxonomy' ) ) || '' )
)

const readControl = ( filter ) => {
	const select = filter.querySelector( 'select' )
	if ( select ) {
		return {
			options: Array.from( select.options ).map( ( option ) => option.value ).filter( ( value ) => value !== '' ),
			values: Array.from( select.selectedOptions ).map( ( option ) => option.value ).filter( ( value ) => value !== '' ),
		}
	}

	const inputs = Array.from( filter.querySelectorAll( 'input[type="checkbox"]' ) )
	return {
		options: inputs.map( ( input ) => input.value ).filter( ( value ) => value !== '' ),
		values: inputs.filter( ( input ) => input.checked ).map( ( input ) => input.value ),
	}
}

const readFilters = ( toolbar ) => {
	const relation = ( toolbar.getAttribute( attr( 'relation' ) ) || 'AND' ).toUpperCase()
	const terms = toolbar.querySelector( `[${attr( 'terms' )}]` )
	const termsRelation = terms
		? ( terms.getAttribute( attr( 'relation' ) ) || 'AND' ).toUpperCase()
		: 'AND'

	const filters = Array.from( toolbar.querySelectorAll( `[${attr( 'filter' )}]` ) ).map( ( filter ) => ( {
		type: filter.getAttribute( attr( 'filter' ) ) || '',
		taxonomy: filter.getAttribute( attr( 'taxonomy' ) ) || '',
		operator: ( filter.getAttribute( attr( 'operator' ) ) || 'in' ).toLowerCase(),
		...readControl( filter ),
	} ) )

	return { relation, termsRelation, filters }
}

const readOrder = ( toolbars ) => {
	let orderby = ''
	let order = ''

	toolbars.forEach( ( toolbar ) => {
		const control = toolbar.querySelector( `[${attr( 'order' )}]` )
		if ( ! ( control instanceof HTMLSelectElement ) || control.value === '' ) return
		const parts = control.value.split( '|' )
		if ( ! parts[ 0 ] ) return
		orderby = parts[ 0 ]
		order = parts[ 1 ] || 'DESC'
	} )

	if ( orderby === '' ) return null
	return { orderby, order }
}

const filtersAreActive = ( filters ) => filters.some( ( filter ) => filter.values.length > 0 )

const readSearch = ( toolbars ) => {
	let search = ''
	toolbars.forEach( ( toolbar ) => {
		const field = toolbar.querySelector( `[${attr( 'search' )}]` )
		if ( ! ( field instanceof HTMLInputElement ) ) return
		search = field.value.trim()
	} )
	return search
}

const readRequest = ( id, page ) => {
	const { toolbars } = pieces( id )
	const primary = toolbars[ 0 ]
	const parsed = primary
		? readFilters( primary )
		: { relation: 'AND', termsRelation: 'AND', filters: [] }
	const sort = readOrder( toolbars )
	const search = readSearch( toolbars )
	const request = {
		page,
		search,
		relation: parsed.relation,
		terms_relation: parsed.termsRelation,
		filters: parsed.filters,
		filtered: filtersAreActive( parsed.filters ) || search !== '',
	}
	if ( sort ) {
		request.orderby = sort.orderby
		request.order = sort.order
	}
	return request
}

const rememberDisabled = ( control, disabled ) => {
	if ( disabled ) {
		if ( control.hasAttribute( 'data-tcres-query-listing-was-disabled' ) ) return
		const was = control instanceof HTMLAnchorElement
			? control.getAttribute( 'aria-disabled' ) === 'true'
			: control.disabled
		control.setAttribute( 'data-tcres-query-listing-was-disabled', was ? '1' : '0' )
		return
	}

	const was = control.getAttribute( 'data-tcres-query-listing-was-disabled' )
	if ( was === null ) return
	control.removeAttribute( 'data-tcres-query-listing-was-disabled' )
	return was === '1'
}

const setControlsDisabled = ( id, disabled ) => {
	const { toolbars, navs } = pieces( id )
	toolbars.concat( navs ).forEach( ( root ) => {
		const controls = root.matches( 'select, input, button' ) ? [ root ] : []
		root.querySelectorAll( 'select, input, button' ).forEach( ( control ) => {
			controls.push( control )
		} )
		controls.forEach( ( control ) => {
			if ( ! ( control instanceof HTMLElement ) ) return
			const was = rememberDisabled( control, disabled )
			if ( disabled ) {
				control.disabled = true
				return
			}
			if ( was === undefined ) return
			control.disabled = was
		} )

		root.querySelectorAll( `a[${attr( 'page' )}]` ).forEach( ( link ) => {
			if ( ! ( link instanceof HTMLAnchorElement ) ) return
			const was = rememberDisabled( link, disabled )
			if ( disabled ) {
				link.setAttribute( 'aria-disabled', 'true' )
				return
			}
			if ( was === undefined ) return
			if ( was ) link.setAttribute( 'aria-disabled', 'true' )
			else link.removeAttribute( 'aria-disabled' )
		} )
	} )
}

const visibleCount = ( grid ) => (
	grid.querySelectorAll( `[${attr( 'item' )}]` ).length
)

const updateCount = ( id, shownCount, total, filtered ) => {
	pieces( id ).toolbars.forEach( ( toolbar ) => {
		const count = toolbar.querySelector( `[${attr( 'count' )}]` )
		if ( ! count ) return
		const shown = count.querySelector( `[${attr( 'shown' )}]` )
		const sep = count.querySelector( `[${attr( 'sep' )}]` )
		const totalEl = count.querySelector( `[${attr( 'total' )}]` )

		count.classList.toggle( 'is-filtered', filtered )
		if ( shown ) {
			shown.hidden = false
			shown.textContent = String( shownCount )
		}
		if ( sep ) sep.hidden = false
		if ( totalEl ) totalEl.textContent = String( total )
	} )
}

const syncChoices = ( select ) => {
	const root = select.closest( '.choices' )
	if ( ! root ) return

	const selected = Array.from( select.selectedOptions ).filter( ( option ) => option.value !== '' )
	const multipleList = root.querySelector( '.choices__list--multiple' )
	if ( multipleList ) {
		multipleList.querySelectorAll( '.choices__item' ).forEach( ( item ) => {
			const value = item.getAttribute( 'data-value' ) || ''
			const keep = selected.some( ( option ) => option.value === value )
			if ( ! keep ) item.remove()
		} )
	}

	const single = root.querySelector( '.choices__list--single .choices__item' )
	if ( single ) {
		const current = selected[ 0 ]
		if ( ! current ) {
			const placeholder = select.querySelector( 'option[value=""]' )
			single.textContent = placeholder
				? placeholder.textContent
				: ''
			single.setAttribute( 'data-value', '' )
			single.classList.add( 'choices__placeholder' )
		} else {
			single.textContent = current.textContent
			single.setAttribute( 'data-value', current.value )
			single.classList.remove( 'choices__placeholder' )
		}
	}
}

const writeControl = ( filter, values ) => {
	const selected = {}
	values.forEach( ( value ) => {
		selected[ value ] = true
	} )

	const select = filter.querySelector( 'select' )
	if ( select ) {
		Array.from( select.options ).forEach( ( option ) => {
			option.selected = !! selected[ option.value ]
		} )
		if ( ! select.multiple && values.length === 0 ) select.value = ''
		syncChoices( select )
		return
	}

	filter.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( input ) => {
		if ( input instanceof HTMLInputElement ) input.checked = !! selected[ input.value ]
	} )
}

const syncToolbars = ( id, sourceToolbar, sourceFilter ) => {
	if ( ! sourceFilter ) return
	const signature = controlSignature( sourceFilter )
	const values = readControl( sourceFilter ).values

	pieces( id ).toolbars.forEach( ( toolbar ) => {
		if ( toolbar === sourceToolbar ) return
		toolbar.setAttribute( 'data-tcres-query-listing-syncing', '1' )
		toolbar.querySelectorAll( `[${attr( 'filter' )}]` ).forEach( ( filter ) => {
			if ( controlSignature( filter ) !== signature ) return
			writeControl( filter, values )
		} )
		toolbar.removeAttribute( 'data-tcres-query-listing-syncing' )
	} )
}

const applyChoiceClasses = ( filter ) => {
	filter.querySelectorAll( '.choices__item[data-value]' ).forEach( ( item ) => {
		const value = item.getAttribute( 'data-value' ) || ''
		if ( value === '' ) return
		const option = filter.querySelector( `option[value="${escapeAttr( value )}"]` )
		const input = filter.querySelector( `input[value="${escapeAttr( value )}"]` )
		const source = option || input
		const unavailable = !! ( source && source.getAttribute( attr( 'available' ) ) === '0' )
		item.classList.toggle( 'is-unavailable', unavailable )
	} )
}

const markFilterAvailability = ( filter, availableValues ) => {
	const available = {}
	availableValues.forEach( ( value ) => {
		available[ value ] = true
	} )

	let optionCount = 0
	let unavailableCount = 0

	filter.querySelectorAll( 'option' ).forEach( ( option ) => {
		if ( option.value === '' ) return
		optionCount += 1
		const isUnavailable = ! available[ option.value ]
		if ( isUnavailable ) unavailableCount += 1
		option.setAttribute( attr( 'available' ), isUnavailable ? '0' : '1' )
		option.classList.toggle( 'is-unavailable', isUnavailable )
	} )

	filter.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( input ) => {
		if ( ! ( input instanceof HTMLInputElement ) || input.value === '' ) return
		optionCount += 1
		const isUnavailable = ! available[ input.value ]
		if ( isUnavailable ) unavailableCount += 1
		input.setAttribute( attr( 'available' ), isUnavailable ? '0' : '1' )
		const label = input.closest( 'label' )
		if ( label ) label.classList.toggle( 'is-unavailable', isUnavailable )
	} )

	filter.classList.toggle( 'is-unavailable', optionCount > 0 && unavailableCount === optionCount )
	applyChoiceClasses( filter )

	let cleared = false
	const select = filter.querySelector( 'select' )
	if ( select ) {
		Array.from( select.selectedOptions ).forEach( ( option ) => {
			if ( option.value === '' || available[ option.value ] ) return
			option.selected = false
			cleared = true
		} )
		if ( cleared ) syncChoices( select )
	}

	filter.querySelectorAll( 'input[type="checkbox"]' ).forEach( ( input ) => {
		if ( ! ( input instanceof HTMLInputElement ) || ! input.checked ) return
		if ( input.value === '' || available[ input.value ] ) return
		input.checked = false
		cleared = true
	} )

	if ( cleared && select && ! select.multiple ) {
		const stillSelected = Array.from( select.selectedOptions ).some( ( option ) => option.value !== '' )
		if ( ! stillSelected ) select.value = ''
		syncChoices( select )
	}

	return cleared
}

const applyAvailability = ( id, available ) => {
	if ( ! Array.isArray( available ) ) return false
	let cleared = false

	pieces( id ).toolbars.forEach( ( toolbar ) => {
		toolbar.querySelectorAll( `[${attr( 'filter' )}]` ).forEach( ( filter ) => {
			const type = filter.getAttribute( attr( 'filter' ) ) || ''
			const taxonomy = filter.getAttribute( attr( 'taxonomy' ) ) || ''
			const row = available.find( ( item ) => (
				item
				&& item.type === type
				&& ( item.taxonomy || '' ) === taxonomy
			) )
			if ( ! row || ! Array.isArray( row.values ) ) return
			if ( markFilterAvailability( filter, row.values ) ) cleared = true
		} )
	} )

	return cleared
}

const hideMessage = ( grid, kind ) => {
	grid.classList.remove( kind === 'empty' ? 'is-empty' : 'is-error' )
	grid.querySelectorAll( `[${attr( kind + '-message' )}]` ).forEach( ( node ) => node.remove() )
}

const showMessage = ( grid, kind ) => {
	hideMessage( grid, kind )
	const template = grid.querySelector( `template[${attr( kind )}]` )
	grid.classList.add( kind === 'empty' ? 'is-empty' : 'is-error' )
	if ( ! ( template instanceof HTMLTemplateElement ) ) return

	const message = document.createElement( 'div' )
	message.setAttribute( attr( kind + '-message' ), '' )
	message.appendChild( template.content.cloneNode( true ) )
	grid.appendChild( message )
}

const clearResults = ( grid ) => {
	Array.from( grid.children ).forEach( ( child ) => {
		if ( child.matches( `[${attr( 'loading' )}]` ) ) return
		if ( child.tagName === 'TEMPLATE' ) return
		child.remove()
	} )
}

const insertedItems = ( nodes ) => nodes.filter( ( node ) => (
	node instanceof Element
	&& (
		node.matches( `[${attr( 'item' )}]` )
		|| node.querySelector( `[${attr( 'item' )}]` )
	)
) )

const itemElements = ( nodes ) => {
	const items = []
	nodes.forEach( ( node ) => {
		if ( ! ( node instanceof Element ) ) return
		if ( node.matches( `[${attr( 'item' )}]` ) ) {
			items.push( node )
			return
		}
		node.querySelectorAll( `[${attr( 'item' )}]` ).forEach( ( item ) => items.push( item ) )
	} )
	return items
}

const wait = ( ms ) => new Promise( ( resolve ) => {
	window.setTimeout( resolve, ms )
} )

const enterItems = ( items, step ) => {
	if ( ! items.length ) return Promise.resolve()
	if ( reducedMotion() ) {
		items.forEach( ( item ) => {
			item.classList.remove( 'is-entering', 'is-leaving' )
			item.classList.add( 'is-visible' )
		} )
		return Promise.resolve()
	}

	items.forEach( ( item ) => {
		item.classList.remove( 'is-visible', 'is-leaving' )
		item.classList.add( 'is-entering' )
	} )

	const gap = Number.isFinite( step ) && step >= 0
		? step
		: 60
	return new Promise( ( resolve ) => {
		window.requestAnimationFrame( () => {
			items.forEach( ( item, index ) => {
				window.setTimeout( () => {
					item.classList.remove( 'is-entering' )
					item.classList.add( 'is-visible' )
					if ( index === items.length - 1 ) resolve()
				}, index * gap )
			} )
		} )
	} )
}

const leaveItems = async ( grid ) => {
	const items = Array.from( grid.querySelectorAll( `[${attr( 'item' )}]` ) )
	if ( ! items.length || reducedMotion() ) return
	items.forEach( ( item ) => {
		item.classList.remove( 'is-visible', 'is-entering' )
		item.classList.add( 'is-leaving' )
	} )
	await wait( 350 )
}

const appendHtml = ( grid, html ) => {
	const holder = document.createElement( 'div' )
	holder.innerHTML = html
	const nodes = Array.from( holder.children )
	nodes.forEach( ( node ) => grid.appendChild( node ) )
	return insertedItems( nodes )
}

const refreshEmbedded = () => {
	if ( typeof window.tcresCollapseInit === 'function' ) window.tcresCollapseInit()
	if ( typeof window.tcresTabsInit === 'function' ) window.tcresTabsInit()
	if ( typeof window.tcresModalInit === 'function' ) window.tcresModalInit()
}

const pageHref = ( href, fromPage, toPage ) => {
	if ( ! href ) return href
	const target = String( toPage )
	if ( href.includes( '{page}' ) ) return href.split( '{page}' ).join( target )

	const from = String( fromPage )
	if ( ! /^\d+$/.test( from ) ) return href

	let url
	try {
		url = new URL( href, window.location.href )
	} catch ( error ) {
		return href
	}

	const parts = url.pathname.split( '/' )
	for ( let index = parts.length - 1; index >= 0; index -= 1 ) {
		if ( parts[ index ] !== from ) continue
		parts[ index ] = target
		url.pathname = parts.join( '/' )
		const local = url.origin === window.location.origin
		return local
			? `${url.pathname}${url.search}${url.hash}`
			: url.href
	}

	return href
}

const updatePagination = ( id, page, maxPages ) => {
	pieces( id ).navs.forEach( ( nav ) => {
		if ( nav.hasAttribute( attr( 'more' ) ) ) {
			nav.hidden = maxPages <= 1 || page >= maxPages
			return
		}
		if ( ! nav.hasAttribute( attr( 'pagination' ) ) ) return
		if ( maxPages <= 1 ) {
			nav.hidden = true
			return
		}

		nav.hidden = false
		const prev = nav.querySelector( `a[${attr( 'page' )}="prev"]` )
		const next = nav.querySelector( `a[${attr( 'page' )}="next"]` )
		const list = nav.querySelector( 'ol' )
		const sampleItem = list ? list.querySelector( 'li' ) : null
		const sampleLink = sampleItem ? sampleItem.querySelector( 'a' ) : null
		const samplePage = sampleLink
			? ( sampleLink.getAttribute( attr( 'page' ) ) || '1' )
			: '1'
		const sampleHref = sampleLink ? ( sampleLink.getAttribute( 'href' ) || '' ) : ''

		if ( list && sampleItem instanceof HTMLLIElement && sampleLink instanceof HTMLAnchorElement ) {
			const template = sampleItem.cloneNode( true )
			list.querySelectorAll( 'li' ).forEach( ( item ) => item.remove() )
			const fragment = document.createDocumentFragment()
			let index = 1
			while ( index <= maxPages ) {
				const item = template.cloneNode( true )
				const link = item.querySelector( 'a' )
				if ( link instanceof HTMLAnchorElement ) {
					link.hidden = false
					link.textContent = String( index )
					link.setAttribute( attr( 'page' ), String( index ) )
					link.setAttribute( 'href', pageHref( sampleHref, samplePage, index ) )
					link.classList.toggle( 'is-current', index === page )
					link.removeAttribute( 'aria-disabled' )
					if ( index === page ) link.setAttribute( 'aria-current', 'page' )
					else link.removeAttribute( 'aria-current' )
				}
				fragment.appendChild( item )
				index += 1
			}
			list.appendChild( fragment )
		}

		const setEnd = ( link, target, blocked ) => {
			if ( ! ( link instanceof HTMLAnchorElement ) ) return
			if ( sampleHref ) link.setAttribute( 'href', pageHref( sampleHref, samplePage, target ) )
			if ( blocked ) link.setAttribute( 'aria-disabled', 'true' )
			else link.removeAttribute( 'aria-disabled' )
		}

		setEnd( prev, Math.max( 1, page - 1 ), page <= 1 )
		setEnd( next, Math.min( maxPages, page + 1 ), page >= maxPages )
	} )
}

const setLoading = ( id, loading, append ) => {
	const { grid, navs } = pieces( id )
	if ( ! grid ) return
	const indicator = grid.querySelector( `[${attr( 'loading' )}]` )

	grid.classList.toggle( 'is-loading', loading )
	if ( indicator ) indicator.hidden = ! loading
	grid.setAttribute( 'aria-busy', loading ? 'true' : 'false' )

	navs.forEach( ( nav ) => {
		if ( ! nav.hasAttribute( attr( 'more' ) ) ) return
		nav.classList.toggle( 'is-loading', loading && append )
	} )

	setControlsDisabled( id, loading )
}

const listings = new Map()

const listingState = ( id ) => {
	if ( ! listings.has( id ) ) {
		listings.set( id, { page: 1, search: '', controller: null, requestId: 0 } )
	}
	return listings.get( id )
}

const runQuery = async ( id, page, append, allowClear ) => {
	const { grid } = pieces( id )
	if ( ! grid ) return

	const state = listingState( id )
	const requestId = state.requestId + 1
	state.requestId = requestId
	if ( state.controller ) state.controller.abort()
	state.controller = typeof AbortController === 'function'
		? new AbortController()
		: null

	const animate = grid.getAttribute( attr( 'animate' ) ) || ''
	const step = Number.parseInt( grid.getAttribute( attr( 'step' ) ) || '60', 10 )
	const request = readRequest( id, page )
	let currentPage = page
	let maxPages = 0
	let settled = false

	setLoading( id, true, append )

	try {
		const body = new FormData()
		body.set( 'action', 'tcres_query_listing' )
		body.set( 'id', id )
		body.set( 'config', grid.getAttribute( attr( 'config' ) ) || '' )
		body.set( 'request', JSON.stringify( {
			page: request.page,
			orderby: request.orderby,
			order: request.order,
			search: request.search,
			relation: request.relation,
			terms_relation: request.terms_relation,
			filters: request.filters,
		} ) )

		const response = await fetch( grid.getAttribute( attr( 'ajax' ) ) || '', {
			method: 'POST',
			body,
			credentials: 'same-origin',
			signal: state.controller ? state.controller.signal : undefined,
		} )
		const payload = await response.json()
		if ( state.requestId !== requestId ) return
		if ( ! payload || ! payload.success || ! payload.data ) throw new Error( 'request' )

		const data = payload.data
		const found = Number( data.found ) || 0
		const total = Number( data.total ) || 0
		currentPage = Number( data.page ) || page
		maxPages = Number( data.max_pages ) || 0
		state.page = currentPage

		hideMessage( grid, 'error' )

		if ( ! append && ( animate === 'both' ) ) await leaveItems( grid )
		if ( state.requestId !== requestId ) return
		if ( ! append ) {
			clearResults( grid )
			hideMessage( grid, 'empty' )
		}

		const nodes = appendHtml( grid, typeof data.html === 'string' ? data.html : '' )
		if ( found === 0 && ! append ) showMessage( grid, 'empty' )

		const shouldAnimate = animate === 'enter' || animate === 'both'
		if ( shouldAnimate ) await enterItems( itemElements( nodes ), step )
		if ( state.requestId !== requestId ) return

		updateCount( id, visibleCount( grid ), total, request.filtered )
		refreshEmbedded()
		settled = true

		const cleared = applyAvailability( id, data.available )
		if ( cleared && allowClear ) {
			await runQuery( id, 1, false, false )
			return
		}
	} catch ( error ) {
		if ( error && error.name === 'AbortError' ) return
		if ( state.requestId !== requestId ) return
		showMessage( grid, 'error' )
	}

	if ( state.requestId !== requestId ) return
	setLoading( id, false, append )
	if ( settled ) updatePagination( id, currentPage, maxPages )
}

const onToolbarChange = ( id, toolbar, event ) => {
	if ( toolbar.getAttribute( 'data-tcres-query-listing-syncing' ) === '1' ) return
	const target = event.target
	if ( ! ( target instanceof Element ) ) return
	if ( event.type === 'search' && ! ( target.closest( `[${attr( 'search' )}]` ) instanceof HTMLInputElement ) ) return
	const filter = target.closest( `[${attr( 'filter' )}]` )
	const order = target.closest( `[${attr( 'order' )}]` )
	const searchField = target.closest( `[${attr( 'search' )}]` )
	if ( ! filter && ! order && ! ( searchField instanceof HTMLInputElement ) ) return
	if ( filter ) syncToolbars( id, toolbar, filter )
	if ( searchField instanceof HTMLInputElement ) {
		const value = searchField.value
		pieces( id ).toolbars.forEach( ( other ) => {
			if ( other === toolbar ) return
			const otherSearch = other.querySelector( `[${attr( 'search' )}]` )
			if ( otherSearch instanceof HTMLInputElement ) otherSearch.value = value
		} )
		const state = listingState( id )
		const search = value.trim()
		if ( state.search === search ) return
		state.search = search
	}
	if ( order && order instanceof HTMLSelectElement ) {
		pieces( id ).toolbars.forEach( ( other ) => {
			if ( other === toolbar ) return
			const otherOrder = other.querySelector( `[${attr( 'order' )}]` )
			if ( otherOrder instanceof HTMLSelectElement ) otherOrder.value = order.value
		} )
	}
	runQuery( id, 1, false, true )
}

const onNavClick = ( id, nav, event ) => {
	const trigger = event.target instanceof Element
		? event.target.closest( `[${attr( 'more' )}], [${attr( 'page' )}]` )
		: null
	if ( ! trigger || ! nav.contains( trigger ) ) return
	event.preventDefault()
	if ( trigger.getAttribute( 'aria-disabled' ) === 'true' ) return
	if ( trigger instanceof HTMLButtonElement && trigger.disabled ) return

	const state = listingState( id )
	if ( trigger.hasAttribute( attr( 'more' ) ) ) {
		runQuery( id, state.page + 1, true, false )
		return
	}

	const value = trigger.getAttribute( attr( 'page' ) ) || ''
	let page = state.page
	if ( value === 'prev' ) page -= 1
	else if ( value === 'next' ) page += 1
	else page = Number.parseInt( value, 10 ) || 1
	if ( page < 1 || page === state.page ) return
	runQuery( id, page, false, false )
}

const bindChoicesRefresh = ( toolbar ) => {
	toolbar.querySelectorAll( `[${attr( 'filter' )}]` ).forEach( ( filter ) => {
		const select = filter.querySelector( 'select' )
		if ( ! select || select.getAttribute( 'data-tcres-query-listing-choices-bound' ) === '1' ) return
		select.setAttribute( 'data-tcres-query-listing-choices-bound', '1' )
		const refreshChoices = () => {
			window.requestAnimationFrame( () => applyChoiceClasses( filter ) )
		}
		select.addEventListener( 'showDropdown', refreshChoices )
		select.addEventListener( 'search', refreshChoices )
	} )
}

const bindListing = ( grid ) => {
	if ( grid.getAttribute( 'data-tcres-query-listing-bound' ) === '1' ) return
	const id = grid.getAttribute( attr( 'id' ) ) || ''
	if ( id === '' ) return
	grid.setAttribute( 'data-tcres-query-listing-bound', '1' )

	const total = Number.parseInt( grid.getAttribute( attr( 'total' ) ) || '0', 10 ) || 0
	const state = listingState( id )
	state.page = 1
	updateCount( id, visibleCount( grid ), total, false )

	pieces( id ).toolbars.forEach( ( toolbar ) => {
		if ( toolbar.getAttribute( 'data-tcres-query-listing-bound' ) === '1' ) return
		toolbar.setAttribute( 'data-tcres-query-listing-bound', '1' )
		toolbar.addEventListener( 'change', ( event ) => onToolbarChange( id, toolbar, event ) )
		toolbar.addEventListener( 'search', ( event ) => onToolbarChange( id, toolbar, event ) )
		bindChoicesRefresh( toolbar )
	} )

	pieces( id ).navs.forEach( ( nav ) => {
		if ( nav.getAttribute( 'data-tcres-query-listing-bound' ) === '1' ) return
		nav.setAttribute( 'data-tcres-query-listing-bound', '1' )
		nav.addEventListener( 'click', ( event ) => onNavClick( id, nav, event ) )
	} )

	const maxPages = Number.parseInt( grid.getAttribute( attr( 'pages' ) ) || '0', 10 ) || 0
	updatePagination( id, 1, maxPages )
}

export const tcresQueryListingInit = ( root = document ) => {
	const scope = root instanceof Element || root instanceof Document
		? root
		: document
	scope.querySelectorAll( `[${attr( 'grid' )}]` ).forEach( ( grid ) => {
		if ( grid instanceof HTMLElement ) bindListing( grid )
	} )
}