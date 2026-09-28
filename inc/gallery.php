<?php
/**
 * Галереї: карусель і мозаїка.
 *
 * Старі сторінки-галереї — це просто низка мініатюр 150×150, кожна в посиланні
 * на файл; такі збираємо в карусель. Новіші сторінки зроблено блоком
 * «Галерея» з підписами — їх показуємо мозаїкою, де розмір комірки випливає
 * з розмірів самого зображення. Клік у будь-якому випадку відкриває картинку
 * поверх сторінки (див. assets/js/gallery.js).
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Слаги сторінок, де низку зображень треба збирати в карусель.
 *
 * Список, а не автовизначення: інакше поведінка сторінки мовчки змінювалася б
 * від правок тексту, а одиничні картинки в записах ставали б «каруселями з
 * одного слайда».
 *
 * @return array
 */
function belosvyat_gallery_pages() {
	/**
	 * Дозволяє додати сторінки без правки теми.
	 *
	 * @param array $slugs Слаги сторінок.
	 */
	return array_filter( (array) apply_filters( 'belosvyat_gallery_pages', array( 'flora' ) ) );
}

/**
 * Чи є поточна сторінка галереєю.
 *
 * @return bool
 */
function belosvyat_is_gallery_page() {
	$pages = belosvyat_gallery_pages();

	return ! empty( $pages ) && is_page( $pages );
}

/**
 * Регулярка одного зображення-посилання.
 *
 * @return string
 */
function belosvyat_gallery_link_pattern() {
	return '<a\b[^>]*href=["\'][^"\']+\.(?:jpe?g|png|gif|webp)["\'][^>]*>\s*<img\b[^>]*>\s*</a>';
}

/**
 * Додає клас до готового тега <img> зі старого контенту.
 *
 * @param string $img   HTML тега <img>.
 * @param string $class Клас, який треба додати.
 * @return string
 */
function belosvyat_add_img_class( $img, $class ) {
	if ( preg_match( '#\sclass=["\']([^"\']*)["\']#i', $img, $matches ) ) {
		return str_replace( $matches[0], ' class="' . esc_attr( $matches[1] . ' ' . $class ) . '"', $img );
	}

	return preg_replace( '#<img\b#i', '<img class="' . esc_attr( $class ) . '"', $img, 1 );
}

/**
 * Зображення одного слайда каруселі з медіатеки.
 *
 * @param int $attachment_id ID вкладення.
 * @param int $index         Порядковий номер з нуля (перший вантажимо одразу).
 * @return string
 */
function belosvyat_carousel_image( $attachment_id, $index ) {
	return wp_get_attachment_image(
		$attachment_id,
		'large',
		false,
		array(
			'class'    => 'carousel__image',
			'loading'  => $index ? 'lazy' : 'eager',
			'decoding' => 'async',
		)
	);
}

/**
 * Слайд каруселі із зображенням.
 *
 * @param string $image HTML тега <img>.
 * @param string $full  Адреса оригіналу.
 * @param int    $index Порядковий номер з нуля.
 * @param int    $total Загальна кількість слайдів.
 * @return string
 */
function belosvyat_carousel_slide( $image, $full, $index, $total ) {
	return sprintf(
		'<li class="carousel__slide"><a class="carousel__link" href="%1$s" aria-label="%2$s">%3$s</a></li>',
		esc_url( $full ),
		esc_attr(
			sprintf(
				/* translators: 1: номер зображення, 2: загальна кількість. */
				__( 'Відкрити зображення %1$d з %2$d', 'belosvyat' ),
				$index + 1,
				$total
			)
		),
		$image // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML зображення.
	);
}

/**
 * Слайд каруселі з довільним вмістом (наприклад, панеллю мозаїки).
 *
 * @param string $inner HTML усередині слайда.
 * @return string
 */
function belosvyat_carousel_raw_slide( $inner ) {
	return '<li class="carousel__slide">' . $inner . '</li>';
}

/**
 * Обгортка каруселі з кнопками гортання.
 *
 * @param string $slides        HTML слайдів.
 * @param string $extra_classes Додаткові класи кореневого елемента.
 * @param string $extra_attrs   Додаткові атрибути кореня (напр. автопрокрутка).
 * @return string
 */
