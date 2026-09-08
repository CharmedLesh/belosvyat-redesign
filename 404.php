<?php
/**
 * Сторінка 404.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content content--wide">
	<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
</main>

<?php
get_footer();
