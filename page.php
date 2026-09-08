<?php
/**
 * Окрема сторінка.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content">

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
get_sidebar();
get_footer();
