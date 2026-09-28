<?php
/**
 * Головна: анонс новин.
 *
 * Три останні записи тими самими картками, що й у стрічці, плюс «пігулки»
 * рубрик — вони ведуть на архіви рубрик.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_latest = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $belosvyat_latest->have_posts() ) {
	wp_reset_postdata();

	return;
}
?>
<section class="front-section front-section--news front-section--tint">
	<div class="front-section__inner container">
		<h2 class="front-section__title"><?php echo esc_html( belosvyat_option( 'belosvyat_front_news_title' ) ); ?></h2>

		<?php belosvyat_category_chips( 6 ); ?>

		<div class="entry-grid entry-grid--preview">
			<?php
			// Кнопки поширення від AddToAny в анонсі зайві: плагін додає їх
			// усередину тексту картки. Вимикаємо його штатним фільтром, щоб
			// розмітки взагалі не було, і вмикаємо назад одразу після циклу.
			add_filter( 'addtoany_sharing_disabled', '__return_true' );

			while ( $belosvyat_latest->have_posts() ) {
				$belosvyat_latest->the_post();
				get_template_part( 'template-parts/content/content', get_post_format() );
			}

			remove_filter( 'addtoany_sharing_disabled', '__return_true' );

			wp_reset_postdata();
			?>
		</div>

		<?php belosvyat_front_cta( 'belosvyat_front_news_cta', belosvyat_posts_page_url() ); ?>
	</div>
</section>
