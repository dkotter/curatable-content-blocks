/*! Curatable Content Blocks - v0.1.0
 * http://10up.com/
 * Copyright (c) 2014; * Licensed GPLv2+ */
// Ensure the global `wp` object exists.
var wp = window.wp || {};

( function( $ ) {
	'use strict';

	var frame,
		$body = $('body');

	// Sortable blocks
	$( '.sortable' ).sortable({
		handle: '.handle',
		forcePlaceholderSize: true,
		items: "> div",
		update: function( event, ui ) {
			var target = $( event.target );
			if ( target.hasClass( 'content-blocks-wrapper' ) ) {
				$( "[name^='ccb_content_blocks[']" ).each( function( i ) {
					var $this = $( this ),
						index = $this.parents( '.row' ).index(),
						name = $this.attr( 'name' ),
						replacement = name.replace( /ccb_content_blocks\[[0-9]+\]/, 'ccb_content_blocks['+ index +']' );

					$this.attr( 'name', replacement );
				});
			}
		}
    });

    $body.on( 'click', '.content-block-adder .toggle', function( e ) {
		e.preventDefault();
		$(this).toggleClass( 'open' ).siblings( '.content-block-select' ).slideToggle( 'fast' );
    });

	var cache = {};
	$body.on( 'click', '.add-content-block', function(e){
		var $this = $(this),
			$adder = $this.closest('.content-block-adder'),
			area = $adder.data('ccbArea'),
			row = $this.parents( '.row' ).index(),
			column = $this.parents( '.block' ).data( 'ccbColumn' ),
			iterator = $adder.data('ccbIterator'),
			$toggle = $adder.find('.toggle'),
			type = $this.siblings('[name=new_content_block]').val(),
			template;

		if ( type in cache ) {
			template = cache[type];
		} else {
			template = cache[type] = $('#tmpl-ccb-cb-' + type).html();
		}

		// Hard-coded instance of area support
		template = template.replace( /\{\{\{area\}\}\}/g, area );
		template = template.replace( /\{\{\{row\}\}\}/g, row );
		template = template.replace( /\{\{\{column\}\}\}/g, column );
		template = template.replace( /\{\{\{iterator\}\}\}/g, iterator );
		$adder.data('ccbIterator', iterator + 1);

		var $added = $( template ).insertBefore( $adder );
		// Example of initializing a JS widget on add
		//$added.find( '.post-finder' ).postFinder();
		//$added.find( '.select2').select2({ width: 200 });
		if ( 'embeds' === type ) {
			var editor_id = $added.find( '.wp-editor-area' ).attr( 'id' );
			quicktags( editor_id );
		}

		$toggle.click();

		e.preventDefault();
	});

	$body.on('click', '.content-block-header', function(e){
		$(this).parent('.content-block').toggleClass('collapsed');
	});

	$body.on('click', '.delete-content-block', function(e){
		$(this).closest('.content-block').remove();
		e.preventDefault();
	});

	$body.on( 'click', '.delete-row', function( e ) {
		$( this ).closest( '.row' ).remove();
		e.preventDefault();
		$( "[name^='ccb_content_blocks[']" ).each( function( i ) {
			var $this = $( this ),
				index = $this.parents( '.row' ).index(),
				name = $this.attr( 'name' ),
				replacement = name.replace( /ccb_content_blocks\[[0-9]+\]/, 'ccb_content_blocks['+ index +']' );

			$this.attr( 'name', replacement );
		});
	});

	// Manual override switcher
	$body.on( 'change', '.toggle-manual input', function(e){
		e.preventDefault();
		var $this = $( this ),
		$toggle = $this.closest( '.toggle-manual' );

		$toggle.siblings( '.non-manual' ).slideToggle( 'fast' );
		$toggle.siblings( '.manual' ).slideToggle( 'fast' );
	});

	// Media uploader
	$body.on( 'click', '.select-image', function(e) {
		var $this = $(this),
			$image = $this.siblings('img'),
			$field = $this.siblings('.image-id-input');

		e.preventDefault();

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create the media frame.
		frame = wp.media.frames.chooseImage = wp.media({
			// Set the title of the modal.
			title: 'Choose an Image',

			// Tell the modal to show only images.
			library: {
				type: 'image'
			},

			// Customize the submit button.
			button: {
				// Set the text of the button.
				text: 'Select Image'
			}
		});

		// When an image is selected, run a callback.
		frame.on( 'select', function() {
			// Grab the selected attachment.
			var attachment = frame.state().get('selection').first(),
				sizes = attachment.get('sizes'),
				imageUrl = attachment.get('url');

			// Use thumbnail size if abailable for preview
			if ( "undefined" !== typeof sizes.thumbnail ) {
				imageUrl = sizes.thumbnail.url;
			}

			// set the hidden input's value
			$field.attr('value', attachment.id);

			// Show the image in the placeholder
			$image.attr('src', imageUrl);
		});

		frame.open();
	});

	$body.on( 'click', '.remove-image', function(e) {
		var $this = $(this),
			$image = $this.siblings('img'),
			$field = $this.siblings('.image-id-input');

		e.preventDefault();

		$image.attr('src', '');
		$field.attr('value', '');
	});

	function onClickAddNewRow( e ) {
		e.preventDefault();

		var $target = $( '.ccb-add' ),
			$this = $( e.currentTarget ),
			type = $this.data( 'type' ),
			row = $body.find( '.row' ).last().index(),
			template;

		if ( type in cache ) {
			template = cache[type];
		} else {
			template = cache[type] = $( '#tmpl-ccb-cb-' + type ).html();
		}
		template = template.replace( /\{\{\{row\}\}\}/g, ++row );

		$target.before( template );
	}

	function onClickRemoveRow( e ) {
		e.preventDefault();
		var $currentTarget = $( e.currentTarget );
		$currentTarget.closest( '.row' ).remove();
		$( "[name^='ccb_content_blocks[']" ).each( function( i ) {
			var $this = $( this ),
				index = $this.parents( '.row' ).index(),
				name = $this.attr( 'name' ),
				replacement = name.replace( /ccb_content_blocks\[[0-9]+\]/, 'ccb_content_blocks['+ index +']' );

			$this.attr( 'name', replacement );
		});
	}

	$( '.ccb-choose-row a' ).on( 'click', onClickAddNewRow );
	$( '.delete-row' ).on( 'click', onClickRemoveRow );

	/*$( document ).ready(function() {
		$( '.select2' ).select2({ width: 200 });
	});*/

	var $contentBlocks = $( '.content-blocks-wrapper' ),
		editor = document.getElementById( 'postdivrich');

	var pageCuration = {
		'init': function() {
			var $curatedInput = $( document.getElementById( 'ccb-curated-page' ) );
			if ( $curatedInput.prop( 'checked' ) ) {
				pageCuration.show();
			}

			$( document.getElementById( 'ccb-curated-page' ) ).on( 'click', pageCuration.toggle );
		},

		'show': function() {
			$contentBlocks.show();
			$( editor ).hide();
		},

		'toggle': function() {
			$contentBlocks.toggle();
			$( editor ).toggle();
		}
	};
	$( document ).ready( pageCuration.init );

})( jQuery );
