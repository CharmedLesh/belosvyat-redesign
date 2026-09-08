<?php
/**
 * Вміст сторінки.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>

	<header class="entry__header">
		<h1 class="entry__title"><?php the_title(); ?></h1>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry__featured">
			<?php the_post_thumbnail( 'belosvyat-hero', array( 'class' => 'entry__featured-image' ) ); ?>
		</figure>
	<?php endif; ?>

	<div class="entry__content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before'    => '<nav class="page-links" aria-label="' . esc_attr__( 'Сторінки', 'belosvyat' ) . '"><span class="page-links__label">' . esc_html__( 'Сторінки:', 'belosvyat' ) . '</span>',
				'after'     => '</nav>',
				'separator' => '',
			)
		);
		?>
	</div>

	<?php if ( get_edit_post_link() ) : ?>
		<footer class="entry__footer">
			<?php
			edit_post_link(
				__( 'Редагувати сторінку', 'belosvyat' ),
				'<span class="entry__edit">',
				'</span>'
			);
			?>
		</footer>
	<?php endif; ?>

</article>
