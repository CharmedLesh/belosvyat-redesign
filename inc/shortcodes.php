<?php
/**
 * Шорткоди, успадковані від старої теми.
 *
 * Збережено лише [year] — він присутній у збереженому тексті підвалу.
 * Решту шорткодів Artisteer ([rss], [ad], [top], [login-link], [blog-title],
 * [xhtml], [css]) видалено як непотрібні.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Поточний рік: [year]
 *
 * @return string
 */
function belosvyat_shortcode_year() {
	return esc_html( wp_date( 'Y' ) );
}
add_shortcode( 'year', 'belosvyat_shortcode_year' );
