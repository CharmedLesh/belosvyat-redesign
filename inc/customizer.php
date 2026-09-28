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

		// Головна сторінка. Заголовки й підписи кнопок мають значення за
		// замовчуванням, а описи порожні: розділ покаже лише заголовок, доки
		// текст не заповнять у Кастомайзері.
		'belosvyat_front_about_title' => 'Про парк',
		'belosvyat_front_about_text'  => '',
		'belosvyat_front_flora_title' => 'Рослинний світ',
		'belosvyat_front_flora_text'  => '',
		'belosvyat_front_flora_cta'   => 'Докладніше про флору',
		'belosvyat_front_fauna_title' => 'Тваринний світ',
		'belosvyat_front_fauna_text'  => '',
		'belosvyat_front_fauna_cta'   => 'Докладніше про фауну',
		'belosvyat_front_news_title'  => 'Новини парку',
		'belosvyat_front_news_cta'    => 'Усі новини',
		'belosvyat_front_tour_title'  => 'ВІАР-тур парком',
		'belosvyat_front_tour_text'   => '',
		'belosvyat_front_tour_url'    => '',

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
		'belosvyat_front',
		array(
			'title'       => __( 'Головна сторінка', 'belosvyat' ),
			'description' => __( 'Тексти розділів на «/». Зображення підтягуються зі сторінок «Флора» та «Фауна», новини — з останніх записів.', 'belosvyat' ),
			'priority'    => 35,
		)
	);

	$belosvyat_front_fields = array(
		'belosvyat_front_about_title' => array( 'text', 'sanitize_text_field', 'postMessage', __( 'Про парк: заголовок', 'belosvyat' ), '' ),
		'belosvyat_front_about_text'  => array( 'textarea', 'wp_kses_post', 'postMessage', __( 'Про парк: текст', 'belosvyat' ), __( 'Порожньо — розділ покаже лише заголовок.', 'belosvyat' ) ),
		'belosvyat_front_flora_title' => array( 'text', 'sanitize_text_field', 'postMessage', __( 'Рослини: заголовок', 'belosvyat' ), '' ),
		'belosvyat_front_flora_text'  => array( 'textarea', 'wp_kses_post', 'postMessage', __( 'Рослини: текст', 'belosvyat' ), '' ),
		'belosvyat_front_flora_cta'   => array( 'text', 'sanitize_text_field', 'refresh', __( 'Рослини: напис на кнопці', 'belosvyat' ), __( 'Кнопка веде на сторінку «Флора».', 'belosvyat' ) ),
		'belosvyat_front_fauna_title' => array( 'text', 'sanitize_text_field', 'postMessage', __( 'Тварини: заголовок', 'belosvyat' ), '' ),
		'belosvyat_front_fauna_text'  => array( 'textarea', 'wp_kses_post', 'postMessage', __( 'Тварини: текст', 'belosvyat' ), '' ),
		'belosvyat_front_fauna_cta'   => array( 'text', 'sanitize_text_field', 'refresh', __( 'Тварини: напис на кнопці', 'belosvyat' ), __( 'Кнопка веде на сторінку «Фауна».', 'belosvyat' ) ),
		'belosvyat_front_news_title'  => array( 'text', 'sanitize_text_field', 'postMessage', __( 'Новини: заголовок', 'belosvyat' ), '' ),
		'belosvyat_front_news_cta'    => array( 'text', 'sanitize_text_field', 'refresh', __( 'Новини: напис на кнопці', 'belosvyat' ), __( 'Кнопка веде на сторінку записів.', 'belosvyat' ) ),
		'belosvyat_front_tour_title'  => array( 'text', 'sanitize_text_field', 'postMessage', __( 'ВІАР-тур: заголовок', 'belosvyat' ), '' ),
		'belosvyat_front_tour_text'   => array( 'textarea', 'wp_kses_post', 'postMessage', __( 'ВІАР-тур: текст', 'belosvyat' ), '' ),
		'belosvyat_front_tour_url'    => array( 'url', 'esc_url_raw', 'refresh', __( 'ВІАР-тур: адреса панорами', 'belosvyat' ), __( 'Порожньо — адреса береться з iframe на сторінці «ВІАР-ТУР».', 'belosvyat' ) ),
	);

	foreach ( $belosvyat_front_fields as $belosvyat_key => $belosvyat_field ) {
		$wp_customize->add_setting(
			$belosvyat_key,
			array(
				'default'           => $defaults[ $belosvyat_key ],
				'sanitize_callback' => $belosvyat_field[1],
				'transport'         => $belosvyat_field[2],
			)
		);
		$wp_customize->add_control(
			$belosvyat_key,
			array(
				'label'       => $belosvyat_field[3],
				'description' => $belosvyat_field[4],
				'section'     => 'belosvyat_front',
				'type'        => $belosvyat_field[0],
			)
		);
	}

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

		$belosvyat_front_partials = array(
			'belosvyat_front_about_title' => '.front-section--about .front-section__title',
			'belosvyat_front_about_text'  => '.front-section--about .front-section__text',
			'belosvyat_front_flora_title' => '.front-section--flora .front-section__title',
			'belosvyat_front_flora_text'  => '.front-section--flora .front-section__text',
			'belosvyat_front_fauna_title' => '.front-section--fauna .front-section__title',
			'belosvyat_front_fauna_text'  => '.front-section--fauna .front-section__text',
			'belosvyat_front_news_title'  => '.front-section--news .front-section__title',
			'belosvyat_front_tour_title'  => '.front-section--tour .front-section__title',
			'belosvyat_front_tour_text'   => '.front-section--tour .front-section__text',
		);

		foreach ( $belosvyat_front_partials as $belosvyat_key => $belosvyat_selector ) {
			$wp_customize->selective_refresh->add_partial(
				$belosvyat_key,
				array(
					'selector'        => $belosvyat_selector,
					// Рендеримо так само, як на сервері, щоб перегляд не розходився
					// з готовою сторінкою: ключі на _text — абзаци, решта — текст.
					'render_callback' => static function ( $partial ) {
						$value = belosvyat_option( $partial->id );

						if ( '_text' === substr( $partial->id, -5 ) ) {
							return wpautop( wp_kses_post( $value ) );
						}

						return esc_html( $value );
					},
				)
			);
		}
	}
}
add_action( 'customize_register', 'belosvyat_customize_register' );
