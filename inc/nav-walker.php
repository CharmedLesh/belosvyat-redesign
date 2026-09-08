<?php
/**
 * Walker головного меню.
 *
 * Додає до пунктів з підменю кнопку-перемикач, доступну з клавіатури.
 * Стара тема мала власний walker без жодної ARIA-розмітки та без підтримки
 * дотику — тут використано штатний Walker_Nav_Menu як основу.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Меню з доступними підменю.
 */
class Belosvyat_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Відкриває підменю.
	 *
	 * @param string   $output Розмітка меню.
	 * @param int      $depth  Глибина вкладеності.
	 * @param stdClass $args   Аргументи wp_nav_menu().
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"sub-menu\">\n";
	}

	/**
	 * Виводить пункт меню.
	 *
	 * @param string   $output Розмітка меню.
	 * @param WP_Post  $item   Пункт меню.
	 * @param int      $depth  Глибина вкладеності.
	 * @param stdClass $args   Аргументи wp_nav_menu().
	 * @param int      $id     ID елемента.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes       = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[]     = 'menu-item-' . $item->ID;
		$has_children  = in_array( 'menu-item-has-children', $classes, true );
		$class_names   = implode( ' ', array_filter( apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) ) );
		$is_current    = in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true );

		$output .= sprintf( '<li class="%s">', esc_attr( $class_names ) );

		$attributes = array(
			'href'   => ! empty( $item->url ) ? $item->url : '',
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
		);

		$attr_html = '';
		foreach ( $attributes as $key => $value ) {
			if ( '' === $value ) {
				continue;
			}
			$attr_html .= sprintf( ' %s="%s"', $key, 'href' === $key ? esc_url( $value ) : esc_attr( $value ) );
		}

		if ( $is_current ) {
			$attr_html .= ' aria-current="page"';
		}

		/*
		 * Частина пунктів цього меню збережена з порожнім post_title —
		 * WordPress підставляє назву прив'язаної сторінки у $item->title.
		 */
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$output .= sprintf(
			'<a class="menu-link"%s>%s</a>',
			$attr_html,
			esc_html( $title )
		);

		if ( $has_children ) {
			$output .= sprintf(
				'<button type="button" class="submenu-toggle" aria-expanded="false"><span class="screen-reader-text">%s</span><svg class="submenu-toggle__icon" width="12" height="8" viewBox="0 0 12 8" aria-hidden="true" focusable="false"><path d="M1 1.5 6 6.5 11 1.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>',
				/* translators: %s — назва пункту меню. */
				esc_html( sprintf( __( 'Розгорнути підменю «%s»', 'belosvyat' ), $title ) )
			);
		}
	}
}
