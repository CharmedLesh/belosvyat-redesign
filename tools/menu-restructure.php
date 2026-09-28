<?php
/**
 * Одноразова перебудова головного меню.
 *
 * Додає пункт «Головна», згортає випадайку «Екоосвіта та рекреація» в один
 * пункт і прибирає кілька зайвих пунктів. Пункти-сторінки беруть адресу з бази,
 * тож на бойовому домені все лишається робочим.
 *
 * Запуск (із кореня теми):
 *   php tools/menu-restructure.php
 *
 * Скрипт ідемпотентний: повторний запуск нічого не дублює.
 * Ручний варіант для бою — у docs/front-page-setup.md.
 *
 * @package Belosvyat
 */

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

$belosvyat_locations = get_nav_menu_locations();
$belosvyat_menu      = isset( $belosvyat_locations['primary-menu'] )
	? wp_get_nav_menu_object( $belosvyat_locations['primary-menu'] )
	: false;

if ( ! $belosvyat_menu ) {
	fwrite( STDERR, "Меню для локації primary-menu не призначене\n" );
	exit( 1 );
}

$belosvyat_menu_id = (int) $belosvyat_menu->term_id;
$belosvyat_items   = wp_get_nav_menu_items( $belosvyat_menu_id );

/**
 * Шукає пункт меню, що веде на задану сторінку.
 *
 * @param array $items   Пункти меню.
 * @param int   $page_id ID сторінки.
 * @return object|null
 */
function belosvyat_menu_item_for_page( $items, $page_id ) {
	foreach ( (array) $items as $item ) {
		if ( 'post_type' === $item->type && 'page' === $item->object && (int) $item->object_id === $page_id ) {
			return $item;
		}
	}

	return null;
}

/**
 * Шукає пункт меню за його ID серед наявних.
 *
 * @param array $items Пункти меню.
 * @param int   $id    ID пункту.
 * @return object|null
 */
function belosvyat_menu_item_by_id( $items, $id ) {
	foreach ( (array) $items as $item ) {
		if ( (int) $item->ID === $id ) {
			return $item;
		}
	}

	return null;
}

// --- 1. Пункт «Головна» ---------------------------------------------------
$belosvyat_front_id = (int) get_option( 'page_on_front' );

if ( ! $belosvyat_front_id ) {
	fwrite( STDERR, "Статична головна не налаштована — спершу запустіть tools/front-page-migration.php\n" );
	exit( 1 );
}

$belosvyat_home_item = belosvyat_menu_item_for_page( $belosvyat_items, $belosvyat_front_id );

if ( $belosvyat_home_item ) {
	$belosvyat_home_item_id = (int) $belosvyat_home_item->ID;
} else {
	$belosvyat_home_item_id = wp_update_nav_menu_item(
		$belosvyat_menu_id,
		0,
		array(
			'menu-item-title'     => 'Головна',
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $belosvyat_front_id,
			'menu-item-parent-id' => 0,
			'menu-item-status'    => 'publish',
		)
	);

	if ( is_wp_error( $belosvyat_home_item_id ) ) {
		fwrite( STDERR, $belosvyat_home_item_id->get_error_message() . "\n" );
		exit( 1 );
	}

	echo "додано пункт «Головна»\n";
}

// --- 2. «Екоосвіта та рекреація» → один пункт зі сторінкою відділу ---------
$belosvyat_ecoedu_id = 723;
$belosvyat_ecoedu    = belosvyat_menu_item_by_id( $belosvyat_items, $belosvyat_ecoedu_id );
$belosvyat_ecoedu_page = get_page_by_path( 'sklad-viddilu-3' );

if ( $belosvyat_ecoedu && $belosvyat_ecoedu_page ) {
	$belosvyat_result = wp_update_nav_menu_item(
		$belosvyat_menu_id,
		$belosvyat_ecoedu_id,
		array(
			'menu-item-title'     => 'Склад відділу екоосвіти та рекреації',
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => (int) $belosvyat_ecoedu_page->ID,
			'menu-item-parent-id' => 0,
			'menu-item-status'    => 'publish',
			'menu-item-url'       => '',
		)
	);

	if ( is_wp_error( $belosvyat_result ) ) {
		fwrite( STDERR, $belosvyat_result->get_error_message() . "\n" );
		exit( 1 );
	}

	echo "пункт «Екоосвіта та рекреація» замінено на «Склад відділу екоосвіти та рекреації»\n";
}

// --- 3. Зайві пункти ------------------------------------------------------
$belosvyat_remove = array(
	712  => 'Склад відділу (підпункт екоосвіти)',
	1060 => 'Екотуристичні маршрути',
	7001 => 'Напрями роботи',
	7005 => 'Правила поведінки на маршрутах',
	7009 => 'Робота служби державної охорони',
	1557 => 'Проект організації території парку',
);

foreach ( $belosvyat_remove as $belosvyat_id => $belosvyat_label ) {
	if ( ! belosvyat_menu_item_by_id( $belosvyat_items, $belosvyat_id ) ) {
		continue;
	}

	wp_delete_post( $belosvyat_id, true );

	printf( "прибрано пункт «%s»\n", $belosvyat_label );
}

// --- 4. Порядок пунктів ---------------------------------------------------
// Явний порядок: батьки та їхні підпункти поспіль, інакше меню перемішається.
$belosvyat_order = array(
	$belosvyat_home_item_id, // Головна
	126,                     // Новини
	638,                     // Наука
	658,
	6981,
	6977,
	679,
	6982,
	$belosvyat_ecoedu_id,    // Склад відділу екоосвіти та рекреації
	689,                     // Охорона ПЗФ
	722,
	1054,
	6517,                    // Електроний квиток
	6635,                    // ВІАР-ТУР
);

$belosvyat_position = 1;

foreach ( $belosvyat_order as $belosvyat_id ) {
	$belosvyat_post = get_post( $belosvyat_id );

	if ( ! $belosvyat_post || 'nav_menu_item' !== $belosvyat_post->post_type ) {
		continue;
	}

	if ( (int) $belosvyat_post->menu_order !== $belosvyat_position ) {
		wp_update_post(
			array(
				'ID'         => $belosvyat_id,
				'menu_order' => $belosvyat_position,
			)
		);
	}

	$belosvyat_position++;
}

wp_cache_flush();

echo "\nменю після змін:\n";

foreach ( wp_get_nav_menu_items( $belosvyat_menu_id ) as $belosvyat_item ) {
	printf(
		"%-5d ord=%-3d %s%s → %s\n",
		$belosvyat_item->ID,
		$belosvyat_item->menu_order,
		$belosvyat_item->menu_item_parent ? '   └ ' : '',
		$belosvyat_item->title,
		$belosvyat_item->url
	);
}
