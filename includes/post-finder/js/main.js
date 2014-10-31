(function($) {
	var cache = {};

	$.postFinder = function(element, options) {
		var defaults, mainTemplate, itemTemplate;

		if ( 'mainTemplate' in cache ) {
			mainTemplate = cache['mainTemplate'];
		} else {
			mainTemplate = cache['mainTemplate'] = $('#tmpl-post-finder-main').html()
		}

		if ( 'itemTemplate' in cache ) {
			itemTemplate = cache['itemTemplate'];
		} else {
			itemTemplate = cache['itemTemplate'] = $('#tmpl-post-finder-item').html()
		}

		defaults = {
			template : mainTemplate,
			fieldSelector : 'input[type=hidden]',
			selectSelector : 'select',
			listSelector : '.list',
			searchSelector : '.search',
			outsideSelector : '.outside-search',
			resultsSelector : '.results',
			querySelector : 'input[type=text]',
			nonceSelector : '#post_finder_nonce'
		};

		var plugin = this;

		plugin.settings = {} //empty object to store extended settings

		var $element = $(element), //store jquery object of el
			element = element; //store html el

		plugin.init = function() {

			// over write defaults with passed options
			plugin.settings = $.extend({}, defaults, options);

			// all jquery objects are fetched once and stored in the plugin object
			plugin.$field = $element.find(plugin.settings.fieldSelector),
			plugin.$select = $element.find(plugin.settings.selectSelector),
			plugin.$list = $element.find(plugin.settings.listSelector),
			plugin.$search = $element.find(plugin.settings.searchSelector),
			plugin.$outsideSearch = $element.find(plugin.settings.outsideSelector),
			plugin.$results = plugin.$search.find(plugin.settings.resultsSelector),
			plugin.$query = plugin.$search.find(plugin.settings.querySelector),
			plugin.nonce = $(plugin.settings.nonceSelector).val();

			// bind select
			plugin.$select.on('change', function(e){
				plugin.add_item( $(this).val(), $('option:selected', this).text(), $('option:selected', this).data('permalink') );
			});

			// bind search button
			plugin.$search.find('.button').click(function(){
				plugin.search();
			});
			plugin.$search.keypress(function( e ){
				if ( e.which == 13 ) {
					event.preventDefault();
					plugin.search();
				}
			});

			// bind list
			plugin.$list.sortable({
				placeholder: 'placeholder',
				update: function(ui, e) {
					plugin.serialize();
				}
			});

			// remove button
			plugin.$list.on('click', '.icon-remove', function(e){
				e.preventDefault();
				plugin.remove_item( $(this).closest('li').data('id') );
			});

			// add button
			plugin.$results.on('click', '.add', function(e){
				e.preventDefault();
				$li = $(this).closest('li');
				plugin.add_item( $li.data('id'), $li.find('span').text(), $li.data('permalink') );
			});
			plugin.$outsideSearch.on('click', '.add', function(e){
				e.preventDefault();

				var $textInput = plugin.$outsideSearch.find( '.outside-text' ),
					$urlInput = plugin.$outsideSearch.find( '.outside-url' );

				plugin.add_item( $textInput.val() + ';' + $urlInput.val(), $textInput.val(), $urlInput.val() );

				$textInput.val( '' );
				$urlInput.val( '' );
			});

			// bind number inputs
			plugin.$list.on('keypress', 'li input.position', function(e) {
				if( e.which == 13 ) {
					e.preventDefault();
					//plugin.move_item( $(this).closest('li'), $(this).val() );
					$(this).trigger('blur');
				}
			});

			plugin.$list.on('blur', 'li input.position', function(e){
				plugin.move_item( $(this).closest('li'), $(this).val() );
			});
		};

		// move an element to a specific position if possible
		plugin.move_item = function( $el, pos ) {

			var $li = plugin.$list.find('li'),
				len = $li.length,
				$clone;

			// has to be a position thats available
			if( pos > len || pos < 1 ) {
				alert( 'Please pick a position between 1 and ' + len );
				return false;
			}

			// dont move it if were already there
			if( ( pos - 1 ) == $el.index() ) {
				return false;
			}

			// clone the element
			$clone = $el.clone();

			// first position
			if( pos == 1 ) {

				plugin.$list.prepend( $clone );

				console.log( 'prepend li' );

			// middle positions
			} else if( pos > 1 && pos < len ) {

				plugin.$list.find('li').eq( pos - 1 ).before( $clone );

				console.log( 'insert li after pos' );

			// last position
			} else if( pos == len ) {

				plugin.$list.append( $clone );

				console.log( 'append li' );
			}

			// remove the original element
			$el.remove();

			console.log( 'move complete' );

			plugin.serialize();
		};

		plugin.add_item = function( id, title, permalink ) {//private method

			// make sure we have an id
			if( id == 0 )
				return;

			if( plugin.$list.find('li').length >= $element.data('limit') ) {
				alert('Sorry, maximum number of items added.');
				return;
			}

			// see if item already exists
			if( plugin.$list.find('li[data-id="' + id + '"]').length ) {
				alert('Sorry, that item has already been added.');
				return;
			}

			// add item
			plugin.$list.append(_.template(plugin.settings.template, {
				id: id,
				title: title,
				edit_url: POST_FINDER_CONFIG.adminurl + 'post.php?post=' + id + '&action=edit',
				permalink: permalink,
				pos: plugin.$list.length + 1
			}));

			// hide notice
			plugin.$list.find('.notice').hide();

			// remove from select if there
			plugin.$select.find('option[value="' + id + '"]').remove();

			// update the input
			plugin.serialize();
		};

		//Prv method to remove an item
		plugin.remove_item = function( id ) {

			plugin.$list.find('li[data-id="' + id + '"]').remove();

			plugin.serialize();

			// show notice if no posts
			if( plugin.$list.find('li').length == 0 ) {
				plugin.$list.find('.notice').show();
			}
		};

		plugin.search = function() {
			var html = '',
				args = $element.data('args'),
				data = {
					action: 'pf_search_posts',
					s: plugin.$query.val(),
					_ajax_nonce: plugin.nonce
				};

			// merge the default args in
			data = $.extend(data, $element.data('args'));

			// display loading
			plugin.$search.addClass('loading');

			$.getJSON(
				ajaxurl,
				data,
				function(response) {
					if( typeof response.posts != "undefined" ) {
						for( var i in response.posts ) {
							html += _.template(itemTemplate, response.posts[i]);
						}
						plugin.$results.html(html);
					}
				}
			);
		};

		plugin.serialize = function() {

			var ids = [], i = 1;

			plugin.$list.find('li').each(function(){
				$(this).find('input.position').val(i);
				ids.push( $(this).data('id') );
				i++;
			});

			plugin.$field.val( ids.join(',') );
		};

		plugin.init();

	};

	$.fn.postFinder = function(options) {

		return this.each(function() {
			if (undefined == $(this).data('postFinder')) {
				var plugin = new $.postFinder(this, options);
				$(this).data('postFinder', plugin);
			}
		});

	};

})(jQuery);
