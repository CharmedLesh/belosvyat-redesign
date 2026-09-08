<?php
/**
 * Налаштування теми.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Реєструє підтримку можливостей WordPress.
 */
function belosvyat_setup() {
	load_theme_textdomain( 'belosvyat', BELOSVYAT_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 96,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Фонове зображення банера на головній сторінці.
	add_theme_support(
		'custom-header',
		array(
			'default-image' => '',
			'width'         => 1920,
			'height'        => 720,
			'flex-height'   => true,
			'flex-width'    => true,
			'header-text'   => false,
		)
	);

	// Формати записів, що реально використовувались у старій темі.
	add_theme_support( 'post-formats', array( 'aside', 'gallery' ) );

	add_editor_style( 'assets/css/main.css' );

	/*
	 * `primary-menu` навмисно збережено зі старої теми — до нього вже прив'язане
	 * меню «навігація по сайту». Перейменування скинуло б цю прив'язку.
	 */
	register_nav_menus(
		array(
			'primary-menu'   => __( 'Головне меню', 'belosvyat' ),
			'secondary-menu' => __( 'Додаткове меню (верхня смуга)', 'belosvyat' ),
			'footer-menu'    => __( 'Меню у підвалі', 'belosvyat' ),
		)
	);

	add_image_size( 'belosvyat-card', 720, 460, true );
	add_image_size( 'belosvyat-hero', 1600, 720, true );
}
add_action( 'after_setup_theme', 'belosvyat_setup' );

/**
 * Ширина контенту для oEmbed та широких зображень.
 */
function belosvyat_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'belosvyat_content_width', 760 );
}
add_action( 'after_setup_theme', 'belosvyat_content_width', 0 );

/**
 * Назва сайту з резервним значенням.
 *
 * @return string
 */
function belosvyat_site_title() {
	$name = get_bloginfo( 'name', 'display' );

	if ( '' === trim( wp_strip_all_tags( (string) $name ) ) ) {
		$name = BELOSVYAT_FALLBACK_TITLE;
	}

	return apply_filters( 'belosvyat_site_title', $name );
}

/**
 * Підставляє резервну назву у <title>, доки `blogname` порожній.
 *
 * @param array $parts Частини заголовка документа.
 * @return array
 */
function belosvyat_document_title_parts( $parts ) {
	/*
	 * `site` заповнюємо лише тоді, коли WordPress сам додав цю частину:
	 * на головній сторінці її немає, інакше назва задвоювалася б.
	 */
	if ( array_key_exists( 'site', $parts ) && empty( $parts['site'] ) ) {
		$parts['site'] = BELOSVYAT_FALLBACK_TITLE;
	}

	if ( empty( $parts['title'] ) ) {
		$parts['title'] = BELOSVYAT_FALLBACK_TITLE;
	}

	return $parts;
}
add_filter( 'document_title_parts', 'belosvyat_document_title_parts' );

/**
 * Додає корисні класи до <body>.
 *
 * @param array $classes Класи body.
 * @return array
 */
function belosvyat_body_classes( $classes ) {
	$classes[] = belosvyat_has_sidebar() ? 'has-sidebar' : 'no-sidebar';

	if ( is_front_page() && belosvyat_hero_is_enabled() ) {
		$classes[] = 'has-hero';
	}

	if ( ! is_singular() ) {
		$classes[] = 'is-listing';
	}

	return $classes;
}
add_filter( 'body_class', 'belosvyat_body_classes' );
