// --------------------------------------------------
//	Modules: Frontend: Tabs
// --------------------------------------------------


const TAB_TRANSITION_MS = 280,
	instances           = new Set()
let listenersBound = false

const getLocationHash = () => decodeURIComponent( window.location.hash.replace( /^#/, '' ) ).trim()

const pushLocationHash = ( hash ) => {
	if ( ! hash || `#${ hash }` === window.location.hash ) return

	const url = `${ window.location.pathname }${ window.location.search }#${ hash }`
	window.history.pushState( { tcresTab: hash }, '', url )
}

const getTabId = ( tab ) => ( tab.getAttribute( 'data-tcres-tab-id' ) || '' ).trim()

const bindTabList = ( tabList ) => {
	if ( ! ( tabList instanceof Element ) ) return null
	if ( tabList.getAttribute( 'data-tcres-tabs-bound' ) === '1' ) return null

	const tabs = Array.from( tabList.querySelectorAll( '[role="tab"][data-tcres-tab-id]' ) )
	if ( ! tabs.length ) return null

	const wrap        = tabList.nextElementSibling,
		useTransition = wrap instanceof Element && wrap.matches( '[data-tcres-tab-panels]' )

	if ( useTransition && wrap ) {
		wrap.querySelectorAll( '.tcres-tab-panel' ).forEach( ( panel ) => {
			panel.removeAttribute( 'hidden' )
			panel.setAttribute( 'aria-hidden', 'true' )
		} )
	}

	const findTabByHash = ( hash ) => tabs.find( ( tab ) => getTabId( tab ) === hash )

	const getDefaultTab = () => {
		return tabs.find( ( tab ) => tab.hasAttribute( 'data-tcres-tab-default' ) )
			|| tabs.find( ( tab ) => tab.classList.contains( 'is-active' ) || tab.getAttribute( 'aria-selected' ) === 'true' )
			|| tabs[ 0 ]
	}

	const getPanel = ( tab ) => {
		const id = tab && tab.getAttribute( 'aria-controls' )
		return id
			? document.getElementById( id )
			: null
	}

	const setPanelActive = ( panel, active ) => {
		if ( ! panel ) return

		if ( useTransition && wrap && wrap.contains( panel ) ) {
			panel.classList.toggle( 'is-active', active )
			panel.classList.remove( 'is-entering' )
			panel.setAttribute( 'aria-hidden', active ? 'false' : 'true' )
			return
		}

		panel.classList.toggle( 'is-active', active )
		panel.hidden = ! active
		panel.setAttribute( 'aria-hidden', active ? 'false' : 'true' )
	}

	const deactivateAll = () => {
		tabs.forEach( ( tab ) => {
			tab.classList.remove( 'is-active' )
			tab.setAttribute( 'aria-selected', 'false' )
			tab.setAttribute( 'tabindex', '-1' )
			setPanelActive( getPanel( tab ), false )
		} )
		if ( useTransition && wrap ) wrap.style.minHeight = ''
	}

	const activateTab = ( targetTab, updateHash = false ) => {
		if ( ! targetTab ) {
			deactivateAll()
			return
		}

		const targetPanel = getPanel( targetTab ),
			currentTab    = tabs.find( ( tab ) => tab.classList.contains( 'is-active' ) ),
			currentPanel  = currentTab ? getPanel( currentTab ) : null

		tabs.forEach( ( tab ) => {
			const isActive = tab === targetTab
			tab.classList.toggle( 'is-active', isActive )
			tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' )
			tab.setAttribute( 'tabindex', isActive ? '0' : '-1' )
		} )

		if ( updateHash ) pushLocationHash( getTabId( targetTab ) )

		if ( ! useTransition || ! wrap || ! targetPanel ) {
			tabs.forEach( ( tab ) => {
				setPanelActive( getPanel( tab ), getPanel( tab ) === targetPanel )
			} )
			return
		}

		if ( currentPanel === targetPanel ) return

		let done = false
		const onOutEnd = () => {
			if ( done ) return
			done = true
			targetPanel.classList.add( 'is-entering' )
			targetPanel.classList.remove( 'is-active' )
			targetPanel.setAttribute( 'aria-hidden', 'true' )
			requestAnimationFrame( () => {
				requestAnimationFrame( () => {
					targetPanel.classList.remove( 'is-entering' )
					targetPanel.classList.add( 'is-active' )
					targetPanel.setAttribute( 'aria-hidden', 'false' )
					wrap.style.minHeight = ''
				} )
			} )
		}

		if ( currentPanel ) {
			wrap.style.minHeight = `${ currentPanel.offsetHeight }px`
			currentPanel.classList.remove( 'is-active' )
			currentPanel.setAttribute( 'aria-hidden', 'true' )
			currentPanel.addEventListener( 'transitionend', onOutEnd, { once: true } )
			window.setTimeout( onOutEnd, TAB_TRANSITION_MS + 50 )
		} else {
			targetPanel.classList.add( 'is-entering' )
			targetPanel.classList.remove( 'is-active' )
			targetPanel.setAttribute( 'aria-hidden', 'true' )
			requestAnimationFrame( () => {
				targetPanel.classList.remove( 'is-entering' )
				targetPanel.classList.add( 'is-active' )
				targetPanel.setAttribute( 'aria-hidden', 'false' )
			} )
		}
	}

	const activateDefault = ( updateHash = false ) => {
		const defaultTab = getDefaultTab()
		if ( defaultTab ) {
			activateTab( defaultTab, updateHash )
		} else {
			deactivateAll()
		}
	}

	const onTabActivate = ( tab ) => {
		activateTab( tab, true )
	}

	const onTabClick = ( event ) => {
		const tab = event.currentTarget
		if ( tab.tagName === 'A' ) event.preventDefault()
		onTabActivate( tab )
	}

	const onTabKeydown = ( event ) => {
		const tab = event.currentTarget,
			index = tabs.indexOf( tab )
		if ( index === -1 ) return

		let targetIndex = index

		switch ( event.key ) {
			case 'ArrowLeft':
			case 'ArrowUp':
				targetIndex = index > 0
					? index - 1
					: tabs.length - 1
				break
			case 'ArrowRight':
			case 'ArrowDown':
				targetIndex = index < tabs.length - 1
					? index + 1
					: 0
				break
			case 'Home':
				targetIndex = 0
				break
			case 'End':
				targetIndex = tabs.length - 1
				break
			case 'Enter':
			case ' ':
				event.preventDefault()
				onTabActivate( tab )
				return
			default:
				return
		}

		event.preventDefault()
		const targetTab = tabs[ targetIndex ]
		if ( ! targetTab ) return
		targetTab.focus()
		onTabActivate( targetTab )
	}

	tabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', onTabClick )
		tab.addEventListener( 'keydown', onTabKeydown )
	} )

	const hash     = getLocationHash(),
		tabByHash  = hash ? findTabByHash( hash ) : null,
		initialTab = tabByHash || getDefaultTab()

	if ( initialTab ) activateTab( initialTab, false )
	else deactivateAll()

	tabList.setAttribute( 'data-tcres-tabs-bound', '1' )

	return {
		tabList,
		findTabByHash,
		getDefaultTab,
		activateTab,
		activateDefault,
	}
}

const syncFromUrl = () => {
	const hash = getLocationHash()

	instances.forEach( ( instance ) => {
		if ( ! hash ) {
			instance.activateDefault( false )
			return
		}

		const tab = instance.findTabByHash( hash )
		if ( tab ) {
			instance.activateTab( tab, false )
		} else {
			instance.activateDefault( false )
		}
	} )
}

const bindGlobalListeners = () => {
	if ( listenersBound ) return
	listenersBound = true
	window.addEventListener( 'hashchange', syncFromUrl )
	window.addEventListener( 'popstate', syncFromUrl )
}

export const tcresTabsInit = ( root = document ) => {
	const scope = root instanceof Element || root instanceof Document
		? root
		: document

	scope.querySelectorAll( '[data-tcres-tabs]' ).forEach( ( tabList ) => {
		const instance = bindTabList( tabList )
		if ( instance ) instances.add( instance )
	} )

	bindGlobalListeners()
}