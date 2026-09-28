<?php
/**
 * Головна: рослинний світ.
 *
 * Карусель — ті самі зображення, що й на сторінці «Флора».
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_flora_carousel = belosvyat_front_flora_carousel();
$belosvyat_flora_page     = belosvyat_front_source_page( 'flora' );
$belosvyat_flora_url      = $belosvyat_flora_page ? get_permalink( $belosvyat_flora_page ) : '';
?>
<section class="front-section front-section--flora front-section--tint">
	<div class="front-section__inner container">
		<h2 class="front-section__title"><?php echo esc_html( belosvyat_option( 'belosvyat_front_flora_title' ) ); ?></h2>

		<?php belosvyat_front_text( 'belosvyat_front_flora_text' ); ?>
	</div>

	<?php if ( $belosvyat_flora_carousel ) : ?>
		<?php /* Стрічка живе поза .container: вона має йти від краю до краю екрана. */ ?>
		<div class="front-section__bleed">
			<?php echo $belosvyat_flora_carousel; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Складено в inc/gallery.php. ?>
		</div>
	<?php endif; ?>

	<div class="front-section__inner container">
		<?php belosvyat_front_cta( 'belosvyat_front_flora_cta', $belosvyat_flora_url ); ?>
	</div>
</section>
