<?php
/**
 * Банер на головній сторінці.
 *
 * Фонове зображення береться з «Зображення шапки» (custom-header).
 * Якщо його не задано — використовується градієнт у кольорах теми.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

$belosvyat_hero_image = get_header_image();
$belosvyat_title      = belosvyat_option( 'belosvyat_hero_title' );
$belosvyat_subtitle   = belosvyat_option( 'belosvyat_hero_subtitle' );
$belosvyat_cta_text   = belosvyat_option( 'belosvyat_hero_cta_text' );
$belosvyat_cta_url    = belosvyat_option( 'belosvyat_hero_cta_url' );

if ( '' === trim( (string) $belosvyat_title ) ) {
	$belosvyat_title = belosvyat_site_title();
}

if ( '' === trim( (string) $belosvyat_cta_url ) ) {
	$belosvyat_posts_page = (int) get_option( 'page_for_posts' );
	$belosvyat_cta_url    = $belosvyat_posts_page ? get_permalink( $belosvyat_posts_page ) : '#content';
}
?>
<section class="hero<?php echo $belosvyat_hero_image ? ' hero--has-image' : ''; ?>"
	<?php if ( $belosvyat_hero_image ) : ?>
		style="--hero-image: url('<?php echo esc_url( $belosvyat_hero_image ); ?>')"
	<?php endif; ?>
>
	<div class="hero__inner container">
		<p class="hero__eyebrow"><?php esc_html_e( 'Природно-заповідний фонд України', 'belosvyat' ); ?></p>

		<h1 class="hero__title"><?php echo esc_html( $belosvyat_title ); ?></h1>

		<?php if ( $belosvyat_subtitle ) : ?>
			<p class="hero__subtitle"><?php echo esc_html( $belosvyat_subtitle ); ?></p>
		<?php endif; ?>

		<?php if ( $belosvyat_cta_text ) : ?>
			<p class="hero__actions">
				<a class="button button--light" href="<?php echo esc_url( $belosvyat_cta_url ); ?>">
					<?php echo esc_html( $belosvyat_cta_text ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</section>
