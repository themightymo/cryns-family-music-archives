(function(window, document, $, undefined){
	'use strict';

	if(parent) {
		// Message our frame so we know when to run scripts
		parent.postMessage( 'site_loaded', '*');
	}

	// Initiate our object and vars
	var app = {
		// make sure localize_script is called (or bail)
		appp               : typeof window.appp !== 'undefined' ? window.appp : false,
		// Check for woocommerce plugin
		woo                : typeof window.apppwoo !== 'undefined' ? window.apppwoo : false,
		// Initialize snap.js left panel menu
		snapper            : new Snap({
			element         : document.getElementById('page'),
			disable         : 'right',
			hyperextensible : false,
			touchToDrag     : false // animation is too choppy
		}),
		spinner            : null,
		scriptsLoaded      : {},
		stylesLoaded       : {},
		xhr                : [],
		scriptsLoadedCount : 0,
		stylesLoadedCount  : 0,
		backhref           : '',
		backLoad		   : false,
		history			   : [],
		laststate          : window.location.href,
		timeout            : false,
		isWidth600         : true,
		$                  : {},
		modalID			   : ''
	};

	app.cacheSelectors = function() {
		app.$.body        = $('body');
		app.$.main        = $('#main');
		app.$.ajaxModal   = $('#ajaxModal');
		app.$.modalInside = $('.modal-inside');
		app.$.ioModal     = $('.io-modal');
	};

	app.init = function() {

		app.cacheSelectors();

		var isWidth600Check;

		if ( ! app.appp )
			return;

		// app.logGroup( 'apppresser.init()' );

		app.log( 'window.appp', app.appp );
		app.log( 'window.apppwoo', app.woo );

		// Check for loaded scripts/styles
		setTimeout( function() {
			app.scriptLoader();
			app.styleLoader();
			app.setCurrentNav();
		}, 1000);

		// Only add pushstate logic if "Disable dynamic page loading" is not enabled
		if( appp.can_ajax ) {
			// load with a fresh pushstate
			window.history.replaceState({}, '', window.location.href);

			// place newurl on top of url history array if moving forward in navigation,
			// but only if it's not already the first element already there
			if( app.history.length === 0 || ( app.history.length && app.history[0].url != window.location.href ) ) {
				app.history.unshift({
		            url: window.location.href
		        });
			}

	        if( !sessionStorage.urlHistory )
	        sessionStorage.urlHistory = JSON.stringify( app.history ) ;

		} else {

			// Dynamic page loading is disabled so we need to manually add back() to the button

			$('.pull-left .back').on('click', function() { window.history.back(); });
			
			// hide the back arrow if there is no history
			if( window.history.length == 1) {
				$('.pull-left .back').hide();
			}
		}

		// Load spinner
		app.$.body.append('<div class="ajax-spinner"><div class="spinner"></div></div>');
		app.$.spinner = $('.ajax-spinner');


		$('.site-header').on('touchmove',function(e){ e.preventDefault(); });
		$('.site-footer').on('touchmove',function(e){ e.preventDefault(); });


		var hammertime = new Hammer( $('.site-title-wrap').get(0) );

		hammertime.on('doubletap', function(ev) {
		    app.$.main.animate({scrollTop:0}, 'fast');
		});

		// force external links to inappbrowser
		$('body').on( 'click', '#main a', function(e) {
		   var a = new RegExp('/' + window.location.host + '/');
		   if( !a.test(e.target.href) ) {
	       		if ( !$(this).is('[href*="#"]') && e.target.tagName == 'A' ) {
		   			e.preventDefault();
		   			e.stopPropagation();
		   			window.open(e.target.href, '_blank');
		   		}
		   }
   		});


		// Check if width is > 600px
		if ( window.matchMedia ) {
			// Establishing media check
			isWidth600Check = window.matchMedia( '(min-width: 600px)' );
			// Add listener for detecting changes
			isWidth600Check.addListener( function( mediaQueryList ) {
				app.isWidth600 = mediaQueryList.matches;
				app.log( 'Width ' + ( app.isWidth600 ? '>' : '<' ) +' 600' );
			});
		}

		app.logGroup( true );

		// Setting initial values
		app.isWidth600 = isWidth600Check && isWidth600Check.matches;

		/**
		 * Multi-level left panel menu
		 */

		app.$.subMenu = $('.snap-drawer li').has('ul.sub-menu, ul.children').children('a').addClass('has-sub-menu'); // For sub-menu
		// Add back button
		app.$.subMenu.next('ul').prepend('<a href="#" class="menu-back"><i class="fa fa-angle-left fa-lg"></i> '+l10n.back+'</a>' );
		// Add right arrow to links with a sub menu
		app.$.subMenu.append('<i class="fa fa-angle-right fa-lg pull-right"></i>');


		/**
		 * Ajax panel
		 */
		/* This needs to be more flexible to allow users to use the panel in custom ways. Instead of listing selectors, we could add a class of .panel-toggle to the element, then just say anything with .panel-toggle will open the panel.  We can add a theme option so people can add any selector to use the panel. For example:
		var ajaxitems = 'ul.products li a, a.module, a.setting, a.cart-contents, .panel-toggle a, a.panel-toggle';
		var ajaxitems = [get the setting - array of selectors]; // retrieve selectors from theme option, store them in a variable
		$(ajaxitems).addClass('panel-toggle'); // add .panel-toggle to any selectors the user enters. Must be an <a> tag.
		*/
		app.backhref = app.woo && app.woo.is_shop ? app.woo.shop_url : app.appp.home_url;

		app.$.body
			.on('click', '.ajaxify, .ajaxify a, .blog .post a, li.previous a, li.next a, .entry-meta a, .entry-title a, .page-links a, .comment-author a, .woocommerce-pagination a, a.wc-forward, .bbp-forum-title a, #bbpress-forums a, a.item-link, .appp-below-post a', function(event) {
				var $self = $(this);

				if ( app.canAjax( $self ) )
					app.loadAjaxContent( $self.attr('href'), false, event );

			})
			// Slide open drawer submenus while ajax-loading the main menu item's page
			.on('click', '.snap-drawer li a', function(event) {
				var $self      = $(this);
				var hasSubMenu = $self.hasClass( 'has-sub-menu' );
				var hasExternal = $self.parent().hasClass( 'external' );

				if ( hasSubMenu ) {
					$( event.target ).next('ul').addClass('open-sub-menu');
					// event.preventDefault();
				}

				if ( hasExternal ) {
					window.open( $(this).attr( 'href' ), '_blank' );
				}

				if ( app.canAjax( $self ) ) {
					app.loadAjaxContent( $self.attr('href'), false, event );

					// If smaller screen, hide the menu onclick
					if ( ! app.isWidth600 && app.snapper.state().state == 'left' && ! hasSubMenu )
						app.snapper.close();
				}


			})
			// ajax load pages on footer menu tab clicks
			.on('click', '.footer-menu a', function(event) {
				var $self = $(this);

				if ( app.canAjax( $self ) ) {
					app.loadAjaxContent( $self.attr('href'), false, event );

					// If smaller screen, hide the menu onclick
					if ( ! app.isWidth600 && app.snapper.state().state == 'left' )
						app.snapper.close();
				}


			})
			// ajax load previously visited pages
			.on('click', 'a.back', function(event) {

				event.preventDefault();

				app.backLoad = true;

				var prevUrl = JSON.parse( sessionStorage.urlHistory );

				if( prevUrl.length <= 1 ) return;

				// remove the current url from array
				prevUrl.shift();

				setTimeout( function() {
					app.loadAjaxContent( prevUrl[0].url, false, event );
				}, 0);

				sessionStorage.urlHistory = JSON.stringify( prevUrl ) ;

			})
			.on( 'click', '.menu-back', function(event) {
				event.preventDefault();
				// Close sub menu if back button is clicked
				$(this).closest('ul').removeClass('open-sub-menu');
			})
			// Bootstrap Modal
			.on( 'click', 'a.modal-toggle, .modal-toggle a', function(event) {
				if ( ! apppresser.canModal( $(this) ) )
					return;
				event.preventDefault();
				var content = this.href+' #content';
				app.$.ajaxModal.modal();
				app.$.modalInside.load( content );
			})
			// Load login screen in modal.
			.on( 'click', '.comment-reply-login', function(event) {
				event.preventDefault();
				$('.snap-drawer .io-modal-open').trigger('click');
			})
			// Panel open
			.on( 'click', '#nav-left-open', function(){
				// Close left panel menu if it's open
				if( app.snapper.state().state == 'left' )
					app.snapper.close();
				else
					app.snapper.open( 'left' );
			})
			/*
			* modal window a work in progress...
			*/
			.on( 'click', '.io-modal-open, .io-modal-close', function(event) {
				event.preventDefault();

				var UpClasses   = 'slide-in-up-add ng-animate slide-in-up slide-in-up-add-active';
				var downClasses = 'slide-in-up-remove slide-in-up-remove-active';

				if ( $(this).hasClass( 'io-modal-open' ) ) {

					//get href of button that matches id of modal div
					app.modalID = $(this).attr('href');

					if( app.modalID == '#loginModal') {
						$('input[name=redirect_to]').val(window.location);
					}
					
					$('#error-message').html(' ');
					// need to move .css to css file
					$(app.modalID).css('display', 'block').removeClass(downClasses).addClass(UpClasses);

				} else {

					// slide down modal and put it back in the content area.
					$('.io-modal').removeClass(UpClasses).addClass(downClasses).css('display', 'none');
					$('form').trigger("reset");
					app.$.spinner.hide();

				}
			})
			.on( 'submit', 'form#loginform', function(event) {
						
				var data = $(this).serializeArray().reduce(function(obj, item) {
					obj[item.name] = item.value;
					return obj;
				}, {});
				
				if( '' === data.log || '' === data.pwd ) {
					event.preventDefault();
				}
			});

		// Close ioModal when opening snap drawer
		app.snapper.on( 'open', function(){
			if ( app.$.ioModal.data( 'isOpen' ) ) {
				app.ioModal.close();
			}
		});

		// Instantiate fastclick
		FastClick.attach(document.body);

		// Display alert when device goes offline
		document.addEventListener("offline", function () {
			alert( l10n.offline );
		}, false);

		// Swipe to go forward/back
		if( typeof Hammer == "function" && window.appp.can_ajax ) {

			// This doesn't work with a jQuery selector
			var hammerel = document.getElementById('main');
	
			var apppTouch = new Hammer( hammerel );
			
		    apppTouch.on('swipe', function(ev) {
			    
			    var nextLink = $('.appp-below-post a[rel="next"]').attr('href');
				var prevLink = $('.appp-below-post a[rel="prev"]').attr('href');
			
			    if( ev.deltaX >= 100 ) {
				    
				    if(prevLink) {
				    	apppresser.loadAjaxContent( prevLink );
				    }
				    
			    } else if( ev.deltaX <= -100 ) {

				    if(nextLink) {
				    	apppresser.loadAjaxContent( nextLink );
				    }
			    }
			});

		}

	}; // init

	app.ioModal = (function(){
		var UpClasses   = 'slide-in-up-add ng-animate slide-in-up slide-in-up-add-active';
		var downClasses = 'slide-in-up-remove slide-in-up-remove-active';

		return {
			open: function() {
				app.$.ioModal
					.removeClass( downClasses )
					.addClass( UpClasses )
					.data( 'isOpen', true )
					.trigger('isOpen');
			},
			close: function() {
				app.$.ioModal
					.removeClass( UpClasses )
					.addClass( downClasses )
					.data( 'isOpen', false )
					.trigger('isClosed');

				// iOS scroll fix
				setTimeout( function() { app.$.ioModal.removeClass( downClasses ); }, 150 );
			}
		};
	})();

	app.setCurrentNav = function() {
		// get current page's corresponding nav item
		var $current = $('.has-sub-menu[href="'+ window.location.href +'"]');

		if ( $current.length ) {
			// Open its corresponding submenu
			$current.next('ul').addClass('open-sub-menu');
			var $parent = $current.parents( '.sub-menu' );
			while ( $parent.length ) {
				// And any submenu's above it
				$parent.addClass('open-sub-menu');
				$parent = $parent.parents( '.sub-menu' );
			}
		}
	};

	app.scriptLoader = function( $scripts ) {
		

		$scripts = $scripts || $( 'script[src]' );
		var addedscripts = {}, filename, src, count = 0, counted = ( 'length' in app.scriptsLoaded );

		app.scriptsLoaded.length = counted ? app.scriptsLoaded.length : 0;

		$scripts.each(function () {
			var $self = $(this);

			if ( $self.data('loaded') === true )
				return true;

			src      = $self.attr('src');
			filename = src.replace(/^.*[\\\/]/, '').replace(/(\?.*)|(#.*)/g, '');

			// if ( $.inArray( filename, app.scriptsLoaded ) !== -1 )
			if ( filename in app.scriptsLoaded )
				return true;

			app.scriptsLoaded.length++;
			count++;
			app.scriptsLoaded[filename] = src;
			addedscripts[filename] = src;
			// addedscripts.push( filename );
			$self.data('loaded', true);
		});

		app.log( 'scriptLoader' );

		if ( ! count ) {
			app.log( 'No new scripts to load.' );
			return addedscripts;
		}

		if ( counted )
			app.log( 'addedscripts', count, addedscripts );
		app.log( 'app.scriptsLoaded', app.scriptsLoaded );

		app.log( 'END scriptLoader' );

		return addedscripts;

	};

	app.styleLoader = function( $styles ) {
		

		$styles   = $styles || $( 'link[type="text/css"]' );

		var addedStyles = {}, filename, src, count = 0, counted = ( 'length' in app.stylesLoaded );

		app.stylesLoaded.length = counted ? app.stylesLoaded.length : 0;

		$styles.each(function () {
			var $self = $(this);

			if ( $self.data('loaded') === true )
				return true;

			src      = $self.attr('href');
			filename = src.replace(/^.*[\\\/]/, '').replace(/(\?.*)|(#.*)/g, '');

			// if ( $.inArray( filename, app.stylesLoaded ) !== -1 )
			if ( filename in app.stylesLoaded )
				return true;

			app.stylesLoaded.length++;
			count++;
			app.stylesLoaded[filename] = src;
			addedStyles[filename] = src;
			// addedStyles.push( filename );
			$self.data('loaded', true);
		});

		app.log( 'styleLoader' );

		if ( ! count ) {
			app.log( 'No new styles to load.' );
			return addedStyles;
		}

		if ( counted )
			app.log( 'addedStyles', count, addedStyles );
		app.log( 'app.stylesLoaded', app.stylesLoaded );

		app.log( 'END styleLoader' );

		return addedStyles;

	};

	app.loadAjaxContent = function( href, $selector, event) {
		

		// var for passed event target
		if(event) {
			var that = event.target;
			event.preventDefault();
		}

		var fragments = href.split( '/' );
		// Don't ajax page fragments
		if ( fragments[fragments.length-1].charAt(0) === '#' && !$(that).parent().hasClass('back') )
			return;

		// @TODO get ajax working on main nav items
		// (localized data not working)

		var titles = {
			'title'    : $('title'),
			'navtitle' : $('.site-title-wrap h1')
		};
		$selector  = $selector || app.$.main;
		href       = href || this.href;

		// Cancel pending timeout actions
		if (app.timeout)
			clearTimeout(app.timeout);

		// Cancel pending xhr requests
		$.each(app.xhr, function( index, request ) {
			// app.log( 'aborting', index, request.requestURL );
			request.abort();
		});

		if ( app.doingAjax === true )
			return;
		app.doingAjax = true;

		// Show ajax spinner
		app.$.spinner.show();
		
		setTimeout(function() {
			app.$.spinner.hide();
		}, 60000);
	
		// For native transitions
		var nativeTrans  = (typeof event === 'undefined') ? 0 : event.currentTarget.className.indexOf('transition-left');
		var dirRight     = (typeof event === 'undefined') ? 0 : event.currentTarget.className.indexOf('transition-right');

		// Do our ajax
		var status = $.ajax({
			url: href,
			type: 'GET',
			dataType: 'html',
			cache: false
		}).done(function( responseText ) {

			if( parent && nativeTrans >= 0 ) {
				// Message our frame so we know when to run transition
				parent.postMessage( 'native_transition_left', '*');
			} else if( parent && dirRight >= 0 ) {
				// This one goes right (for back button)
				parent.postMessage( 'native_transition_right', '*');
			}
			
			// Need to delay this for native transition timing
			// setTimeout(function() {

				var html       = $("<div>").append( $.parseHTML( responseText, document, true ) );
				var $main      = html.find( '#main' );
				var newtitles    = {
					'title'    : html.find( 'title' ),
					'navtitle' : html.find( '.site-title-wrap h1' )
				};
				var newclasses = $main.attr( 'class' ).replace( 'site-main', '' );
				var content    = $main.children().unwrap();
				var appp_header_right = html.find( '#top-menu3' );
				var appp_pull_left = html.find( '.pull-left' );
				var appp_modal = html.find( '.io-modal' );
				var appp_activity_modal = html.find( '#activity-post-form' );
				// Get scripts on new page and filter out any that have been loaded on the page already
				var scripts    = app.scriptLoader( html.find( 'script[src]' ) );
				var styles     = app.styleLoader( html.find( 'link[type="text/css"]' ) );
				// @TODO figure out how to re-load localized data
				// app.loadL10n( html );
				// Replace existing page body classes with new
				app.$.body.attr( 'class', newclasses );
				app.$.main.attr( 'class', newclasses );
				// Replace existing page <title> with new
				titles.title.text( newtitles.title.text() );
				// Replace existing page nav title with new
				titles.navtitle.html( newtitles.navtitle.html() );

				// Replace existing page content with new
				$( '#top-menu3' ).replaceWith( appp_header_right );
				$( '.pull-left' ).replaceWith( appp_pull_left );
				$( '#activity-post-form' ).replaceWith( appp_activity_modal );

				$selector.html( content );

				// Change url to reflect new page
				app.change_url( href );

			// }, 60);

			app.timeout = setTimeout(function (){
				// Loop through our new scripts
				$.each( scripts, function( filename, url ) {

					app.log( '$.each(scripts)', filename, url );

					var response = app.loadScript( url, true, function() {
						// Increase our scripts loaded count
						app.scriptsLoadedCount++;

						app.log( 'script retrieved', url );
						app.log( 'scripts retrieved', app.scriptsLoadedCount );
					});
					if ( response ) {

						response
							.done(function( script, textStatus, jqXHR ) {
								// jqXHR.requestURL = url;
								// app.log( 'rescript: Status', url );
							})
							.fail(function( jqXHR, settings, exception ) {
								app.log( 'loadScript: Triggered ajaxError handler for: '+ url, exception );
							});

						// Add this script loading ajax request to our pending xhr requests
						app.xhr.push( response );
					}
				});
				$.each( styles, function( filename, url ) {

					app.log( '$.each(styles)', filename, url );
					app.loadCSS( url );
					app.stylesLoadedCount++;
					app.log( 'style retrieved', url );
					app.log( 'styles retrieved', app.stylesLoadedCount );

				});

			}, 30);

			// A hook for other JS functions to run
			// Passes in jQuery, our $selector and the href
			$(document).trigger( 'load_ajax_content_done', $, $selector, href );
			app.$.body.trigger( 'post-load', $, $selector, href );

			if(parent) {
				// Message our frame so we know when to run scripts
				parent.postMessage( 'load_ajax_content_done', '*');
			}


		}).complete( function( jqXHR, status ) {

			var href;

			// jqXHR.requestURL = href;
			// app.log( 'selector load was performed.' );

			// Hide spinner
			app.$.spinner.fadeOut('fast');

			if ( status !== 'success' )
				return;

			// app.log( 'selector load was successful.' );

			app.$.main.animate({scrollTop:0}, 'fast');

			// add current_page_item class the clicked tab or drawer item
			if ( $(that).parents().hasClass('footer-menu') ) {
				href = $( that ).attr('href');
				$( '.footer-menu li' ).removeClass('current_page_item');
				$( that ).parent().addClass('current_page_item');
				$( '#site-navigation ul.menu li' ).removeClass('current_page_item');
				$('#site-navigation ul.menu a[href="' + href + '"]').parent().addClass('current_page_item');

			}

			if ( $(that).parents().hasClass('menu') ) {
				href = $( that ).attr('href');
				$( '#site-navigation ul.menu li' ).removeClass('current_page_item');
				$( that ).parent().addClass('current_page_item');
				$( '.footer-menu li' ).removeClass('current_page_item');
				$('.footer-menu a[href="' + href + '"]').parent().addClass('current_page_item');
			}

		});

		// Add this ajax request to our pending xhr requests
		app.xhr.push( status );
		app.doingAjax = false;

	};

	app.canAjax = function( $element ) {
		return ( apppresser.appp.can_ajax && ! $element.is('.menu-back, .external, .no-ajax, .menu .no-ajax > a, .nav-divider, .modal-toggle, .modal-toggle a') || $element.is('.back')  );
	};

	app.canModal = function( $element ) {
		return ( apppresser.appp.can_ajax && ! $element.is('a.no-modal, .no-modal a') );
	};

	app.change_url = function( newurl ) {
		
		newurl = newurl || apppresser.backhref;

		// Change url to reflect new page
		window.history.pushState({},'', newurl);

		var prevUrl = JSON.parse(sessionStorage.urlHistory);

		if( !app.backLoad ) {
			// place newurl on top of url history array if moving forward in navigation,
			// but only if it's not already the first element already there
			if( prevUrl.length === 0 || ( prevUrl.length && prevUrl[0].url != window.location.href ) ) {
				// adds new url to the beginning of the array
				prevUrl.unshift({
		            url: newurl
		        });
			}
        }
        //save adjusted url history array to local storage incase browser gets refreshed
        sessionStorage.urlHistory = JSON.stringify( prevUrl ) ;

        app.backLoad = false;

	};

	app.loadL10n = function( html ) {
		var inlineScripts = html.find( "script[type='text/javascript']" ).text();
		var pattern = /\/\* <!\[CDATA\[ \*\/([\s\S]*)\/\* \]\]> \*\//;
		// var test = inlineScripts.search(pattern);
		var matches = inlineScripts.match(pattern);
		var script = matches[1];
		eval( script );
	};

	app.loadScript = function(url, arg1, arg2) {
		
		var cache = false, callback = null;
		// arg1 and arg2 can be interchangable as either the callback function or the cache bool
		if ($.isFunction(arg1)){
			callback = arg1;
			cache = arg2 || cache;
		} else {
			cache = arg1 || cache;
			callback = arg2 || callback;
		}
		// equivalent to a $.getScript but with control over cacheing
		return $.ajax({
			url: url,
			type: 'GET',
			dataType: 'script',
			cache: cache,
			success: callback
		});
	};

	app.loadCSS = function( href ) {

		var cssLink = $('<link>');
		$("head").append(cssLink); // IE hack: append before setting href

		cssLink.attr({
			rel:  "stylesheet",
			type: "text/css",
			href: href
		});

	};

	app.untrailingslashit = function(str) {
		if ( str.substr(-1) == '/' ) {
			return str.substr(0, str.length - 1);
		}
		return str;
	};

	/**
	 * Safely log things if query var is set
	 * @since  1.0.0
	 */
	app.log = function() {
		
		if ( this.appp.debug && console && typeof console.log === 'function' ) {
			console.log.apply(console, arguments);
		}
	};

	/**
	 * Group logged items
	 * @since  1.0.0
	 */
	app.logGroup = function( groupName, expanded ) {
		

		if ( this.appp.debug && console && typeof console.group === 'function' ) {
			if ( groupName === true ) {
				console.groupEnd();
			} else if ( typeof groupName === 'undefined' ) {
				if ( expanded )
					console.group();
				else
					console.groupCollapsed();
			} else {
				if ( expanded )
					console.group( groupName );
				else
					console.groupCollapsed( groupName );
			}
		}
	};

	/*
	 * Handles ajax modal new password request
	 */
	app.newPassword = function() {

		var codeMsg = $('.reset-code-rsp');

		console.log('newPassword');

		if( $('#lost_email').val() === '' ) {
			codeMsg.html('Email required.');
			return false;
		}

		codeMsg.html('<i class="fa fa-cog fa-spin"></1>');

		var data = {
			// app_lost_password functions found in apppresser core plugin, inc/AppPresser_Ajax_Extras.php
	  		action: 'app-lost-password',
	  		email: $('#lost_email').val(),
	  		nonce: $('#app_new_password').val()
	  	};

	  	console.dir(data);

	  	var reset = $.ajax({
			type: 'post',
			url : apppCore.ajaxurl,
			dataType: 'json',
			data : data,
			success: function( response ) {
				console.dir(response);
				codeMsg.html(response.data.message);
				$('input[type=text]').val('');
				$('input[type=password]').val('');
			},
			error: function(e) {
				console.log('Password reset error ' + e);
			}

		});

		return reset;

	};

	/*
	 * Handles ajax modal change password request
	 */
	app.changePassword = function() {

		var pwVal = $('#app-pw').val();
		var pwrVal = $('#app-pwr').val();
		var rCode = $('#reset-code').val();
		var pwMsg = $('.psw-msg');

		console.log('changePassword');

		if ( pwVal != pwrVal || pwVal === '' ) {
				pwMsg.html('Passwords do not match.');
				return false;
		}

		if ( rCode === '' ) {
				pwMsg.html('Please enter your reset code.');
				return false;
		}

		pwMsg.html('<i class="fa fa-cog fa-spin"></i>');

		var data = {
	  		action: 'app-validate-password',
	  		code: rCode,
	  		password: pwVal,
	  		nonce: $('#app_new_password').val()
	  	};

	  	var validation = $.ajax({
				type: 'post',
				url : apppCore.ajaxurl,
				dataType: 'json',
				data : data,
				success: function( response ) {
					pwMsg.html(response.data.message);
					$('#app-pw').val('');
					$('#app-pwr').val('');
					if( response.data.success ) {
						pwMsg.append(' Logging you in...');
						setTimeout( function() {
							window.location.reload();
						}, 1000);
					}
				}

		});

		return validation;

	};

	/*
	 * Ajax password reset events
	 */

	$( 'body' )

	.on('click', '#app-new-password', app.newPassword )

	.on('click', '#app-change-password', app.changePassword );

	/*
	 * Add comment to page after submitted with ajax
	 */
	app.appendComment = function( author, comment ) {

		var el;

		if( $('.comment-list') ) {
			el = $('.comment-list');
		} else {
			el = $('#comments');
		}
		console.log('Append ', el);
		el.append( '<li class="comment" id="ajax-comment"> <article class="comment-body"> <footer class="comment-meta"> <div class="comment-author vcard"> <cite class="fn">' + author + '</cite> <span class="says">says:</span></div><!-- .comment-author --> <div class="comment-metadata"></div><!-- .comment-metadata --> <p class="comment-awaiting-moderation">Your comment is awaiting moderation.</p> </footer><!-- .comment-meta --> <div class="comment-content"> <p>' + comment + '</p> </div><!-- .comment-content --> </article><!-- .comment-body --> </li>' );
	};
	
	// do not submit comment if no value
	$( 'body' ).on( 'click', '#respond #submit', function() {
	
		// comment check
		var $comment = $( this ).closest( '#respond' ).find( '#comment' ),
			comment  = $.trim( $comment.val() );

	    if ( comment === '' ) {
	        alert( appp.i18n_required_comment_text );
			return false;
		}
		
		// rating check
		var $rating = $( this ).closest( '#respond' ).find( '#rating' ),
		rating  = $rating.val();

		if ( $rating.size() > 0 && ! rating && wc_single_product_params.review_rating_required === 'yes' && comment !== '' ) {
			alert( wc_single_product_params.i18n_required_rating_text );
			return false;
		}
			
	});

	/*
	 * Ajax comment modal
	 */

	if( $('body').hasClass('logged-in') ) {
		$('.ajax-comment-form-author, .ajax-comment-form-email, .ajax-comment-form-url').hide();
	}

	$('body')

	.on('click' , '.comment-reply-link', function() {
		// get the comment id from href
		var re = /\b\d+/;
		var comment_id = re.exec(this.href);

		// send comment id to form and open comment modal
		$('#ajax-comment-parent').val(comment_id);
		$( '.appp-comment-btn' ).trigger('click');
	} )

	.on( 'click', '#ajax-comment-form-submit #submit', function() {

		var commentform=$('#commentform');

		// Defining the Status message element 
		var statusdiv = $('#comment-status');

		var comment_author = $('.ajax-comment-form-author #author').val();
		var comment_email = $('.ajax-comment-form-email #email').val();
		var comment = $('.ajax-comment-form-comment #comment').val();
		var comment_parent = $('#ajax-comment-parent').val();
		var logged_in = $('body').hasClass('logged-in');
		var commentData;

		if(logged_in) {

			$('.ajax-comment-form-author, .ajax-comment-form-email, .ajax-comment-form-url').hide();

			commentData = {
				comment_post_ID: $('#commentform #comment_post_ID').val(),
				comment: comment,
				comment_parent: comment_parent,
			};

		} else {

			// if name, email, or comment empty, show error
			if( !comment_author || !comment_email || !comment ) {
				statusdiv.html('<p class="ajax-error" >Please fill out required fields.</p>');
				return false;
			}

			commentData = {
				author: comment_author,
				email: comment_email,
				url: $('.ajax-comment-form-url #url').val(),
				comment_post_ID: $('#commentform #comment_post_ID').val(),
				comment: comment,
				comment_parent: comment_parent,
			};
		}

		//Add a status message
		statusdiv.html('<p class="ajax-placeholder">Processing...</p>');

		//Extract action URL from commentform
		var formurl=commentform.attr('action');

		//Post Form with data
		$.ajax({
			type: 'post',
			url: formurl,
			data: commentData,
			error: function(XMLHttpRequest, textStatus, errorThrown){
				statusdiv.html('<p class="ajax-error" >You might have left one of the fields blank, or be posting too quickly</p>');
			},
			success: function(data, textStatus){
				// console.log( data );
				if(textStatus=="success") {
					statusdiv.html('<p class="ajax-success" >Thanks for your comment. We appreciate your response.</p>');

					app.appendComment( comment_author, comment );

					setTimeout( function() {
						$( ".io-modal-close" ).trigger( "click" );
					}, 1500 );
				} else {
					statusdiv.html('<p class="ajax-error" >Please wait a while before posting your next comment</p>');
					commentform.find('textarea[name=comment]').val('');
				}
			}
		});

		return false;

	});
	

	app.init();

	window.apppresser = app;

})(window, document, jQuery);