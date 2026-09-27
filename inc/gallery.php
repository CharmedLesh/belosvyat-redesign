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
			$image = wp_get_attachment_image(
				$attachment_id,
				'large',
				false,
				array(
					'class'    => 'carousel__image',
					'loading'  => $index ? 'lazy' : 'eager',
					'decoding' => 'async',
				)
			);

			$original = wp_get_attachment_image_url( $attachment_id, 'full' );

			if ( $original ) {
				$full = $original;
			}
		} else {
			$image = belosvyat_add_img_class( $img, 'carousel__image' );
		}

		$slides .= sprintf(
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

	return sprintf(
		'<div class="carousel" data-carousel %1$s>
	<ul class="carousel__track" data-carousel-track>%2$s</ul>
	<button class="carousel__nav carousel__nav--prev" type="button" data-carousel-prev aria-label="%3$s" hidden><span aria-hidden="true">&lsaquo;</span></button>
	<button class="carousel__nav carousel__nav--next" type="button" data-carousel-next aria-label="%4$s" hidden><span aria-hidden="true">&rsaquo;</span></button>
</div>',
		belosvyat_lightbox_label_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Атрибути екрановані.
		$slides, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складено вище.
		esc_attr__( 'Попереднє зображення', 'belosvyat' ),
		esc_attr__( 'Наступне зображення', 'belosvyat' )
	);
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
 * Одна комірка мозаїки з блока core/image.
 *
 * @param array $block Внутрішній блок галереї.
 * @return string HTML комірки або порожній рядок.
 */
function belosvyat_mosaic_item( $block ) {
	$attachment_id = isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : 0;

	if ( ! $attachment_id ) {
		return '';
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
		return '';
	}

	$meta = wp_get_attachment_metadata( $attachment_id );
	$size = belosvyat_mosaic_size_class(
		isset( $meta['width'] ) ? (int) $meta['width'] : 0,
		isset( $meta['height'] ) ? (int) $meta['height'] : 0
	);

	$caption = '';

	if ( preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#is', $block['innerHTML'], $matches ) ) {
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

	return sprintf(
		'<li class="mosaic__item %1$s"><figure class="mosaic__figure"><a class="mosaic__link" href="%2$s" aria-label="%3$s">%4$s</a>%5$s</figure></li>',
		esc_attr( $size ),
		esc_url( $full ),
		esc_attr( $label ),
		$image, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML зображення від WordPress.
		$caption ? '<figcaption class="mosaic__caption">' . wp_kses_post( $caption ) . '</figcaption>' : ''
	);
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
