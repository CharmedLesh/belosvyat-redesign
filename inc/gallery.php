<?php
/**
 * Карусель для сторінок-галерей.
 *
 * Старі сторінки-галереї — це просто низка мініатюр 150×150, кожна в посиланні
 * на файл. Замість «килима» з картинок збираємо їх в одну карусель, а клік
 * відкриває зображення поверх сторінки (див. assets/js/gallery.js).
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
		'<div class="carousel" data-carousel data-label-close="%1$s" data-label-prev="%2$s" data-label-next="%3$s" data-label-image="%4$s">
	<ul class="carousel__track" data-carousel-track>%5$s</ul>
	<button class="carousel__nav carousel__nav--prev" type="button" data-carousel-prev aria-label="%2$s" hidden><span aria-hidden="true">&lsaquo;</span></button>
	<button class="carousel__nav carousel__nav--next" type="button" data-carousel-next aria-label="%3$s" hidden><span aria-hidden="true">&rsaquo;</span></button>
</div>',
		esc_attr__( 'Закрити', 'belosvyat' ),
		esc_attr__( 'Попереднє зображення', 'belosvyat' ),
		esc_attr__( 'Наступне зображення', 'belosvyat' ),
		esc_attr__( 'Зображення', 'belosvyat' ),
		$slides // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складено вище.
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
