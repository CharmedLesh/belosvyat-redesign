<?php
/**
 * Сумісність зі старим контентом.
 *
 * На сайті ~380 записів і ~50 сторінок, створених у 2012–2025 роках: там є
 * таблиці з фіксованою шириною, [gallery], вирівнювання через float та
 * інлайнові розміри. Ці фільтри не дають такому контенту ламати макет.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Довжина автоматичного анонсу (у словах).
 *
 * @return int
 */
function belosvyat_excerpt_length() {
	return 28;
}
add_filter( 'excerpt_length', 'belosvyat_excerpt_length' );

/**
 * Закінчення автоматичного анонсу.
 *
 * @return string
 */
function belosvyat_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'belosvyat_excerpt_more' );

/**
 * Обгортає таблиці у прокручуваний контейнер.
 *
 * Старі записи містять таблиці з width="800" тощо — на мобільному вони
 * розпирали б сторінку. Обгортка дає горизонтальний скрол лише таблиці.
 * Блокові таблиці (.wp-block-table) вже мають власну обгортку <figure>.
 *
 * @param string $content HTML контенту.
 * @return string
 */
function belosvyat_wrap_tables( $content ) {
	if ( is_admin() || false === stripos( $content, '<table' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'#<table\b[^>]*>.*?</table>#is',
		static function ( $matches ) {
			// Блокові таблиці залишаємо як є — їх обгортає <figure>.
			if ( false !== stripos( $matches[0], 'wp-block-table' ) ) {
				return $matches[0];
			}

			return '<div class="table-scroll">' . $matches[0] . '</div>';
		},
		$content
	);
}
add_filter( 'the_content', 'belosvyat_wrap_tables', 20 );
