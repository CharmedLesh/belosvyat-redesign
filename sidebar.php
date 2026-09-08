<?php
/**
 * Бічна панель.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

if ( ! belosvyat_has_sidebar() ) {
	return;
}
?>
<aside class="sidebar" id="sidebar" aria-label="<?php esc_attr_e( 'Бічна панель', 'belosvyat' ); ?>">
	<?php dynamic_sidebar( 'primary-widget-area' ); ?>

	<?php if ( is_active_sidebar( 'secondary-widget-area' ) ) : ?>
		<?php dynamic_sidebar( 'secondary-widget-area' ); ?>
	<?php endif; ?>
</aside>
