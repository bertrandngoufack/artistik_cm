( function() {
	wp.hooks.addFilter( 'frm_deny_drop_in_repeater', 'frmCoupons', handleDenyHook );

	/**
	 * Handle the deny drop in repeater hook.
	 *
	 * @param {boolean} deny
	 * @param {HTMLElement} draggable
	 * @return {boolean}
	 */
	function handleDenyHook( deny, draggable ) {
		const isCoupon = draggable.classList.contains( 'edit_field_type_coupon' ) || draggable.id === 'coupon';
		if ( isCoupon ) {
			deny = true;
		}
		return deny;
	}
}() );
