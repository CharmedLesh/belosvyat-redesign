<?php
/**
 * Підключення стилів та скриптів.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Версія файлу за часом зміни — щоб кеш скидався автоматично.
 *
 * @param string $relative_path Шлях відносно кореня теми.
 * @return string
 */
function belosvyat_asset_version( $relative_path ) {
	$file = BELOSVYAT_DIR . '/' . ltrim( $relative_path, '/' );

	if ( file_exists( $file ) ) {
		return (string) filemtime( $file );
	}

	return BELOSVYAT_VERSION;
}

/**
 * Підключає ресурси фронтенду.
 */
function belosvyat_enqueue_assets() {
	wp_enqueue_style(
		'belosvyat-main',
		BELOSVYAT_URI . '/assets/css/main.css',
		array(),
		belosvyat_asset_version( 'assets/css/main.css' )
	);

	// style.css містить лише заголовок теми, але дочірні теми очікують цей handle.
	wp_style_add_data( 'belosvyat-main', 'path', BELOSVYAT_DIR . '/assets/css/main.css' );

	wp_enqueue_script(
		'belosvyat-navigation',
		BELOSVYAT_URI . '/assets/js/navigation.js',
		array(),
		belosvyat_asset_version( 'assets/js/navigation.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Карусель, мозаїка та перегляд зображень — лише на сторінках-галереях.
	if ( belosvyat_is_gallery_page() || belosvyat_is_mosaic_page() ) {
		wp_enqueue_script(
			'belosvyat-gallery',
			BELOSVYAT_URI . '/assets/js/gallery.js',
			array(),
			belosvyat_asset_version( 'assets/js/gallery.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'belosvyat_enqueue_assets' );
