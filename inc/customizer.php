<?php
/**
 * Налаштування теми у Кастомайзері.
 *
 * Замінює саморобну панель опцій Artisteer (library/options.php + admins.php),
 * яка зберігала ~45 окремих рядків у wp_options без Settings API та без nonce.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Значення налаштувань теми за замовчуванням.
 *
 * @return array
 */
function belosvyat_defaults() {
	return array(
		'belosvyat_hero_enabled'  => true,
		'belosvyat_hero_title'    => '',
		'belosvyat_hero_subtitle' => 'Природоохоронна, науково-дослідна та рекреаційна установа на узбережжі Дніпро-Бузького лиману',
		'belosvyat_hero_cta_text' => 'Новини парку',
		'belosvyat_hero_cta_url'  => '',
		'belosvyat_footer_text'   => '© [year] Національний природний парк «Білобережжя Святослава»',
	);
}

/**
 * Читає налаштування теми.
 *
 * @param string $key Ключ налаштування.
 * @return mixed
 */
function belosvyat_option( $key ) {
	$defaults = belosvyat_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

	return get_theme_mod( $key, $default );
}

/**
 * Чи показувати банер на головній.
 *
 * @return bool
 */
function belosvyat_hero_is_enabled() {
	return (bool) belosvyat_option( 'belosvyat_hero_enabled' );
}

/**
 * Реєструє секції та поля Кастомайзера.
 *
 * @param WP_Customize_Manager $wp_customize Менеджер Кастомайзера.
 */
function belosvyat_customize_register( $wp_customize ) {
	$defaults = belosvyat_defaults();

	$wp_customize->add_section(
		'belosvyat_hero',
		array(
			'title'       => __( 'Банер на головній', 'belosvyat' ),
			'description' => __( 'Фонове зображення банера задається у розділі «Зображення шапки».', 'belosvyat' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_setting(
		'belosvyat_hero_enabled',
		array(
			'default'           => $defaults['belosvyat_hero_enabled'],
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'belosvyat_hero_enabled',
		array(
			'label'   => __( 'Показувати банер', 'belosvyat' ),
			'section' => 'belosvyat_hero',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'belosvyat_hero_title',
		array(
			'default'           => $defaults['belosvyat_hero_title'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'belosvyat_hero_title',
		array(
			'label'       => __( 'Заголовок банера', 'belosvyat' ),
			'description' => __( 'Порожньо — використовується назва сайту.', 'belosvyat' ),
			'section'     => 'belosvyat_hero',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'belosvyat_hero_subtitle',
		array(
			'default'           => $defaults['belosvyat_hero_subtitle'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'belosvyat_hero_subtitle',
		array(
			'label'   => __( 'Підзаголовок банера', 'belosvyat' ),
			'section' => 'belosvyat_hero',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'belosvyat_hero_cta_text',
		array(
			'default'           => $defaults['belosvyat_hero_cta_text'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'belosvyat_hero_cta_text',
		array(
			'label'   => __( 'Текст кнопки', 'belosvyat' ),
			'section' => 'belosvyat_hero',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'belosvyat_hero_cta_url',
		array(
			'default'           => $defaults['belosvyat_hero_cta_url'],
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'belosvyat_hero_cta_url',
		array(
			'label'       => __( 'Посилання кнопки', 'belosvyat' ),
			'description' => __( 'Порожньо — кнопка веде на стрічку новин.', 'belosvyat' ),
			'section'     => 'belosvyat_hero',
			'type'        => 'url',
		)
	);

	$wp_customize->add_section(
		'belosvyat_footer',
		array(
			'title'    => __( 'Підвал', 'belosvyat' ),
			'priority' => 130,
		)
	);

	$wp_customize->add_setting(
		'belosvyat_footer_text',
		array(
			'default'           => $defaults['belosvyat_footer_text'],
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'belosvyat_footer_text',
		array(
			'label'       => __( 'Текст унизу сторінки', 'belosvyat' ),
			'description' => __( 'Підтримується шорткод [year].', 'belosvyat' ),
			'section'     => 'belosvyat_footer',
			'type'        => 'textarea',
		)
	);

	// Живий перегляд для текстових полів.
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'belosvyat_hero_subtitle',
			array(
				'selector'        => '.hero__subtitle',
				'render_callback' => static function () {
					return esc_html( belosvyat_option( 'belosvyat_hero_subtitle' ) );
				},
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'belosvyat_footer_text',
			array(
				'selector'        => '.site-footer__copyright',
				'render_callback' => static function () {
					return do_shortcode( wp_kses_post( belosvyat_option( 'belosvyat_footer_text' ) ) );
				},
			)
		);
	}
}
add_action( 'customize_register', 'belosvyat_customize_register' );
