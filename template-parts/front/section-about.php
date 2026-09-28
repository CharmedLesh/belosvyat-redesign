<?php
/**
 * Головна: розділ про парк.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="front-section front-section--about">
	<div class="front-section__inner container">
		<h2 class="front-section__title"><?php echo esc_html( belosvyat_option( 'belosvyat_front_about_title' ) ); ?></h2>

		<?php belosvyat_front_text( 'belosvyat_front_about_text' ); ?>
	</div>
</section>
