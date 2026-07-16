/**
 * Admin repeater behavior for the FAQ product data panel.
 *
 * Handles add/remove/reorder for the FAQ rows list and reindexes field
 * names so array order always matches DOM order before submit.
 *
 * @package Woo_Product_FAQ
 */

/* global jQuery */

( function ( $ ) {
	'use strict';

	/**
	 * Config for a single repeater: DOM selectors and field names to reindex.
	 */
	var REPEATERS = {
		faq: {
			list: '[data-wpfaq-rows="faq"]',
			row: '[data-wpfaq-row="faq"]',
			addButton: '[data-wpfaq-action="add-faq"]',
			template: '#wpfaq-faq-row-template',
			emptyState: '.wpfaq-empty-state',
			fieldPrefix: 'wpfaq_faqs',
		},
		customTab: {
			list: '[data-wpfaq-rows="custom-tab"]',
			row: '[data-wpfaq-row="custom-tab"]',
			addButton: '[data-wpfaq-action="add-custom-tab"]',
			template: '#wpfaq-custom-tab-row-template',
			emptyState: '.wpfaq-empty-state',
			fieldPrefix: 'wpfaq_custom_tabs',
		},
	};

	/**
	 * Reindexes a row's input/textarea name attributes to match its
	 * current position in the DOM.
	 *
	 * @param {jQuery} $row   Row element.
	 * @param {string} prefix Field name prefix, e.g. "wpfaq_faqs".
	 * @param {number} index  Zero-based row position.
	 * @return {void}
	 */
	function reindexRow( $row, prefix, index ) {
		$row.find( '[name]' ).each( function () {
			var $field = $( this );
			var name = $field.attr( 'name' );
			var match = name.match( /\[[^\]]*\]\[([^\]]+)\]$/ );

			if ( ! match ) {
				return;
			}

			$field.attr( 'name', prefix + '[' + index + '][' + match[ 1 ] + ']' );
		} );
	}

	/**
	 * Reindexes every row in a repeater and toggles its empty-state hint.
	 *
	 * @param {Object} config Repeater config from REPEATERS.
	 * @return {void}
	 */
	function reindexRepeater( config ) {
		var $list = $( config.list );

		$list.children( config.row ).each( function ( index ) {
			reindexRow( $( this ), config.fieldPrefix, index );
		} );

		var $emptyState = $list.closest( '.wpfaq-rows-group' ).find( config.emptyState );

		if ( $emptyState.length ) {
			$emptyState.prop( 'hidden', $list.children( config.row ).length > 0 );
		}
	}

	/**
	 * Wires add/remove/sortable behavior for one repeater config.
	 *
	 * @param {Object} config Repeater config from REPEATERS.
	 * @return {void}
	 */
	function initRepeater( config ) {
		var $list = $( config.list );

		if ( ! $list.length ) {
			return;
		}

		reindexRepeater( config );

		$( document ).on( 'click', config.addButton, function ( event ) {
			event.preventDefault();

			var templateHtml = $( config.template ).html();

			if ( ! templateHtml ) {
				return;
			}

			var $newRow = $( $.parseHTML( $.trim( templateHtml ) ) );

			$list.append( $newRow );
			reindexRepeater( config );
			$newRow.find( 'input, textarea' ).first().trigger( 'focus' );
		} );

		$list.on( 'click', '[data-wpfaq-action="remove"]', function ( event ) {
			event.preventDefault();
			$( this ).closest( config.row ).remove();
			reindexRepeater( config );
		} );

		if ( $.fn.sortable ) {
			$list.sortable( {
				handle: '.wpfaq-row__handle',
				items: config.row,
				axis: 'y',
				update: function () {
					reindexRepeater( config );
				},
			} );
		}
	}

	$( function () {
		Object.keys( REPEATERS ).forEach( function ( key ) {
			initRepeater( REPEATERS[ key ] );
		} );
	} );
}( jQuery ) );
