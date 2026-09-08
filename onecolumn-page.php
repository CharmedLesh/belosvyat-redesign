<?php
/**
 * Template Name: One Column
 *
 * Сторінка на всю ширину, без бічної панелі.
 *
 * Ім'я файлу та рядок «Template Name» успадковані від старої теми навмисно:
 * сторінки зберігають ім'я файлу в мета-полі _wp_page_template, тому будь-яке
 * перейменування скинуло б цей шаблон на вже призначених сторінках.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content content--wide">

	<?php belosvyat_breadcrumbs(); ?>

	<?php
	while ( have_posts() ) {
		the_post();

		get_template_part( 'template-parts/content/content', 'page' );

		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	}
	?>

</main>

<?php
get_footer();
