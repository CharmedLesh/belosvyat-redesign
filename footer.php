<?php
/**
 * Підвал сайту.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_footer_columns = belosvyat_footer_columns();
?>
		</div><!-- .layout -->
	</div><!-- .site-content -->

	<footer class="site-footer">

		<?php if ( ! empty( $belosvyat_footer_columns ) ) : ?>
			<div class="site-footer__widgets container" data-columns="<?php echo esc_attr( count( $belosvyat_footer_columns ) ); ?>">
				<?php foreach ( $belosvyat_footer_columns as $belosvyat_area ) : ?>
					<div class="site-footer__column">
						<?php dynamic_sidebar( $belosvyat_area ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="site-footer__bar">
			<div class="container site-footer__bar-inner">

				<p class="site-footer__copyright">
					<?php echo do_shortcode( wp_kses_post( belosvyat_option( 'belosvyat_footer_text' ) ) ); ?>
				</p>

				<?php
				if ( has_nav_menu( 'footer-menu' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer-menu',
							'container'      => 'nav',
							'container_class' => 'footer-nav',
							'menu_class'     => 'footer-nav__list',
							'depth'          => 1,
						)
					);
				}
				?>

				<a class="footer-feed" href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>">
					<?php esc_html_e( 'RSS-стрічка', 'belosvyat' ); ?>
				</a>

			</div>
		</div>
	</footer>

	<button type="button" class="to-top" id="to-top" hidden>
		<span class="screen-reader-text"><?php esc_html_e( 'Нагору', 'belosvyat' ); ?></span>
		<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
			<path d="M8 13V3M3.5 7.5 8 3l4.5 4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
	</button>

</div><!-- .site -->

<?php wp_footer(); ?>
</body>
</html>
