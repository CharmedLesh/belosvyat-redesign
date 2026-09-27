<?php
/**
 * Завантажувач теми «Білобережжя Святослава».
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

define( 'BELOSVYAT_VERSION', '2.0.0' );
define( 'BELOSVYAT_DIR', get_template_directory() );
define( 'BELOSVYAT_URI', get_template_directory_uri() );

/**
 * Назва сайту за замовчуванням.
 *
 * У базі цього сайту `blogname` порожній, тому шапка й <title> були б порожні.
 * Значення нижче використовується лише як запасний варіант — щойно назву
 * буде заповнено в «Налаштування → Загальні», вона матиме пріоритет.
 */
define( 'BELOSVYAT_FALLBACK_TITLE', 'Національний природний парк «Білобережжя Святослава»' );

require_once BELOSVYAT_DIR . '/inc/setup.php';
require_once BELOSVYAT_DIR . '/inc/enqueue.php';
require_once BELOSVYAT_DIR . '/inc/widget-areas.php';
require_once BELOSVYAT_DIR . '/inc/nav-walker.php';
require_once BELOSVYAT_DIR . '/inc/template-tags.php';
require_once BELOSVYAT_DIR . '/inc/customizer.php';
require_once BELOSVYAT_DIR . '/inc/shortcodes.php';
require_once BELOSVYAT_DIR . '/inc/legacy-content.php';
require_once BELOSVYAT_DIR . '/inc/gallery.php';
