<?php
/**
 * Сторінка вкладення.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content content--wide">

	<?php belosvyat_breadcrumbs(); ?>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--attachment' ); ?>>

			<header class="entry__header">
				<h1 class="entry__title"><?php the_title(); ?></h1>
				<?php belosvyat_post_meta( array( 'date' ) ); ?>
			</header>

			<figure class="entry__attachment">
				<?php
				if ( wp_attachment_is_image() ) {
					echo wp_get_attachment_image( get_the_ID(), 'full', false, array( 'class' => 'entry__attachment-image' ) );
				} else {
					printf(
						'<a href="%s">%s</a>',
						esc_url( wp_get_attachment_url() ),
						esc_html( get_the_title() )
					);
				}
				?>

				<?php if ( has_excerpt() ) : ?>
					<figcaption><?php echo wp_kses_post( get_the_excerpt() ); ?></figcaption>
				<?php endif; ?>
			</figure>

			<div class="entry__content">
				<?php the_content(); ?>
			</div>

			<?php
			$belosvyat_parent = wp_get_post_parent_id( get_the_ID() );

			if ( $belosvyat_parent ) :
				?>
				<footer class="entry__footer">
					<a class="button button--ghost" href="<?php echo esc_url( get_permalink( $belosvyat_parent ) ); ?>">
						<?php esc_html_e( 'Повернутися до матеріалу', 'belosvyat' ); ?>
					</a>
				</footer>
			<?php endif; ?>

		</article>
		<?php
	endwhile;
	?>

</main>

<?php
get_footer();
