<?php
/**
 * Банер на головній сторінці та у стрічці новин.
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

// На стрічці новин <h1> належить заголовку сторінки, тож банер там — <p>.
$belosvyat_title_tag = is_front_page() ? 'h1' : 'p';

if ( '' === trim( (string) $belosvyat_cta_url ) ) {
	// Типова кнопка веде у стрічку новин, тому на самій стрічці її ховаємо:
	// посилання на сторінку, де відвідувач уже перебуває, нічого не дає.
	if ( is_home() ) {
		$belosvyat_cta_text = '';
	}

	$belosvyat_cta_url = belosvyat_posts_page_url();
}
?>
<section class="hero<?php echo $belosvyat_hero_image ? ' hero--has-image' : ''; ?>"
	<?php if ( $belosvyat_hero_image ) : ?>
		style="--hero-image: url('<?php echo esc_url( $belosvyat_hero_image ); ?>')"
	<?php endif; ?>
>
	<div class="hero__inner container">
		<p class="hero__eyebrow"><?php esc_html_e( 'Природно-заповідний фонд України', 'belosvyat' ); ?></p>

		<<?php echo esc_attr( $belosvyat_title_tag ); ?> class="hero__title"><?php echo esc_html( $belosvyat_title ); ?></<?php echo esc_attr( $belosvyat_title_tag ); ?>>

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
