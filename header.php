<?php
/**
 * Шапка сайту.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Перейти до основного вмісту', 'belosvyat' ); ?></a>

<div class="site">

	<header class="site-header" id="site-header">
		<div class="site-header__inner container">

			<div class="brand">
				<?php belosvyat_site_branding(); ?>
			</div>

			<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Головне меню', 'belosvyat' ); ?>">
				<?php
				if ( has_nav_menu( 'primary-menu' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary-menu',
							'container'      => false,
							'menu_class'     => 'primary-nav__list',
							'depth'          => 3,
							'walker'         => new Belosvyat_Nav_Walker(),
						)
					);
				} else {
					printf(
						'<p class="primary-nav__empty"><a href="%s">%s</a></p>',
						esc_url( admin_url( 'nav-menus.php' ) ),
						esc_html__( 'Створити меню', 'belosvyat' )
					);
				}
				?>
			</nav>

			<div class="site-header__actions">
				<button type="button" class="icon-button" id="search-toggle" aria-expanded="false" aria-controls="site-search">
					<span class="screen-reader-text"><?php esc_html_e( 'Пошук по сайту', 'belosvyat' ); ?></span>
					<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
						<circle cx="9" cy="9" r="6" fill="none" stroke="currentColor" stroke-width="2" />
						<path d="M13.5 13.5 18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
					</svg>
				</button>

				<button type="button" class="icon-button menu-toggle" id="menu-toggle" aria-expanded="false" aria-controls="primary-nav">
					<span class="screen-reader-text"><?php esc_html_e( 'Меню', 'belosvyat' ); ?></span>
					<span class="menu-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
				</button>
			</div>

		</div>

		<div class="site-search" id="site-search" hidden>
			<div class="container">
				<?php get_search_form(); ?>
			</div>
		</div>
	</header>

	<?php
	if ( ( is_front_page() || is_home() ) && ! is_paged() && belosvyat_hero_is_enabled() ) {
		get_template_part( 'template-parts/hero' );
	}
	?>

	<div class="site-content" id="content">
		<div class="container layout">
