<?php
/**
 * Одноразова міграція: статична головна «/» та стрічка новин на «/news».
 *
 * Створює сторінки «Головна» і «Новини», перемикає «Налаштування → Читання»
 * та переводить перший пункт меню з довільного посилання на посилання-сторінку.
 *
 * Запуск (із кореня теми):
 *   php tools/front-page-migration.php
 *
 * Скрипт ідемпотентний: повторний запуск нічого не дублює й друкує те саме.
 * Покрокова інструкція та ручний варіант — у docs/front-page-setup.md.
 *
 * @package Belosvyat
 */

// Скрипт міняє налаштування сайту, тож із браузера він недоступний.
if ( 'cli' !== PHP_SAPI ) {
	http_response_code( 403 );
	exit( 'CLI only' );
}

$belosvyat_dir = __DIR__;

while ( ! file_exists( $belosvyat_dir . '/wp-load.php' ) ) {
	$belosvyat_parent = dirname( $belosvyat_dir );

	if ( $belosvyat_parent === $belosvyat_dir ) {
		fwrite( STDERR, "wp-load.php не знайдено — запускайте скрипт усередині сайту WordPress\n" );
		exit( 1 );
	}

	$belosvyat_dir = $belosvyat_parent;
}

require $belosvyat_dir . '/wp-load.php';

/**
 * Знаходить або створює сторінку із заданим слагом.
 *
 * Шукаємо серед усіх статусів: get_page_by_path() не бачить чернеток і кошика,
 * тож повторний запуск створив би другу сторінку зі слагом news-2.
 *
 * @param string $slug  Слаг сторінки.
 * @param string $title Назва сторінки.
 * @return int ID сторінки.
 */
function belosvyat_migration_page( $slug, $title ) {
	$found = get_posts(
		array(
			'post_type'        => 'page',
			'name'             => $slug,
			'post_status'      => 'any',
			'numberposts'      => 1,
			'suppress_filters' => false,
		)
	);

	if ( $found ) {
		$page = $found[0];

		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
				)
			);

			printf( "сторінку «%s» опубліковано\n", $title );
		}

		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $title,
			// Слаг задаємо явно: плагіни rus-to-lat/acf-rus-to-lat інакше
			// транслітерують «Новини» у «novini».
			'post_name'      => $slug,
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}

	printf( "створено сторінку «%s» (/%s/)\n", $title, $slug );

	return (int) $id;
}

$belosvyat_news_id = belosvyat_migration_page( 'news', 'Новини' );
$belosvyat_home_id = belosvyat_migration_page( 'golovna', 'Головна' );

if ( $belosvyat_news_id === $belosvyat_home_id ) {
	fwrite( STDERR, "Сторінка новин і головна не можуть бути однією сторінкою\n" );
	exit( 1 );
}

update_option( 'page_on_front', $belosvyat_home_id );
update_option( 'page_for_posts', $belosvyat_news_id );
update_option( 'show_on_front', 'page' );

// Перший пункт меню: був «довільним посиланням» з абсолютною адресою, через що
// на бойовому домені вів би на локальний сайт. Пункт-сторінка бере адресу з бази.
$belosvyat_menu = wp_get_nav_menu_object( 'навігація по сайту' );

if ( ! $belosvyat_menu ) {
	$belosvyat_locations = get_nav_menu_locations();
	$belosvyat_menu      = isset( $belosvyat_locations['primary-menu'] )
		? wp_get_nav_menu_object( $belosvyat_locations['primary-menu'] )
		: false;
}

if ( $belosvyat_menu ) {
	$belosvyat_items = wp_get_nav_menu_items( $belosvyat_menu->term_id );

	foreach ( (array) $belosvyat_items as $belosvyat_item ) {
		$belosvyat_is_home_link = 'custom' === $belosvyat_item->type
			&& untrailingslashit( $belosvyat_item->url ) === untrailingslashit( home_url( '/' ) );

		$belosvyat_is_news_link = 'post_type' === $belosvyat_item->type
			&& (int) $belosvyat_item->object_id === $belosvyat_news_id;

		if ( ! $belosvyat_is_home_link && ! $belosvyat_is_news_link ) {
			continue;
		}

		$belosvyat_result = wp_update_nav_menu_item(
			$belosvyat_menu->term_id,
			$belosvyat_item->ID,
			array(
				'menu-item-title'     => 'Новини',
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $belosvyat_news_id,
				'menu-item-parent-id' => 0,
				'menu-item-position'  => (int) $belosvyat_item->menu_order,
				'menu-item-status'    => 'publish',
				'menu-item-url'       => '',
			)
		);

		if ( is_wp_error( $belosvyat_result ) ) {
			fwrite( STDERR, $belosvyat_result->get_error_message() . "\n" );
			exit( 1 );
		}

		printf( "пункт меню #%d тепер веде на сторінку «Новини»\n", $belosvyat_item->ID );
		break;
	}
} else {
	fwrite( STDERR, "Меню не знайдено — пункт «Головна» доведеться змінити вручну\n" );
}

flush_rewrite_rules( false );
wp_cache_flush();

$belosvyat_first = wp_get_nav_menu_items( $belosvyat_menu ? $belosvyat_menu->term_id : 0 );
$belosvyat_first = $belosvyat_first ? $belosvyat_first[0] : null;

printf(
	"\nnews=%d (%s)\nhome=%d (%s)\nshow_on_front=%s page_on_front=%s page_for_posts=%s\nперший пункт меню: type=%s object_id=%s url=%s\n",
	$belosvyat_news_id,
	get_permalink( $belosvyat_news_id ),
	$belosvyat_home_id,
	get_permalink( $belosvyat_home_id ),
	get_option( 'show_on_front' ),
	get_option( 'page_on_front' ),
	get_option( 'page_for_posts' ),
	$belosvyat_first ? $belosvyat_first->type : '—',
	$belosvyat_first ? $belosvyat_first->object_id : '—',
	$belosvyat_first ? $belosvyat_first->url : '—'
);
