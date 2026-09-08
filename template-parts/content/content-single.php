<?php
/**
 * Повний текст запису.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>

	<header class="entry__header">
		<h1 class="entry__title"><?php the_title(); ?></h1>
		<?php belosvyat_post_meta( array( 'date', 'category', 'views' ) ); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry__featured">
			<?php the_post_thumbnail( 'belosvyat-hero', array( 'class' => 'entry__featured-image' ) ); ?>
			<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
				<figcaption><?php echo wp_kses_post( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="entry__content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Сторінки запису', 'belosvyat' ) . '"><span class="page-links__label">' . esc_html__( 'Сторінки:', 'belosvyat' ) . '</span>',
				'after'    => '</nav>',
				'separator' => '',
			)
		);
		?>
	</div>

	<?php
	$belosvyat_tags = get_the_tag_list( '', '', '' );

	if ( $belosvyat_tags ) :
		?>
		<footer class="entry__footer">
			<div class="entry__tags">
				<span class="entry__tags-label"><?php esc_html_e( 'Мітки:', 'belosvyat' ); ?></span>
				<?php echo wp_kses_post( $belosvyat_tags ); ?>
			</div>
		</footer>
	<?php endif; ?>

</article>
