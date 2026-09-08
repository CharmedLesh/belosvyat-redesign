<?php
/**
 * Порожній результат: нічого не знайдено.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="no-results">
	<h1 class="no-results__title">
		<?php
		if ( is_search() ) {
			esc_html_e( 'Нічого не знайдено', 'belosvyat' );
		} elseif ( is_404() ) {
			esc_html_e( 'Сторінку не знайдено', 'belosvyat' );
		} else {
			esc_html_e( 'Тут поки що порожньо', 'belosvyat' );
		}
		?>
	</h1>

	<p class="no-results__text">
		<?php
		if ( is_search() ) {
			esc_html_e( 'За вашим запитом нічого не знайдено. Спробуйте інші слова.', 'belosvyat' );
		} elseif ( is_404() ) {
			esc_html_e( 'Можливо, сторінку переміщено або видалено. Скористайтеся пошуком або перейдіть на головну.', 'belosvyat' );
		} else {
			esc_html_e( 'Записів у цьому розділі ще немає.', 'belosvyat' );
		}
		?>
	</p>

	<div class="no-results__search">
		<?php get_search_form(); ?>
	</div>

	<p class="no-results__actions">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'На головну', 'belosvyat' ); ?>
		</a>
	</p>

	<?php belosvyat_category_chips( 8 ); ?>
</div>
