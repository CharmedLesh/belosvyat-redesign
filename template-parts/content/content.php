<?php
/**
 * Картка запису у стрічці та архівах.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--card' ); ?>>

	<?php belosvyat_entry_thumbnail(); ?>

	<div class="entry__body">
		<?php belosvyat_post_meta( array( 'date', 'category' ) ); ?>

		<h2 class="entry__title">
			<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h2>

		<div class="entry__excerpt">
			<?php the_excerpt(); ?>
		</div>

		<a class="entry__more" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Читати далі', 'belosvyat' ); ?>
			<span aria-hidden="true">&rarr;</span>
			<span class="screen-reader-text"><?php the_title(); ?></span>
		</a>
	</div>

</article>