function belosvyat_carousel_markup( $slides, $extra_classes = '', $extra_attrs = '' ) {
	return sprintf(
		'<div class="carousel%5$s" data-carousel %1$s%6$s>
	<ul class="carousel__track" data-carousel-track>%2$s</ul>
	<button class="carousel__nav carousel__nav--prev" type="button" data-carousel-prev aria-label="%3$s" hidden><span aria-hidden="true">&lsaquo;</span></button>
	<button class="carousel__nav carousel__nav--next" type="button" data-carousel-next aria-label="%4$s" hidden><span aria-hidden="true">&rsaquo;</span></button>
</div>',
		belosvyat_lightbox_label_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Атрибути екрановані.
		$slides, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складено вище.
		esc_attr__( 'Попереднє зображення', 'belosvyat' ),
		esc_attr__( 'Наступне зображення', 'belosvyat' ),
		$extra_classes ? ' ' . esc_attr( $extra_classes ) : '',
		$extra_attrs ? ' ' . $extra_attrs : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Атрибути складає тема.
	);
}

/**
 * Збирає карусель з низки зображень-посилань.
 *
 * @param array $matches Результат preg_replace_callback.
 * @return string
 */
function belosvyat_carousel_from_run( $matches ) {
	$run = $matches[0];

	$found = preg_match_all(
		'#<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>\s*(<img\b[^>]*>)\s*</a>#is',
		$run,
		$links,
		PREG_SET_ORDER
	);

	if ( ! $found ) {
		return $run;
	}

	$total  = count( $links );
	$slides = '';

	foreach ( $links as $index => $link ) {
		$full = $link[1];
		$img  = $link[2];

		// Старий редактор лишає в класі ID вкладення — беремо з медіатеки
		// нормальний розмір замість мініатюри 150×150.
		$attachment_id = preg_match( '#wp-image-(\d+)#', $img, $id ) ? (int) $id[1] : 0;

		if ( $attachment_id ) {
			$image = belosvyat_carousel_image( $attachment_id, $index );

			$original = wp_get_attachment_image_url( $attachment_id, 'full' );

			if ( $original ) {
				$full = $original;
			}
		} else {
			$image = belosvyat_add_img_class( $img, 'carousel__image' );
		}

		$slides .= belosvyat_carousel_slide( $image, $full, $index, $total );
	}

	return belosvyat_carousel_markup( $slides );
}

/**
 * Карусель із довільного впорядкованого списку вкладень.
 *
 * Потрібна головній сторінці: там немає контенту з посиланнями-зображеннями,
 * лише список ID, зібраний зі сторінки-джерела.
 *
 * @param array  $ids           ID вкладень у потрібному порядку.
 * @param string $extra_classes Додаткові класи кореня (напр. carousel--rail).
 * @param string $extra_attrs   Додаткові атрибути кореня (напр. автопрокрутка).
 * @return string
 */
function belosvyat_carousel_from_ids( $ids, $extra_classes = '', $extra_attrs = '' ) {
	$ids   = array_values( array_filter( array_map( 'intval', (array) $ids ) ) );
	$total = count( $ids );

	if ( ! $total ) {
		return '';
	}

	// Один запит замість двох на кожне зображення.
	_prime_post_caches( $ids, false, true );

	$slides = '';

	foreach ( $ids as $index => $attachment_id ) {
		$image = belosvyat_carousel_image( $attachment_id, $index );

		if ( ! $image ) {
			continue;
		}

		$full = wp_get_attachment_image_url( $attachment_id, 'full' );

		$slides .= belosvyat_carousel_slide( $image, $full ? $full : '', $index, $total );
	}

	return '' === $slides ? '' : belosvyat_carousel_markup( $slides, $extra_classes, $extra_attrs );
}

/**
 * Замінює низки зображень-посилань на карусель.
 *
 * @param string $content HTML контенту.
 * @return string
 */
