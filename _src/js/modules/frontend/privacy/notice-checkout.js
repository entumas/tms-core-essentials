// --------------------------------------------------
//	Modules: Frontend: Privacy: Notice checkout
// --------------------------------------------------


const PLACE_ORDER_SELECTORS = [
	'.wc-block-components-checkout-place-order-button',
	'.wc-block-checkout__actions',
	'.wp-block-woocommerce-checkout-actions-block',
]


const findBlockInjectPoint = () => {
	for ( let i = 0; i < PLACE_ORDER_SELECTORS.length; i++ ) {
		const el = document.querySelector( PLACE_ORDER_SELECTORS[ i ] )
		if ( el ) return el
	}
	return null
}


const injectCheckoutNoticeFromTemplate = () => {
	const template = document.getElementById( 'tcres-privacy-notice-checkout-template' )
	if ( ! ( template instanceof HTMLTemplateElement ) ) return false

	const checkoutRoot = document.querySelector(
		'.wc-block-checkout, .wp-block-woocommerce-checkout, form.checkout.woocommerce-checkout'
	)
	if ( checkoutRoot && checkoutRoot.querySelector( '.tcres-privacy-notice' ) ) return true

	const target = findBlockInjectPoint()
	if ( ! target || ! target.parentNode ) return false

	const node = template.content.cloneNode( true )
	target.parentNode.insertBefore( node, target )
	return true
}


export const tcresPrivacyNoticeWatchCheckout = ( onUpdate ) => {
	const run = () => {
		injectCheckoutNoticeFromTemplate()
		if ( typeof onUpdate === 'function' ) onUpdate()
	}

	run()

	const roots = [
		document.querySelector( '.woocommerce-checkout' ),
		document.querySelector( '.wp-block-woocommerce-checkout' ),
		document.querySelector( '.wc-block-checkout' ),
	].filter( Boolean )

	if ( ! roots.length ) return

	const observer = new MutationObserver( () => {
		run()
	} )

	roots.forEach( ( root ) => {
		observer.observe( root, { childList: true, subtree: true } )
	} )
}