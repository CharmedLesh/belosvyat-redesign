<?php
/**
 * Загальний резервний шаблон.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content">

	<?php belosvyat_breadcrumbs(); ?>

	<?php if ( have_posts() ) : ?>

		<div class="entry-grid">
			<?php
			while ( have_posts() ) {
				the_post();
				get_template_part( 'template-parts/content/content', get_post_format() );
			}
			?>
		</div>

		<?php belosvyat_pagination(); ?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/content/content', 'none' ); ?>

	<?php endif; ?>

</main>

<?php
get_sidebar();
get_footer();
