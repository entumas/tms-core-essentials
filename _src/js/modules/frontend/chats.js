// --------------------------------------------------
//	Modules: Frontend: Chats
// --------------------------------------------------


export function tcresChatsInit() {
	const chat = document.getElementById( 'tcres-chat' )
	if ( ! chat ) return

	const trigger = chat.querySelector( '.tcres-chat-trigger' )
	if ( ! trigger ) return

	trigger.addEventListener( 'click', () => {
		const isOpen = chat.classList.toggle( 'is-open' )
		trigger.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' )
	} )
}