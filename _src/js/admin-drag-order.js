// --------------------------------------------------
//	Admin-drag-order.js
// --------------------------------------------------


import { tcresDragOrderList } from './modules/admin/drag-order-list.js'


;( () => {
	'use strict'

	const init = () => {
		tcresDragOrderList()
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init )
	} else {
		init()
	}
} )()