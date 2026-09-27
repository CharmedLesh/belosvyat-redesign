/**
 * Карусель галереї та перегляд зображення поверх сторінки.
 *
 * Без jQuery та без сторонніх бібліотек. Прокрутка каруселі — нативна
 * (scroll-snap), тож без JS вона лишається гортабельною, а клік по слайду
 * відкриває файл напряму.
 */
( function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled])';

	/**
	 * Створює накладку для перегляду зображень.
	 *
	 * @param {Object} labels Підписи з data-атрибутів каруселі.
	 * @return {Object} API накладки.
	 */
	function createLightbox( labels ) {
		var overlay = document.createElement( 'div' );
		var lastFocused = null;
		var items = [];
		var current = 0;

		overlay.className = 'lightbox';
		overlay.setAttribute( 'role', 'dialog' );
		overlay.setAttribute( 'aria-modal', 'true' );
		overlay.setAttribute( 'aria-label', labels.image );
		overlay.hidden = true;

		overlay.innerHTML =
			'<div class="lightbox__dialog">' +
				'<figure class="lightbox__figure"><img class="lightbox__image" src="" alt="" /></figure>' +
				'<p class="lightbox__counter" aria-live="polite"></p>' +
				'<button class="lightbox__close" type="button"><span aria-hidden="true">&times;</span></button>' +
				'<button class="lightbox__nav lightbox__nav--prev" type="button"><span aria-hidden="true">&lsaquo;</span></button>' +
				'<button class="lightbox__nav lightbox__nav--next" type="button"><span aria-hidden="true">&rsaquo;</span></button>' +
			'</div>';

		var image = overlay.querySelector( '.lightbox__image' );
		var counter = overlay.querySelector( '.lightbox__counter' );
		var closeButton = overlay.querySelector( '.lightbox__close' );
		var prevButton = overlay.querySelector( '.lightbox__nav--prev' );
		var nextButton = overlay.querySelector( '.lightbox__nav--next' );

		closeButton.setAttribute( 'aria-label', labels.close );
		prevButton.setAttribute( 'aria-label', labels.prev );
		nextButton.setAttribute( 'aria-label', labels.next );

		document.body.appendChild( overlay );

		function show( index ) {
			current = ( index + items.length ) % items.length;

			var item = items[ current ];

			image.src = item.href;
			image.alt = item.alt;
			counter.textContent = ( current + 1 ) + ' / ' + items.length;

			var single = items.length < 2;

			prevButton.hidden = single;
			nextButton.hidden = single;
			counter.hidden = single;
		}

		function close() {
			overlay.hidden = true;
			document.body.classList.remove( 'lightbox-open' );
			image.src = '';

			if ( lastFocused ) {
				lastFocused.focus();
				lastFocused = null;
			}
		}

		function open( list, index, trigger ) {
			items = list;
			lastFocused = trigger || null;

			show( index );

			overlay.hidden = false;
			document.body.classList.add( 'lightbox-open' );
			closeButton.focus();
		}

		closeButton.addEventListener( 'click', close );

		prevButton.addEventListener( 'click', function () {
			show( current - 1 );
		} );

		nextButton.addEventListener( 'click', function () {
			show( current + 1 );
		} );

		// Клік по тлу закриває, клік по самому зображенню — ні.
		overlay.addEventListener( 'click', function ( event ) {
			if ( event.target === overlay || event.target.classList.contains( 'lightbox__dialog' ) || event.target.classList.contains( 'lightbox__figure' ) ) {
				close();
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( overlay.hidden ) {
				return;
			}

			if ( 'Escape' === event.key ) {
				close();
				return;
			}

			if ( 'ArrowLeft' === event.key ) {
				show( current - 1 );
				return;
			}

			if ( 'ArrowRight' === event.key ) {
				show( current + 1 );
				return;
			}

			// Фокус не має тікати з накладки.
			if ( 'Tab' === event.key ) {
				var focusable = Array.prototype.filter.call(
					overlay.querySelectorAll( FOCUSABLE ),
					function ( node ) {
						return ! node.hidden;
					}
				);

				if ( ! focusable.length ) {
					return;
				}

				var first = focusable[ 0 ];
				var last = focusable[ focusable.length - 1 ];

				if ( event.shiftKey && document.activeElement === first ) {
					event.preventDefault();
					last.focus();
				} else if ( ! event.shiftKey && document.activeElement === last ) {
					event.preventDefault();
					first.focus();
				}
			}
		} );

		return { open: open };
	}

	/**
	 * Одна карусель.
	 *
	 * Нескінченність зроблено клонами: перед першим слайдом стоїть копія
	 * останнього, після останнього — копія першого. Тому гортання «через край»
	 * анімується так само, як звичайне, а коли прокрутка спиняється на клоні,
	 * позиція миттєво переставляється на його оригінал. Кадр із клоном і кадр
	 * з оригіналом однакові, тож підміни не видно.
	 *
	 * @param {Element} root     Контейнер каруселі.
	 * @param {Object}  lightbox API накладки.
	 */
	function initCarousel( root, lightbox ) {
		var track = root.querySelector( '[data-carousel-track]' );
		var prev = root.querySelector( '[data-carousel-prev]' );
		var next = root.querySelector( '[data-carousel-next]' );
		var slides = track ? Array.prototype.slice.call( track.children ) : [];

		if ( ! track || slides.length < 2 ) {
			return;
		}

		root.classList.add( 'carousel--ready' );

		[ prev, next ].forEach( function ( node ) {
			if ( node ) {
				node.hidden = false;
			}
		} );

		// Дані для накладки збираємо до клонування — лише справжні слайди.
		var items = slides.map( function ( slide ) {
			var link = slide.querySelector( '.carousel__link' );
			var img = slide.querySelector( 'img' );

			return {
				href: link ? link.getAttribute( 'href' ) : '',
				alt: img ? img.getAttribute( 'alt' ) || '' : ''
			};
		} );

		/**
		 * Клон слайда. Він службовий, тож ховаємо його від читалок і з таб-обходу.
		 *
		 * @param {Element} slide Слайд-оригінал.
		 * @return {Element} Клон.
		 */
		function cloneSlide( slide ) {
			var clone = slide.cloneNode( true );
			var link = clone.querySelector( '.carousel__link' );

			clone.setAttribute( 'aria-hidden', 'true' );

			if ( link ) {
				link.setAttribute( 'tabindex', '-1' );
			}

			return clone;
		}

		track.insertBefore( cloneSlide( slides[ slides.length - 1 ] ), slides[ 0 ] );
		track.appendChild( cloneSlide( slides[ 0 ] ) );

		var cells = Array.prototype.slice.call( track.children );
		var first = 1;
		var last = cells.length - 2;
		var motionOk = ! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );

		function offset( i ) {
			return cells[ i ].offsetLeft - track.offsetLeft;
		}

		function currentCell() {
			var position = track.scrollLeft;
			var closest = first;
			var distance = Infinity;

			cells.forEach( function ( cell, i ) {
				var delta = Math.abs( offset( i ) - position );

				if ( delta < distance ) {
					distance = delta;
					closest = i;
				}
			} );

			return closest;
		}

		function goTo( i, smooth ) {
			track.scrollTo( {
				left: offset( i ),
				behavior: smooth && motionOk ? 'smooth' : 'auto'
			} );
		}

		function step( delta ) {
			goTo( currentCell() + delta, true );
		}

		/**
		 * Прокрутка спинилася: якщо стоїмо на клоні — переставляємо на оригінал.
		 * Це стосується і кнопок, і свайпу за край.
		 */
		function normalize() {
			var i = currentCell();

			if ( i < first ) {
				goTo( last, false );
			} else if ( i > last ) {
				goTo( first, false );
			}
		}

		var idle = null;

		track.addEventListener( 'scroll', function () {
			window.clearTimeout( idle );

			// 120 мс тиші = анімація (чи свайп) завершилася.
			idle = window.setTimeout( normalize, 120 );
		} );

		prev.addEventListener( 'click', function () {
			step( -1 );
		} );

		next.addEventListener( 'click', function () {
			step( 1 );
		} );

		// Стрілки з клавіатури, коли фокус усередині каруселі.
		root.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				step( -1 );
			} else if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				step( 1 );
			}
		} );

		// Клік по слайду (зокрема по клону) відкриває оригінальне зображення.
		cells.forEach( function ( cell, i ) {
			var link = cell.querySelector( '.carousel__link' );

			if ( ! link ) {
				return;
			}

			var index = ( i - first + items.length ) % items.length;

			link.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				lightbox.open( items, index, link );
			} );
		} );

		// Старт — на першому справжньому слайді, а не на клоні перед ним.
		goTo( first, false );
	}

	function init() {
		var carousels = document.querySelectorAll( '[data-carousel]' );

		if ( ! carousels.length ) {
			return;
		}

		var first = carousels[ 0 ];
		var lightbox = createLightbox( {
			close: first.getAttribute( 'data-label-close' ) || 'Close',
			prev: first.getAttribute( 'data-label-prev' ) || 'Previous',
			next: first.getAttribute( 'data-label-next' ) || 'Next',
			image: first.getAttribute( 'data-label-image' ) || 'Image'
		} );

		Array.prototype.forEach.call( carousels, function ( root ) {
			initCarousel( root, lightbox );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
