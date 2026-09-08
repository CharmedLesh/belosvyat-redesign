<?php
/**
 * Форма пошуку.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_search_id = wp_unique_id( 'search-field-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $belosvyat_search_id ); ?>">
		<?php esc_html_e( 'Пошук по сайту', 'belosvyat' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $belosvyat_search_id ); ?>"
		class="search-form__field"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Що шукаємо?', 'belosvyat' ); ?>"
	/>
	<button type="submit" class="search-form__submit">
		<?php esc_html_e( 'Знайти', 'belosvyat' ); ?>
	</button>
</form>
