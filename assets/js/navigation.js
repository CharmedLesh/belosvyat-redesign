/**
 * Навігація теми «Білобережжя Святослава».
 *
 * Без jQuery. Стара тема покладалася на jQuery.browser, який видалено ще
 * у jQuery 1.9 — тому мобільного меню на сайті фактично не існувало.
 */
( function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

	/**
	 * Мобільне меню.
	 */
	function initMenu() {
		var toggle = document.getElementById( 'menu-toggle' );
		var nav = document.getElementById( 'primary-nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		function close() {
			toggle.setAttribute( 'aria-expanded', 'false' );
			document.body.classList.remove( 'menu-open' );
		}

		function open() {
			toggle.setAttribute( 'aria-expanded', 'true' );
			document.body.classList.add( 'menu-open' );
		}

		toggle.addEventListener( 'click', function () {
			if ( 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
				close();
			} else {
				open();
			}
		} );

		// Esc закриває меню й повертає фокус на кнопку.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key ) {
				return;
			}

			if ( 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
				close();
				toggle.focus();
			}
		} );

		// Утримуємо фокус усередині відкритого меню.
		nav.addEventListener( 'keydown', function ( event ) {
			if ( 'Tab' !== event.key || 'true' !== toggle.getAttribute( 'aria-expanded' ) ) {
				return;
			}

			var items = Array.prototype.filter.call(
				nav.querySelectorAll( FOCUSABLE ),
				function ( el ) {
					return null !== el.offsetParent;
				}
			);

			if ( ! items.length ) {
				return;
			}

			var first = items[ 0 ];
			var last = items[ items.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				toggle.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				toggle.focus();
			}
		} );

		// Клік поза меню закриває його.
		document.addEventListener( 'click', function ( event ) {
			if ( 'true' !== toggle.getAttribute( 'aria-expanded' ) ) {
				return;
			}

			if ( ! nav.contains( event.target ) && ! toggle.contains( event.target ) ) {
				close();
			}
		} );

		// Повернення на десктоп скидає стан меню.
		var desktop = window.matchMedia( '(min-width: 1024px)' );
		var onChange = function ( event ) {
			if ( event.matches ) {
				close();
			}
		};

		if ( desktop.addEventListener ) {
			desktop.addEventListener( 'change', onChange );
		} else if ( desktop.addListener ) {
			desktop.addListener( onChange );
		}
	}

	/**
	 * Кнопки розгортання підменю.
	 */
	function initSubmenus() {
		var toggles = document.querySelectorAll( '.submenu-toggle' );

		Array.prototype.forEach.call( toggles, function ( button ) {
			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				var item = button.closest( 'li' );
				var expanded = 'true' === button.getAttribute( 'aria-expanded' );

				// Закриваємо сусідні підменю того ж рівня.
				if ( ! expanded && item && item.parentNode ) {
					Array.prototype.forEach.call( item.parentNode.children, function ( sibling ) {
						if ( sibling === item ) {
							return;
						}

						var siblingToggle = sibling.querySelector( ':scope > .submenu-toggle' );

						if ( siblingToggle ) {
							siblingToggle.setAttribute( 'aria-expanded', 'false' );
							sibling.classList.remove( 'is-open' );
						}
					} );
				}

				button.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );

				if ( item ) {
					item.classList.toggle( 'is-open', ! expanded );
				}
			} );
		} );
	}

	/**
	 * Панель пошуку в шапці.
	 */
	function initSearch() {
		var toggle = document.getElementById( 'search-toggle' );
		var panel = document.getElementById( 'site-search' );

		if ( ! toggle || ! panel ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var expanded = 'true' === toggle.getAttribute( 'aria-expanded' );

			toggle.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			panel.hidden = expanded;

			if ( ! expanded ) {
				var field = panel.querySelector( 'input[type="search"]' );

				if ( field ) {
					field.focus();
				}
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! panel.hidden ) {
				panel.hidden = true;
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	/**
	 * Липка шапка та кнопка «Нагору».
	 */
	function initScroll() {
		var header = document.getElementById( 'site-header' );
		var toTop = document.getElementById( 'to-top' );
		var ticking = false;

		function update() {
			var y = window.pageYOffset;

			if ( header ) {
				header.classList.toggle( 'is-stuck', y > 24 );
			}

			if ( toTop ) {
				toTop.hidden = y < 600;
			}

			ticking = false;
		}

		window.addEventListener(
			'scroll',
			function () {
				if ( ! ticking ) {
					window.requestAnimationFrame( update );
					ticking = true;
				}
			},
			{ passive: true }
		);

		if ( toTop ) {
			toTop.addEventListener( 'click', function () {
				var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

				window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );
			} );
		}

		update();
	}

	function init() {
		initMenu();
		initSubmenus();
		initSearch();
		initScroll();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
