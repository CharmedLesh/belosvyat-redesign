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
				'<p class="lightbox__caption"></p>' +
				'<p class="lightbox__counter" aria-live="polite"></p>' +
				'<button class="lightbox__close" type="button"><span aria-hidden="true">&times;</span></button>' +
				'<button class="lightbox__nav lightbox__nav--prev" type="button"><span aria-hidden="true">&lsaquo;</span></button>' +
				'<button class="lightbox__nav lightbox__nav--next" type="button"><span aria-hidden="true">&rsaquo;</span></button>' +
			'</div>';

		var image = overlay.querySelector( '.lightbox__image' );
		var caption = overlay.querySelector( '.lightbox__caption' );
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
			caption.textContent = item.caption || '';
			caption.hidden = ! item.caption;
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
	 * Дані одного зображення для накладки.
	 *
	 * Підпис шукаємо в межах власної figure: у панелі мозаїки в одному слайді
	 * лежить кілька зображень, і підпис сусіда брати не можна.
	 *
	 * @param {Element} link Посилання на зображення.
	 * @return {Object} href, alt, caption.
	 */
	function readItem( link ) {
		var scope = link.closest( '.mosaic__figure, .carousel__slide' ) || link;
		var img = link.querySelector( 'img' ) || scope.querySelector( 'img' );
		var caption = scope.querySelector( '.mosaic__caption' );

		return {
			href: link.getAttribute( 'href' ),
			alt: img ? img.getAttribute( 'alt' ) || '' : '',
			caption: caption ? caption.textContent.trim() : ''
		};
	}

	/**
	 * Збирає всі зображення контейнера й нумерує посилання.
	 *
	 * Номер кладемо в атрибут ще до клонування слайдів: клон успадкує його,
	 * тож клік по клону відкриє те саме зображення, що й по оригіналі.
	 *
	 * @param {Element} container Доріжка каруселі або список мозаїки.
	 * @return {Array} Дані зображень у порядку появи.
	 */
	function collectItems( container ) {
		var links = Array.prototype.slice.call(
			container.querySelectorAll( '.carousel__link, .mosaic__link' )
		);

		return links.map( function ( link, i ) {
			link.setAttribute( 'data-lightbox-index', i );

			return readItem( link );
		} );
	}

	/**
	 * Один делегований обробник замість обробника на кожному посиланні.
	 *
	 * Посилання в підписах не мають data-lightbox-index, тому closest() для них
	 * дає null — і вікіпедійні посилання працюють як звичайні.
	 *
	 * @param {Element} container Доріжка каруселі або список мозаїки.
	 * @param {Array}   items     Дані зображень.
	 * @param {Object}  lightbox  API накладки.
	 */
	function bindLightbox( container, items, lightbox ) {
		container.addEventListener( 'click', function ( event ) {
			var link = event.target.closest ? event.target.closest( '[data-lightbox-index]' ) : null;

			if ( ! link || ! container.contains( link ) ) {
				return;
			}

			event.preventDefault();
			lightbox.open( items, parseInt( link.getAttribute( 'data-lightbox-index' ), 10 ) || 0, link );
		} );
	}

	/**
	 * Мозаїка: сітку будує CSS, звідси лише перегляд поверх сторінки.
	 *
	 * @param {Element} root     Контейнер мозаїки.
	 * @param {Object}  lightbox API накладки.
	 */
	function initMosaic( root, lightbox ) {
		var items = collectItems( root );

		if ( items.length ) {
			bindLightbox( root, items, lightbox );
		}
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

		if ( ! track ) {
			return;
		}

		var prev = root.querySelector( '[data-carousel-prev]' );
		var next = root.querySelector( '[data-carousel-next]' );
		var slides = Array.prototype.slice.call( track.children );

		// Нумеруємо й прив'язуємо до клонування; у слайді-панелі зображень
		// кілька, тому збираємо саме посилання, а не слайди.
		var items = collectItems( track );

		if ( items.length ) {
			bindLightbox( track, items, lightbox );
		}

		if ( slides.length < 2 ) {
			return;
		}

		root.classList.add( 'carousel--ready' );

		[ prev, next ].forEach( function ( node ) {
			if ( node ) {
				node.hidden = false;
			}
		} );

		/**
		 * Клон слайда. Він службовий, тож ховаємо його від читалок і з таб-обходу.
		 *
		 * @param {Element} slide Слайд-оригінал.
		 * @return {Element} Клон.
		 */
		function cloneSlide( slide ) {
			var clone = slide.cloneNode( true );

			clone.setAttribute( 'aria-hidden', 'true' );

			Array.prototype.forEach.call( clone.querySelectorAll( 'a[href]' ), function ( link ) {
				link.setAttribute( 'tabindex', '-1' );
			} );

			return clone;
		}

		// Клонуємо весь набір з обох боків, а не по одному слайду: у стрічці на
		// всю ширину екрана видно кілька слайдів одразу, тож за одним клоном
		// зяяла б порожнеча. Для каруселі з одним слайдом у кадрі це нічого не
		// змінює — просто клонів більше.
		var leading = document.createDocumentFragment();
		var trailing = document.createDocumentFragment();

		slides.forEach( function ( slide ) {
			leading.appendChild( cloneSlide( slide ) );
			trailing.appendChild( cloneSlide( slide ) );
		} );

		track.insertBefore( leading, slides[ 0 ] );
		track.appendChild( trailing );

		var cells = Array.prototype.slice.call( track.children );
		var setSize = slides.length;
		var first = setSize;
		var last = setSize * 2 - 1;
		var motionOk = ! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );

		// Куди саме «прилипає» слайд, вирішує CSS: до початку доріжки чи до її
		// центру. Читаємо це значення, щоб кнопки вели туди ж, куди й свайп.
		var snapCentred = 0 === ( getComputedStyle( cells[ 0 ] ).scrollSnapAlign || '' ).indexOf( 'center' );

		function offset( i ) {
			return cells[ i ].offsetLeft - track.offsetLeft;
		}

		/**
		 * Позиція прокрутки, за якої слайд опиняється на своєму місці.
		 *
		 * @param {number} i Номер комірки.
		 * @return {number} Значення scrollLeft.
		 */
		function target( i ) {
			if ( ! snapCentred ) {
				return offset( i );
			}

			return offset( i ) - ( track.clientWidth - cells[ i ].offsetWidth ) / 2;
		}

		function currentCell() {
			var position = track.scrollLeft;
			var closest = first;
			var distance = Infinity;

			cells.forEach( function ( cell, i ) {
				var delta = Math.abs( target( i ) - position );

				if ( delta < distance ) {
					distance = delta;
					closest = i;
				}
			} );

			return closest;
		}

		function goTo( i, smooth ) {
			track.scrollTo( {
				left: target( i ),
				behavior: smooth && motionOk ? 'smooth' : 'auto'
			} );
		}

		function step( delta ) {
			goTo( currentCell() + delta, true );
		}

		/**
		 * Ширина одного набору слайдів у пікселях.
		 *
		 * @return {number}
		 */
		function setWidth() {
			return target( first + setSize ) - target( first );
		}

		/**
		 * Прокрутка спинилася: якщо зайшли на клони — зсуваємо позицію рівно на
		 * один набір. Зсув піксель у піксель, тож кадр не смикається навіть
		 * тоді, коли в кадрі кілька слайдів.
		 */
		function normalize() {
			var i = currentCell();

			if ( i < first ) {
				track.scrollLeft += setWidth();
			} else if ( i > last ) {
				track.scrollLeft -= setWidth();
			}
		}

		// --- Автопрокрутка -------------------------------------------------
		// Крок раз на data-carousel-autoplay мілісекунд, поки відвідувач не
		// чіпає стрічку. Будь-яка взаємодія обнуляє відлік, і рахунок
		// починається наново лише після того, як взаємодія скінчилася.
		var autoplayDelay = parseInt( root.getAttribute( 'data-carousel-autoplay' ), 10 ) || 0;
		var autoplayTimer = null;
		var hovered = false;
		var pressed = false;
		// Прокрутку, яку почали ми самі, не вважаємо взаємодією.
		var selfScroll = false;

		function autoplayStop() {
			if ( autoplayTimer ) {
				window.clearInterval( autoplayTimer );
				autoplayTimer = null;
			}
		}

		/**
		 * Перезапускає відлік з нуля — або лишає стрічку в спокої, поки на ній
		 * курсор чи палець, поки вкладку сховано або поки відкрита накладка.
		 */
		function autoplayReset() {
			autoplayStop();

			if ( ! autoplayDelay || ! motionOk || hovered || pressed ) {
				return;
			}

			autoplayTimer = window.setInterval( function () {
				if ( document.hidden || document.body.classList.contains( 'lightbox-open' ) ) {
					return;
				}

				selfScroll = true;
				step( 1 );
			}, autoplayDelay );
		}

		if ( autoplayDelay && motionOk ) {
			root.addEventListener( 'mouseenter', function () {
				hovered = true;
				autoplayStop();
			} );

			root.addEventListener( 'mouseleave', function () {
				hovered = false;
				autoplayReset();
			} );

			root.addEventListener( 'pointerdown', function () {
				pressed = true;
				autoplayStop();
			} );

			// Палець могли відпустити вже за межами стрічки.
			document.addEventListener( 'pointerup', function () {
				if ( pressed ) {
					pressed = false;
					autoplayReset();
				}
			} );

			document.addEventListener( 'pointercancel', function () {
				if ( pressed ) {
					pressed = false;
					autoplayReset();
				}
			} );

			// Повернення фокуса з накладки теж має перезапустити відлік.
			root.addEventListener( 'focusin', autoplayReset );

			document.addEventListener( 'visibilitychange', function () {
				if ( document.hidden ) {
					autoplayStop();
				} else {
					autoplayReset();
				}
			} );
		}

		var idle = null;

		track.addEventListener( 'scroll', function () {
			window.clearTimeout( idle );

			// Гортання пальцем чи трекпадом — це взаємодія: відлік з нуля.
			if ( ! selfScroll ) {
				autoplayReset();
			}

			// 120 мс тиші = анімація (чи свайп) завершилася.
			idle = window.setTimeout( function () {
				selfScroll = false;
				normalize();
			}, 120 );
		} );

		prev.addEventListener( 'click', function () {
			step( -1 );
			selfScroll = true;
			autoplayReset();
		} );

		next.addEventListener( 'click', function () {
			step( 1 );
			selfScroll = true;
			autoplayReset();
		} );

		// Стрілки з клавіатури, коли фокус усередині каруселі.
		root.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				step( -1 );
			} else if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				step( 1 );
			} else {
				return;
			}

			selfScroll = true;
			autoplayReset();
		} );

		// Старт — на першому справжньому слайді, а не на клоні перед ним.
		goTo( first, false );

		autoplayReset();
	}

	function init() {
		var galleries = Array.prototype.slice.call( document.querySelectorAll( '[data-carousel], [data-mosaic]' ) );

		if ( ! galleries.length ) {
			return;
		}

		var first = galleries[ 0 ];
		var lightbox = createLightbox( {
			close: first.getAttribute( 'data-label-close' ) || 'Close',
			prev: first.getAttribute( 'data-label-prev' ) || 'Previous',
			next: first.getAttribute( 'data-label-next' ) || 'Next',
			image: first.getAttribute( 'data-label-image' ) || 'Image'
		} );

		galleries.forEach( function ( root ) {
			if ( root.hasAttribute( 'data-mosaic' ) ) {
				initMosaic( root, lightbox );
			} else {
				initCarousel( root, lightbox );
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