function belosvyat_build_carousels( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() || ! belosvyat_is_gallery_page() ) {
		return $content;
	}

	$link = belosvyat_gallery_link_pattern();

	// Між картинками у старому контенті трапляються пробіли, &nbsp;, <br> та
	// уривки <p> — усе це вважаємо «нічим».
	$gap = '(?:\s|&nbsp;|\xc2\xa0|<br\s*/?>|</?p[^>]*>|<a\b[^>]*>\s*</a>)*';

	$result = preg_replace_callback(
		'#' . $link . '(?:' . $gap . $link . ')+#is',
		'belosvyat_carousel_from_run',
		$content
	);

	// На битому UTF-8 чи надто складному вмісті preg_* може повернути null —
	// тоді лишаємо контент як був.
	return null === $result ? $content : $result;
}
add_filter( 'the_content', 'belosvyat_build_carousels', 21 );

/**
 * Слаги сторінок, де блочну галерею показуємо мозаїкою.
 *
 * @return array
 */
function belosvyat_mosaic_pages() {
	/**
	 * Дозволяє додати сторінки без правки теми.
	 *
	 * @param array $slugs Слаги сторінок.
	 */
	return array_filter( (array) apply_filters( 'belosvyat_mosaic_pages', array( 'fauna' ) ) );
}

/**
 * Чи показувати галерею мозаїкою на поточній сторінці.
 *
 * @return bool
 */
function belosvyat_is_mosaic_page() {
	$pages = belosvyat_mosaic_pages();

	return ! empty( $pages ) && is_page( $pages );
}

/**
 * Розмір комірки за пропорціями та розміром оригіналу.
 *
 * Саме тому мозаїка не потребує ручного налаштування: нове зображення дістає
 * свою комірку з власних розмірів у медіатеці.
 *
 * @param int $width  Ширина оригіналу.
 * @param int $height Висота оригіналу.
 * @return string Клас-модифікатор або порожній рядок для звичайної комірки.
 */
function belosvyat_mosaic_size_class( $width, $height ) {
	if ( $width < 1 || $height < 1 ) {
		return '';
	}

	$ratio = $width / $height;

	// Вертикальне — висока комірка.
	if ( $ratio <= 0.8 ) {
		return 'mosaic__item--tall';
	}

	// Великий оригінал, близький до квадрата чи 4:3 — акцентна комірка 2×2.
	if ( min( $width, $height ) >= 1500 && $ratio < 1.7 ) {
		return 'mosaic__item--feature';
	}

	// Помітно горизонтальне — широка комірка.
	if ( $ratio >= 1.45 ) {
		return 'mosaic__item--wide';
	}

	return '';
}

/**
 * Підписи для накладки перегляду (спільні для каруселі й мозаїки).
 *
 * @return string Рядок data-атрибутів.
 */
function belosvyat_lightbox_label_attributes() {
	return sprintf(
		'data-label-close="%1$s" data-label-prev="%2$s" data-label-next="%3$s" data-label-image="%4$s"',
		esc_attr__( 'Закрити', 'belosvyat' ),
		esc_attr__( 'Попереднє зображення', 'belosvyat' ),
		esc_attr__( 'Наступне зображення', 'belosvyat' ),
		esc_attr__( 'Зображення', 'belosvyat' )
	);
}

/**
 * Скільки комірок сітки займає елемент мозаїки.
 *
 * @param string $size_class Клас-модифікатор від belosvyat_mosaic_size_class().
 * @return array Ширина та висота в комірках.
 */
function belosvyat_mosaic_item_span( $size_class ) {
	if ( 'mosaic__item--feature' === $size_class ) {
		return array( 2, 2 );
	}

	if ( 'mosaic__item--wide' === $size_class ) {
		return array( 2, 1 );
	}

	if ( 'mosaic__item--tall' === $size_class ) {
		return array( 1, 2 );
	}

	return array( 1, 1 );
}

/**
 * Дані однієї комірки мозаїки з блока core/image.
 *
 * Окремо від розмітки, бо пакувальнику панелей потрібен розмір комірки ще до
 * того, як вона буде виведена.
 *
 * @param array $block Внутрішній блок галереї.
 * @return array|null Ключі image, size, caption, full, label — або null.
 */
