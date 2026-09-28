<?php
/**
 * Головна: панорамний ВІАР-тур.
 *
 * Адреса панорами береться зі сторінки «ВІАР-ТУР», тож окремо її оновлювати
 * не треба. Атрибут height навмисно без «px»: у контенті стоїть height="600px",
 * і саме через цю помилку iframe на сторінці туру схлопувався до 150 пікселів.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_tour_src = belosvyat_front_tour_src();

if ( '' === $belosvyat_tour_src ) {
	return;
}
?>
<section class="front-section front-section--tour">
	<div class="front-section__inner container">
		<h2 class="front-section__title"><?php echo esc_html( belosvyat_option( 'belosvyat_front_tour_title' ) ); ?></h2>

		<?php belosvyat_front_text( 'belosvyat_front_tour_text' ); ?>

		<div class="front-tour">
			<iframe
				class="front-tour__frame"
				src="<?php echo esc_url( $belosvyat_tour_src ); ?>"
				width="100%"
				height="600"
				loading="lazy"
				title="<?php esc_attr_e( 'Панорамний тур парком', 'belosvyat' ); ?>"
				allowfullscreen
			></iframe>
		</div>
	</div>
</section>
