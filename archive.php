<?php
/**
 * Архіви: рубрики, мітки, дати, автори.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content">

	<?php belosvyat_breadcrumbs(); ?>

	<header class="page-header">
		<h1 class="page-header__title"><?php the_archive_title(); ?></h1>

		<?php
		$belosvyat_description = get_the_archive_description();

		if ( $belosvyat_description ) :
			?>
			<div class="page-header__description"><?php echo wp_kses_post( $belosvyat_description ); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( is_category() || is_tag() ) : ?>
		<?php belosvyat_category_chips( 8 ); ?>
	<?php endif; ?>

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
