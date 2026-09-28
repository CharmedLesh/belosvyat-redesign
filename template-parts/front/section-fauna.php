<?php
/**
 * Головна: тваринний світ.
 *
 * Кожен слайд каруселі — панель мозаїки 2×2 з галереї сторінки «Фауна».
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_fauna_carousel = belosvyat_front_fauna_carousel();
$belosvyat_fauna_page     = belosvyat_front_source_page( 'fauna' );
$belosvyat_fauna_url      = $belosvyat_fauna_page ? get_permalink( $belosvyat_fauna_page ) : '';
?>
<section class="front-section front-section--fauna">
	<div class="front-section__inner container">
		<h2 class="front-section__title"><?php echo esc_html( belosvyat_option( 'belosvyat_front_fauna_title' ) ); ?></h2>

		<?php belosvyat_front_text( 'belosvyat_front_fauna_text' ); ?>
	</div>

	<?php if ( $belosvyat_fauna_carousel ) : ?>
		<?php /* Карусель живе поза .container: відступи колонки не мають її підрізати. */ ?>
		<div class="front-section__bleed">
			<?php echo $belosvyat_fauna_carousel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складено в inc/gallery.php. ?>
		</div>
	<?php endif; ?>

	<div class="front-section__inner container">
		<?php belosvyat_front_cta( 'belosvyat_front_fauna_cta', $belosvyat_fauna_url ); ?>
	</div>
</section>