function belosvyat_mosaic_item_data( $block ) {
	$attachment_id = isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : 0;

	if ( ! $attachment_id ) {
		return null;
	}

	$image = wp_get_attachment_image(
		$attachment_id,
		'medium_large',
		false,
		array(
			'class'    => 'mosaic__image',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	if ( ! $image ) {
		return null;
	}

	$meta = wp_get_attachment_metadata( $attachment_id );
	$size = belosvyat_mosaic_size_class(
		isset( $meta['width'] ) ? (int) $meta['width'] : 0,
		isset( $meta['height'] ) ? (int) $meta['height'] : 0
	);

	$caption = '';

	if ( isset( $block['innerHTML'] ) && preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#is', $block['innerHTML'], $matches ) ) {
		$caption = trim( $matches[1] );
	}

	$title = trim( wp_strip_all_tags( $caption ) );
	$full  = wp_get_attachment_image_url( $attachment_id, 'full' );

	$label = $title
		? sprintf(
			/* translators: %s: назва з підпису. */
			__( 'Відкрити зображення: %s', 'belosvyat' ),
			$title
		)
		: __( 'Відкрити зображення', 'belosvyat' );

	return array(
		'image'   => $image,
		'size'    => $size,
		'caption' => $caption,
		'full'    => $full ? $full : '',
		'label'   => $label,
	);
}

/**
 * Розмітка однієї комірки мозаїки.
 *
 * @param array  $data  Дані від belosvyat_mosaic_item_data().
 * @param string $attrs Додаткові атрибути тега <li> (наприклад, grid-area).
 * @return string
 */
function belosvyat_mosaic_item_markup( $data, $attrs = '' ) {
	return sprintf(
		'<li class="mosaic__item %1$s"%6$s><figure class="mosaic__figure"><a class="mosaic__link" href="%2$s" aria-label="%3$s">%4$s</a>%5$s</figure></li>',
		esc_attr( $data['size'] ),
		esc_url( $data['full'] ),
		esc_attr( $data['label'] ),
		$data['image'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML зображення від WordPress.
		$data['caption'] ? '<figcaption class="mosaic__caption">' . wp_kses_post( $data['caption'] ) . '</figcaption>' : '',
		$attrs // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складається в belosvyat_carousel_from_panels().
	);
}

/**
 * Одна комірка мозаїки з блока core/image.
 *
 * @param array $block Внутрішній блок галереї.
 * @return string HTML комірки або порожній рядок.
 */
function belosvyat_mosaic_item( $block ) {
	$data = belosvyat_mosaic_item_data( $block );

	return $data ? belosvyat_mosaic_item_markup( $data ) : '';
}

/**
 * Показує блочну галерею мозаїкою.
 *
 * Працюємо на render_block, а не на the_content: тут галерея приходить окремо
 * й уже розібрана на блоки, тож ID вкладень беремо з атрибутів, а не з класів.
 *
 * @param string $block_content HTML блока.
 * @param array  $block         Розібраний блок.
 * @return string
 */
function belosvyat_render_gallery_as_mosaic( $block_content, $block ) {
	if ( is_admin() || empty( $block['blockName'] ) || 'core/gallery' !== $block['blockName'] ) {
		return $block_content;
	}

	if ( empty( $block['innerBlocks'] ) || ! belosvyat_is_mosaic_page() ) {
		return $block_content;
	}

	$items = '';

	foreach ( $block['innerBlocks'] as $inner ) {
		$items .= belosvyat_mosaic_item( $inner );
	}

	if ( '' === $items ) {
		return $block_content;
	}

	return sprintf(
		'<ul class="mosaic" data-mosaic %1$s>%2$s</ul>',
		belosvyat_lightbox_label_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Атрибути екрановані.
		$items // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складено вище.
	);
}
add_filter( 'render_block', 'belosvyat_render_gallery_as_mosaic', 10, 2 );

/**
 * Прибирає порожні абзаци-посилання на сторінках галерей.
 *
 * У старому контенті лишилися <p><a href="...jpg">&nbsp;</a></p> — раніше вони
 * губилися серед мініатюр, а над каруселлю чи мозаїкою впадають в око
 * підкресленим «хвостиком».
 *
 * @param string $content HTML контенту.
 * @return string
 */
function belosvyat_strip_empty_links( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( ! belosvyat_is_gallery_page() && ! belosvyat_is_mosaic_page() ) {
		return $content;
	}

	// Порожнеча: пробіли, &nbsp; або нерозривний пробіл у UTF-8.
	$blank = '(?:\s|&nbsp;|\xc2\xa0)*';

	$result = preg_replace(
		'#<p[^>]*>' . $blank . '(?:<a\b[^>]*>' . $blank . '</a>)?' . $blank . '</p>#i',
		'',
		$content
	);

	return null === $result ? $content : $result;
}
add_filter( 'the_content', 'belosvyat_strip_empty_links', 22 );

/**
 * Слаги сторінок-джерел для головної.
 *
 * Головна не тримає власних копій галерей: карусель рослин будується зі
 * сторінки «Флора», панелі тварин — з блочної галереї на «Фауні», а тур — з
 * iframe на сторінці ВІАР-туру. Правка цих сторінок одразу видно на головній.
 *
 * @return array Мапа «ключ => слаг».
 */
function belosvyat_front_source_slugs() {
	/**
	 * Дозволяє змінити сторінки-джерела без правки теми.
	 *
	 * @param array $slugs Мапа «ключ => слаг».
	 */
	return apply_filters(
		'belosvyat_front_source_slugs',
		array(
			'flora' => 'flora',
			'fauna' => 'fauna',
			'tour'  => 'viar-tur',
		)
	);
}

/**
 * Сторінка-джерело за ключем.
 *
 * @param string $key Ключ із belosvyat_front_source_slugs().
 * @return WP_Post|null
 */
function belosvyat_front_source_page( $key ) {
	$slugs = belosvyat_front_source_slugs();

	if ( empty( $slugs[ $key ] ) ) {
		return null;
	}

	$page = get_page_by_path( $slugs[ $key ] );

	return $page instanceof WP_Post ? $page : null;
}

/**
 * Усі блоки разом із вкладеними, одним пласким списком.
 *
 * @param array $blocks Результат parse_blocks().
 * @return array
 */
function belosvyat_flatten_blocks( $blocks ) {
	$flat = array();

	foreach ( (array) $blocks as $block ) {
		$flat[] = $block;

		if ( ! empty( $block['innerBlocks'] ) ) {
			$flat = array_merge( $flat, belosvyat_flatten_blocks( $block['innerBlocks'] ) );
		}
	}

	return $flat;
}

/**
 * ID зображень зі вмісту сторінки, у порядку появи.
 *
 * Розуміє і блоки (attrs.id, attrs.ids), і старий контент, де ID лишався
 * тільки в класі wp-image-NNN.
 *
 * @param int|WP_Post|string $page Сторінка, її ID або слаг.
 * @return array
 */
function belosvyat_page_image_ids( $page ) {
	$post = is_string( $page ) ? get_page_by_path( $page ) : get_post( $page );

	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	$ids = array();

	foreach ( belosvyat_flatten_blocks( parse_blocks( $post->post_content ) ) as $block ) {
		if ( ! empty( $block['attrs']['id'] ) ) {
			$ids[] = (int) $block['attrs']['id'];
		}

		if ( ! empty( $block['attrs']['ids'] ) ) {
			$ids = array_merge( $ids, array_map( 'intval', (array) $block['attrs']['ids'] ) );
		}
	}

	if ( preg_match_all( '#wp-image-(\d+)#', $post->post_content, $matches ) ) {
		$ids = array_merge( $ids, array_map( 'intval', $matches[1] ) );
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Внутрішні блоки першої блочної галереї на сторінці.
 *
 * Свідомо parse_blocks(), а не do_blocks(): фільтри render_block (зокрема наш
 * belosvyat_render_gallery_as_mosaic) не мають спрацьовувати для головної.
 *
 * @param int|WP_Post|string $page Сторінка, її ID або слаг.
 * @return array Блоки core/image.
 */
function belosvyat_page_gallery_blocks( $page ) {
	$post = is_string( $page ) ? get_page_by_path( $page ) : get_post( $page );

	if ( ! $post instanceof WP_Post ) {
		return array();
	}

	foreach ( belosvyat_flatten_blocks( parse_blocks( $post->post_content ) ) as $block ) {
		if ( isset( $block['blockName'] ) && 'core/gallery' === $block['blockName'] && ! empty( $block['innerBlocks'] ) ) {
			return $block['innerBlocks'];
		}
	}

	return array();
}

/**
 * Чи вміщається комірка в панель на позиції (x, y).
 *
 * @param array $grid Зайнятість сітки [y][x].
 * @param int   $x    Стовпець.
 * @param int   $y    Рядок.
 * @param int   $w    Ширина в комірках.
 * @param int   $h    Висота в комірках.
 * @param int   $cols Ширина панелі.
 * @param int   $rows Висота панелі.
 * @return bool
 */
function belosvyat_mosaic_cell_fits( $grid, $x, $y, $w, $h, $cols, $rows ) {
	if ( $x + $w > $cols || $y + $h > $rows ) {
		return false;
	}

	for ( $j = $y; $j < $y + $h; $j++ ) {
		for ( $i = $x; $i < $x + $w; $i++ ) {
			if ( ! empty( $grid[ $j ][ $i ] ) ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Розкладає комірки мозаїки по панелях фіксованого розміру.
 *
 * Дірок у панелі не лишаємо: якщо наступне за чергою зображення у вільне місце
 * не влазить, шукаємо серед подальших перше, яке влазить, і підтягуємо його
 * наперед. Тому порядок може трохи відрізнятися від галереї — це свідомий
 * обмін порядку на суцільну стрічку без прогалин. Панель закриваємо лише тоді,
 * коли жодне з решти зображень не вміщається в жодну вільну комірку.
 *
 * Найбільша комірка — 2×2, тож у порожню панель вона входить завжди, і цикл
 * не може зациклитися.
 *
 * Панелі перераховуються з живого вмісту сторінки «Фауна»: змінили галерею —
 * змінилася й розкладка на головній. Це навмисно, щоб не було двох джерел правди.
 *
 * @param array $blocks Блоки core/image у потрібному порядку.
 * @param int   $cols   Ширина панелі в комірках.
 * @param int   $rows   Висота панелі в комірках.
 * @return array Масив панелей; кожна — масив комірок із ключами data, x, y, w, h.
 */
function belosvyat_mosaic_panels( $blocks, $cols = 2, $rows = 2 ) {
	$blocks = (array) $blocks;

	if ( ! $blocks ) {
		return array();
	}

	$ids = array();

	foreach ( $blocks as $block ) {
		if ( ! empty( $block['attrs']['id'] ) ) {
			$ids[] = (int) $block['attrs']['id'];
		}
	}

	if ( $ids ) {
		_prime_post_caches( $ids, false, true );
	}

	// Готуємо чергу: дані комірки та її розмір у клітинках.
	$queue = array();

	foreach ( $blocks as $block ) {
		$data = belosvyat_mosaic_item_data( $block );

		if ( ! $data ) {
			continue;
		}

		$span = belosvyat_mosaic_item_span( $data['size'] );

		$queue[] = array(
			'data' => $data,
			'w'    => $span[0],
			'h'    => $span[1],
		);
	}

	$panels = array();

	while ( $queue ) {
		$panel = array();
		$grid  = array();

		while ( true ) {
			$chosen = null;

			// Перше вільне місце згори вниз і зліва направо, а для нього —
			// перше зображення з черги, яке туди влазить.
			for ( $y = 0; $y < $rows && null === $chosen; $y++ ) {
				for ( $x = 0; $x < $cols && null === $chosen; $x++ ) {
					if ( ! empty( $grid[ $y ][ $x ] ) ) {
						continue;
					}

					foreach ( $queue as $index => $item ) {
						if ( belosvyat_mosaic_cell_fits( $grid, $x, $y, $item['w'], $item['h'], $cols, $rows ) ) {
							$chosen = array( $index, $x, $y );
							break;
						}
					}
				}
			}

			if ( null === $chosen ) {
				break;
			}

			list( $index, $x, $y ) = $chosen;

			$item = $queue[ $index ];

			unset( $queue[ $index ] );
			$queue = array_values( $queue );

			for ( $j = $y; $j < $y + $item['h']; $j++ ) {
				for ( $i = $x; $i < $x + $item['w']; $i++ ) {
					$grid[ $j ][ $i ] = true;
				}
			}

			$panel[] = array(
				'data' => $item['data'],
				'x'    => $x,
				'y'    => $y,
				'w'    => $item['w'],
				'h'    => $item['h'],
			);
		}

		if ( ! $panel ) {
			// Захист від нескінченного циклу: у порожню панель має влазити
			// будь-яка комірка, але якщо раптом ні — просто зупиняємось.
			break;
		}

		$panels[] = $panel;
	}

	return $panels;
}

/**
 * Карусель, де кожен слайд — панель мозаїки.
 *
 * @param array  $panels      Панелі від belosvyat_mosaic_panels().
 * @param string $extra_attrs Додаткові атрибути кореня (напр. автопрокрутка).
 * @return string
 */
function belosvyat_carousel_from_panels( $panels, $extra_attrs = '' ) {
	$slides = '';

	foreach ( (array) $panels as $panel ) {
		$items = '';

		foreach ( $panel as $cell ) {
			// Місце комірки рахує PHP, а не grid-auto-flow: dense, — інакше
			// розкладка браузера могла б розійтися з нашою і панель виросла б
			// у четвертий рядок.
			$items .= belosvyat_mosaic_item_markup(
				$cell['data'],
				sprintf(
					' style="grid-area: %1$d / %2$d / span %3$d / span %4$d;"',
					(int) $cell['y'] + 1,
					(int) $cell['x'] + 1,
					(int) $cell['h'],
					(int) $cell['w']
				)
			);
		}

		if ( '' === $items ) {
			continue;
		}

		// Свідомо без data-mosaic: інакше init() у gallery.js підхопив би
		// панель як окрему мозаїку й повісив другий обробник із чужими номерами.
		$slides .= belosvyat_carousel_raw_slide( '<ul class="mosaic mosaic--panel">' . $items . '</ul>' );
	}

	return '' === $slides ? '' : belosvyat_carousel_markup( $slides, 'carousel--panels', $extra_attrs );
}

/**
 * Кешує зібраний блок головної, доки не змінять сторінку-джерело.
 *
 * Ключ містить час зміни сторінки та версію теми, тож окремий гачок на
 * скидання кешу не потрібен. Після зміни домену підніміть BELOSVYAT_VERSION.
 *
 * @param string   $key      Ключ сторінки-джерела (flora, fauna, tour).
 * @param callable $callback Отримує WP_Post і повертає HTML.
 * @return string
 */
function belosvyat_front_cached_html( $key, $callback ) {
	$page = belosvyat_front_source_page( $key );

	if ( ! $page ) {
		return '';
	}

	// У ключі — час зміни сторінки-джерела та час зміни самого збирача розмітки.
	// Друге важливе при викладанні теми: інакше після правки inc/gallery.php
	// сайт до 12 годин показував би стару розмітку з кешу.
	$transient = 'belosvyat_front_' . md5(
		$key . '|' . $page->post_modified_gmt . '|' . BELOSVYAT_VERSION . '|' . belosvyat_asset_version( 'inc/gallery.php' )
	);
	$cached    = get_transient( $transient );

	if ( is_string( $cached ) ) {
		return $cached;
	}

	$html = (string) call_user_func( $callback, $page );

	set_transient( $transient, $html, 12 * HOUR_IN_SECONDS );

	return $html;
}

/**
 * Пауза між кроками автопрокрутки стрічки рослин, мілісекунди.
 *
 * @return int Нуль вимикає автопрокрутку.
 */
function belosvyat_front_flora_autoplay() {
	/**
	 * Дозволяє змінити паузу або вимкнути автопрокрутку.
	 *
	 * @param int $delay Пауза в мілісекундах.
	 */
	return (int) apply_filters( 'belosvyat_front_flora_autoplay', 10000 );
}

/**
 * Пауза між кроками автопрокрутки стрічки тварин, мілісекунди.
 *
 * @return int Нуль вимикає автопрокрутку.
 */
function belosvyat_front_fauna_autoplay() {
	/**
	 * Дозволяє змінити паузу або вимкнути автопрокрутку.
	 *
	 * @param int $delay Пауза в мілісекундах.
	 */
	return (int) apply_filters( 'belosvyat_front_fauna_autoplay', 10000 );
}

/**
 * Карусель рослин для головної — зі сторінки «Флора».
 *
 * Модифікатор carousel--rail розтягує стрічку на всю ширину екрана: у кадрі
 * стільки зображень, скільки вміщається, а крайні ховаються за краями.
 * Атрибут data-carousel-autoplay вмикає неспішне автогортання; щойно
 * відвідувач торкається стрічки, відлік починається заново (див. gallery.js).
 *
 * @return string
 */
function belosvyat_front_flora_carousel() {
	return belosvyat_front_cached_html(
		'flora',
		static function ( $page ) {
			$autoplay = belosvyat_front_flora_autoplay();

			return belosvyat_carousel_from_ids(
				belosvyat_page_image_ids( $page ),
				'carousel--rail',
				$autoplay > 0 ? sprintf( 'data-carousel-autoplay="%d"', $autoplay ) : ''
			);
		}
	);
}

/**
 * Карусель панелей із тваринами для головної — зі сторінки «Фауна».
 *
 * @return string
 */
function belosvyat_front_fauna_carousel() {
	return belosvyat_front_cached_html(
		'fauna',
		static function ( $page ) {
			$blocks = belosvyat_page_gallery_blocks( $page );

			/**
			 * Дозволяє підмінити список зображень для головної.
			 *
			 * @param array $blocks Блоки core/image.
			 */
			$blocks = apply_filters( 'belosvyat_front_fauna_blocks', $blocks );

			/**
			 * Скільки зображень показувати на головній.
			 *
			 * Рахуємо саме зображення, а не панелі: скільки панелей поміститься
			 * в кадр, вирішує ширина екрана, а не тема.
			 *
			 * @param int $limit Кількість зображень.
			 */
			$limit = (int) apply_filters( 'belosvyat_front_fauna_limit', 18 );

			$panels = belosvyat_mosaic_panels( $blocks );

			if ( $limit > 0 ) {
				// Ріжемо цілими панелями, а не зображеннями: інакше остання
				// панель лишилася б із однією картинкою та трьома дірками.
				$taken = array();
				$shown = 0;

				foreach ( $panels as $panel ) {
					if ( $shown >= $limit ) {
						break;
					}

					$taken[] = $panel;
					$shown  += count( $panel );
				}

				$panels = $taken;
			}

			$autoplay = belosvyat_front_fauna_autoplay();

			return belosvyat_carousel_from_panels(
				$panels,
				$autoplay > 0 ? sprintf( 'data-carousel-autoplay="%d"', $autoplay ) : ''
			);
		}
	);
}

/**
 * Адреса панорамного туру для головної.
 *
 * Спершу поле Кастомайзера, далі — перший iframe зі сторінки «ВІАР-ТУР», щоб
 * адреса зберігалася в одному місці й не розходилася зі сторінкою туру.
 *
 * @return string
 */
function belosvyat_front_tour_src() {
	$custom = trim( (string) belosvyat_option( 'belosvyat_front_tour_url' ) );

	if ( '' !== $custom ) {
		return $custom;
	}

	$page = belosvyat_front_source_page( 'tour' );

	if ( ! $page ) {
		return '';
	}

	if ( preg_match( '#<iframe\b[^>]*\ssrc=["\']([^"\']+)["\']#i', $page->post_content, $matches ) ) {
		return $matches[1];
	}

	return '';
}
