/**
 * Accessible accordion behavior for the frontend FAQ display.
 *
 * Markup ships with every panel visible and expanded so content is never
 * lost without JavaScript. This script collapses items on load and wires
 * the toggle; native <button> semantics already give Enter/Space activation
 * and correct Tab order, so no extra keyboard handling is required.
 *
 * @package Woo_Product_FAQ
 */

( function () {
	'use strict';

	/**
	 * Collapses a single accordion item.
	 *
	 * @param {HTMLElement} trigger Trigger button.
	 * @param {HTMLElement} panel   Associated panel element.
	 * @return {void}
	 */
	function collapse( trigger, panel ) {
		trigger.setAttribute( 'aria-expanded', 'false' );
		panel.hidden = true;
	}

	/**
	 * Expands a single accordion item.
	 *
	 * @param {HTMLElement} trigger Trigger button.
	 * @param {HTMLElement} panel   Associated panel element.
	 * @return {void}
	 */
	function expand( trigger, panel ) {
		trigger.setAttribute( 'aria-expanded', 'true' );
		panel.hidden = false;
	}

	/**
	 * Wires one accordion instance: collapses all items and binds toggles.
	 *
	 * @param {HTMLElement} accordion Root accordion element.
	 * @return {void}
	 */
	function initAccordion( accordion ) {
		var triggers = accordion.querySelectorAll( '.wpfaq-accordion__trigger' );

		triggers.forEach( function ( trigger ) {
			var panel = document.getElementById( trigger.getAttribute( 'aria-controls' ) );

			if ( ! panel ) {
				return;
			}

			collapse( trigger, panel );

			trigger.addEventListener( 'click', function () {
				var isExpanded = 'true' === trigger.getAttribute( 'aria-expanded' );

				if ( isExpanded ) {
					collapse( trigger, panel );
				} else {
					expand( trigger, panel );
				}
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.wpfaq-accordion' ).forEach( initAccordion );
	} );
}() );
