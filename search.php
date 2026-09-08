<?php
/**
 * Результати пошуку.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content">

	<?php belosvyat_breadcrumbs(); ?>

	<header class="page-header">
		<h1 class="page-header__title">
			<?php
			printf(
				/* translators: %s — пошуковий запит. */
				esc_html__( 'Результати пошуку: %s', 'belosvyat' ),
				'<span class="page-header__query">' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>

		<p class="page-header__description">
			<?php
			$belosvyat_found = (int) $wp_query->found_posts;

			printf(
				/* translators: %d — кількість знайдених матеріалів. */
				esc_html__( 'Знайдено %1$d %2$s', 'belosvyat' ),
				$belosvyat_found,
				esc_html( belosvyat_plural( $belosvyat_found, 'матеріал', 'матеріали', 'матеріалів' ) )
			);
			?>
		</p>

		<div class="page-header__search"><?php get_search_form(); ?></div>
	</header>

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
