<?php
/**
 * Стрічка новин (головна сторінка сайту).
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content">

	<?php if ( is_active_sidebar( 'first-top-widget-area' ) ) : ?>
		<div class="content-widgets content-widgets--top">
			<?php dynamic_sidebar( 'first-top-widget-area' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! is_front_page() ) : ?>
		<header class="page-header">
			<h1 class="page-header__title"><?php single_post_title(); ?></h1>
		</header>
	<?php endif; ?>

	<?php belosvyat_category_chips( 8 ); ?>

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

	<?php if ( is_active_sidebar( 'first-bottom-widget-area' ) ) : ?>
		<div class="content-widgets content-widgets--bottom">
			<?php dynamic_sidebar( 'first-bottom-widget-area' ); ?>
		</div>
	<?php endif; ?>

</main>

<?php
get_sidebar();
get_footer();
