<?php
/**
 * Області віджетів.
 *
 * Ідентифікатори (`id`) успадковані від старої теми навмисно: у базі вже
 * розкладені віджети саме за цими ключами. Зміна id знеструмила б бічну панель.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Реєструє області віджетів.
 */
function belosvyat_widget_areas() {
	$defaults = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	);

	$areas = array(
		'primary-widget-area'       => __( 'Бічна панель', 'belosvyat' ),
		'secondary-widget-area'     => __( 'Додаткова бічна панель', 'belosvyat' ),
		'first-top-widget-area'     => __( 'Над контентом — 1', 'belosvyat' ),
		'second-top-widget-area'    => __( 'Над контентом — 2', 'belosvyat' ),
		'first-bottom-widget-area'  => __( 'Під контентом — 1', 'belosvyat' ),
		'second-bottom-widget-area' => __( 'Під контентом — 2', 'belosvyat' ),
		'first-footer-widget-area'  => __( 'Підвал — колонка 1', 'belosvyat' ),
		'second-footer-widget-area' => __( 'Підвал — колонка 2', 'belosvyat' ),
		'third-footer-widget-area'  => __( 'Підвал — колонка 3', 'belosvyat' ),
		'fourth-footer-widget-area' => __( 'Підвал — колонка 4', 'belosvyat' ),
	);

	foreach ( $areas as $id => $name ) {
		register_sidebar(
			array_merge(
				$defaults,
				array(
					'id'          => $id,
					'name'        => $name,
					'description' => __( 'Перетягніть сюди віджети.', 'belosvyat' ),
				)
			)
		);
	}
}
add_action( 'widgets_init', 'belosvyat_widget_areas' );

/**
 * Чи показувати бічну панель на поточному екрані.
 *
 * @return bool
 */
function belosvyat_has_sidebar() {
	// Головна має власну структуру на всю ширину.
	if ( belosvyat_is_static_front_page() ) {
		return false;
	}

	if ( is_page_template( 'onecolumn-page.php' ) || is_404() || is_attachment() ) {
		return false;
	}

	return (bool) is_active_sidebar( 'primary-widget-area' );
}

/**
 * Чи має підвал хоч одну заповнену колонку.
 *
 * @return bool
 */
function belosvyat_footer_columns() {
	$columns = array();

	foreach ( array( 'first-footer-widget-area', 'second-footer-widget-area', 'third-footer-widget-area', 'fourth-footer-widget-area' ) as $id ) {
		if ( is_active_sidebar( $id ) ) {
			$columns[] = $id;
		}
	}

	return $columns;
}
