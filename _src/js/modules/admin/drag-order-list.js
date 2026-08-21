// --------------------------------------------------
//	Modules: Admin: Drag (& drop) order list
// --------------------------------------------------


export function tcresDragOrderList() {
	const cfg = typeof tcresDragOrderConfig !== 'undefined' ? tcresDragOrderConfig : null
	if ( ! cfg ) return

	const tbody = document.querySelector( 'table.wp-list-table tbody#the-list' )
	if ( ! tbody ) return

	const idPrefix = cfg.idPrefix || 'post-'

	tbody.querySelectorAll( '.tcres-drag-handle' ).forEach( handle => {
		handle.setAttribute( 'draggable', 'true' )
		handle.style.webkitUserDrag = 'element'
	} )

	let draggingRow = null

	const finalize = () => {
		if ( ! draggingRow ) return

		draggingRow.classList.remove( 'is-dragging' )
		draggingRow = null

		const ids = Array.from( tbody.querySelectorAll( 'tr[id]' ) )
			.map( tr => tr.id.replace( idPrefix, '' ) )
			.filter( id => id !== '' )

		const form = new URLSearchParams()
		form.append( 'action', cfg.action )
		form.append( 'nonce', cfg.nonce )
		if ( cfg.post_type ) form.append( 'post_type', cfg.post_type )
		if ( cfg.taxonomy ) form.append( 'taxonomy', cfg.taxonomy )
		ids.forEach( id => form.append( 'order[]', id ) )

		fetch( cfg.ajaxurl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: form.toString()
		} )
			.then( r => r.json() )
			.then( resp => {
				if ( ! resp || ! resp.success ) return
				Array.from( tbody.querySelectorAll( 'tr' ) ).forEach( ( tr, i ) => {
					const num = tr.querySelector( '.tcres-order-num' )
					if ( num ) num.textContent = String( i )
				} )
			} )
			.catch( err => {
				console.error( 'Order update failed:', err )
			} )
	}

	tbody.addEventListener( 'dragstart', e => {
		const handle = e.target.closest( '.tcres-drag-handle' )
		if ( ! handle ) {
			e.preventDefault()
			return
		}

		const row = handle.closest( 'tr' )
		if ( ! row ) {
			e.preventDefault()
			return
		}

		draggingRow = row
		row.classList.add( 'is-dragging' )

		try { e.dataTransfer.setData( 'text/plain', row.id || '' ) } catch ( _ ) {}
		e.dataTransfer.effectAllowed = 'move'
	} )

	tbody.addEventListener( 'dragover', e => {
		e.preventDefault()
		if ( ! draggingRow ) return

		const target = e.target.closest( 'tr' )
		if ( ! target || target === draggingRow ) return

		const rect            = target.getBoundingClientRect(),
			shouldInsertAfter = ( e.clientY - rect.top ) > ( rect.height / 2 )

		if ( shouldInsertAfter ) {
			if ( target.nextSibling !== draggingRow ) {
				tbody.insertBefore( draggingRow, target.nextSibling )
			}
		} else if ( target !== draggingRow.nextSibling ) {
			tbody.insertBefore( draggingRow, target )
		}

		e.dataTransfer.dropEffect = 'move'
	} )

	tbody.addEventListener( 'drop', e => {
		e.preventDefault()
		if ( ! draggingRow ) return
		finalize()
	} )

	tbody.addEventListener( 'dragend', () => {
		if ( ! draggingRow ) return
		finalize()
	} )

	tbody.addEventListener( 'selectstart', e => {
		if ( e.target.closest( '.tcres-drag-handle' ) ) e.preventDefault()
	} )
}