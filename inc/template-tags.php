<?php
/**
 * Функції виводу для шаблонів.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Шлях запасного зображення відносно теки завантажень (wp-content/uploads).
 *
 * @return string
 */
function belosvyat_fallback_thumbnail_path() {
	/**
	 * Дозволяє змінити файл-заглушку без правки теми.
	 *
	 * @param string $path Шлях відносно теки завантажень.
	 */
	return ltrim( (string) apply_filters( 'belosvyat_fallback_thumbnail_path', '2018/02/NPP_1.jpg' ), '/' );
}

/**
 * ID вкладення запасного зображення (щоб отримати потрібний розмір і srcset).
 *
 * Пошук за URL відносно дорогий, тому результат кешуємо на добу.
 *
 * @return int ID вкладення або 0, якщо в медіатеці його немає.
 */
function belosvyat_fallback_thumbnail_id() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$transient = get_transient( 'belosvyat_fallback_thumbnail_id' );

	if ( false !== $transient ) {
		$cached = (int) $transient;

		return $cached;
	}

	$uploads = wp_get_upload_dir();
	$url     = trailingslashit( $uploads['baseurl'] ) . belosvyat_fallback_thumbnail_path();
	$cached  = (int) attachment_url_to_postid( $url );

	set_transient( 'belosvyat_fallback_thumbnail_id', $cached, DAY_IN_SECONDS );

	return $cached;
}

/**
 * Джерело запасного зображення.
 *
 * @return array Ключі id, url, width, height.
 */
function belosvyat_fallback_thumbnail_source() {
	$source = array(
		'id'     => belosvyat_fallback_thumbnail_id(),
		'url'    => '',
		'width'  => 0,
		'height' => 0,
	);

	if ( $source['id'] ) {
		return $source;
	}

	// Файл є на диску, але поза медіатекою — віддаємо його прямим посиланням.
	$uploads = wp_get_upload_dir();
	$path    = belosvyat_fallback_thumbnail_path();

	if ( file_exists( trailingslashit( $uploads['basedir'] ) . $path ) ) {
		$source['url'] = trailingslashit( $uploads['baseurl'] ) . $path;
	}

	return $source;
}

/**
 * ID вкладення за URL, з урахуванням суфікса розміру (-300x295).
 *
 * @param string $url Посилання на файл.
 * @return int ID вкладення або 0.
 */
function belosvyat_attachment_id_from_url( $url ) {
	static $cache = array();

	if ( isset( $cache[ $url ] ) ) {
		return $cache[ $url ];
	}

	$clean = strtok( $url, '?' );
	$id    = (int) attachment_url_to_postid( $clean );

	if ( ! $id ) {
		$full = preg_replace( '#-\d+x\d+(?=\.[a-z0-9]+$)#i', '', $clean );

		if ( $full !== $clean ) {
			$id = (int) attachment_url_to_postid( $full );
		}
	}

	$cache[ $url ] = $id;

	return $id;
}

/**
 * Джерело мініатюри запису з ланцюжком запасних варіантів.
 *
 * Більшість архівних записів створені до появи «зображення запису», тому
 * послідовно перевіряємо: зображення запису → перше вкладення → перший <img>
 * у тексті. Це поведінка старої theme_get_post_thumbnail(), збережена свідомо:
 * без неї сітка карток на архівах була б майже порожня.
 *
 * @param int $post_id ID запису.
 * @return array Ключі id, url, width, height.
 */
function belosvyat_get_thumbnail_source( $post_id ) {
	$source = array(
		'id'     => 0,
		'url'    => '',
		'width'  => 0,
		'height' => 0,
	);

	if ( has_post_thumbnail( $post_id ) ) {
		$source['id'] = (int) get_post_thumbnail_id( $post_id );

		return $source;
	}

	$attachments = get_children(
		array(
			'post_parent'    => $post_id,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => 'ASC',
			'orderby'        => 'menu_order ID',
			'numberposts'    => 1,
		)
	);

	if ( ! empty( $attachments ) ) {
		$attachment = reset( $attachments );

		$source['id'] = (int) $attachment->ID;

		return $source;
	}

	// Останній шанс: перший <img> просто в тексті запису.
	$content = get_post_field( 'post_content', $post_id );

	if ( ! $content || ! preg_match( '#<img[^>]+>#i', $content, $tag ) ) {
		return $source;
	}

	if ( ! preg_match( '#\ssrc=["\']([^"\']+)["\']#i', $tag[0], $src ) ) {
		return $source;
	}

	// Редактор лишає width/height у розмітці — цього досить, щоб визначити
	// орієнтацію без зайвого запиту до бази.
	$has_width  = preg_match( '#\swidth=["\']?(\d+)#i', $tag[0], $width );
	$has_height = preg_match( '#\sheight=["\']?(\d+)#i', $tag[0], $height );

	if ( $has_width && $has_height ) {
		$source['url']    = $src[1];
		$source['width']  = (int) $width[1];
		$source['height'] = (int) $height[1];

		return $source;
	}

	// Розмірів у розмітці немає — шукаємо вкладення, щоб узяти їх із медіатеки.
	$source['id'] = belosvyat_attachment_id_from_url( $src[1] );

	if ( ! $source['id'] ) {
		$source['url'] = $src[1];
	}

	return $source;
}

/**
 * Мініатюра запису: розмітка та орієнтація зображення.
 *
 * @param string $size Розмір зображення.
 * @return array Ключі html, orientation, is_fallback.
 */
function belosvyat_get_thumbnail_info( $size = 'belosvyat-card' ) {
	static $cache = array();

	$post_id = get_the_ID();
	$key     = $post_id . ':' . ( is_array( $size ) ? implode( 'x', $size ) : $size );

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$info = array(
		'html'        => '',
		'orientation' => 'landscape',
		'is_fallback' => false,
	);

	if ( ! $post_id ) {
		return $info;
	}

	$attr = array(
		'alt'      => '',
		'loading'  => 'lazy',
		'decoding' => 'async',
		'class'    => 'entry__image',
	);

	$source = belosvyat_get_thumbnail_source( $post_id );

	if ( ! $source['id'] && '' === $source['url'] ) {
		$source              = belosvyat_fallback_thumbnail_source();
		$info['is_fallback'] = true;
		$attr['class']      .= ' entry__image--fallback';
	}

	if ( $source['id'] ) {
		$info['html'] = wp_get_attachment_image( $source['id'], $size, false, $attr );

		$full = wp_get_attachment_image_src( $source['id'], 'full' );

		if ( $full ) {
			$source['width']  = (int) $full[1];
			$source['height'] = (int) $full[2];
		}
	} elseif ( '' !== $source['url'] ) {
		$attributes = '';

		foreach ( $attr as $name => $value ) {
			$attributes .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		$info['html'] = sprintf(
			'<img src="%s"%s />',
			esc_url( $source['url'] ),
			$attributes // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Атрибути екрановані вище.
		);
	}

	if ( $source['width'] && $source['height'] && $source['height'] > $source['width'] ) {
		$info['orientation'] = 'portrait';
	}

	$cache[ $key ] = $info;

	return $info;
}

/**
 * Мініатюра запису з ланцюжком запасних варіантів.
 *
 * @param string $size Розмір зображення.
 * @return string HTML тега <img> або порожній рядок.
 */
function belosvyat_get_thumbnail_html( $size = 'belosvyat-card' ) {
	$info = belosvyat_get_thumbnail_info( $size );

	return $info['html'];
}

/**
 * Клас картки за орієнтацією мініатюри.
 *
 * Вертикальні зображення ставимо збоку від тексту, горизонтальні — над ним.
 * Саму розкладку вмикає CSS від 700px, на мобільних зображення завжди зверху.
 *
 * @param string $size Розмір зображення.
 * @return string
 */
function belosvyat_entry_orientation_class( $size = 'belosvyat-card' ) {
	$info = belosvyat_get_thumbnail_info( $size );

	return 'portrait' === $info['orientation'] ? 'entry--portrait' : 'entry--landscape';
}

/**
 * Виводить мініатюру як посилання на запис.
 *
 * @param string $size Розмір зображення.
 */
function belosvyat_entry_thumbnail( $size = 'belosvyat-card' ) {
	$info = belosvyat_get_thumbnail_info( $size );

	// Немає ані власного зображення, ані файлу-заглушки — лишаємо порожній блок.
	if ( '' === $info['html'] ) {
		echo '<div class="entry__media entry__media--placeholder" aria-hidden="true"></div>';
		return;
	}

	printf(
		'<a class="entry__media%s" href="%s" tabindex="-1" aria-hidden="true">%s</a>',
		$info['is_fallback'] ? ' entry__media--fallback' : '',
		esc_url( get_permalink() ),
		$info['html'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML зображення від WordPress.
	);
}

/**
 * Метадані запису: дата, рубрика, перегляди.
 *
 * @param array $show Які елементи виводити.
 */
function belosvyat_post_meta( $show = array( 'date', 'category' ) ) {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	$items = array();

	if ( in_array( 'date', $show, true ) ) {
		$items[] = sprintf(
			'<time class="entry__date" datetime="%s">%s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);
	}

	if ( in_array( 'category', $show, true ) ) {
		$categories = get_the_category();

		if ( ! empty( $categories ) ) {
			$items[] = sprintf(
				'<a class="entry__category" href="%s">%s</a>',
				esc_url( get_category_link( $categories[0]->term_id ) ),
				esc_html( $categories[0]->name )
			);
		}
	}

	// Плагін WP-PostViews, якщо активний.
	if ( in_array( 'views', $show, true ) && function_exists( 'get_the_views' ) ) {
		$items[] = '<span class="entry__views">' . wp_kses_post( get_the_views() ) . '</span>';
	}

	if ( empty( $items ) ) {
		return;
	}

	echo '<div class="entry__meta">' . implode( '<span class="entry__meta-sep" aria-hidden="true">·</span>', $items ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Посторінкова навігація.
 *
 * Плагін WP-PageNavi, якщо активний, має пріоритет — так було і в старій темі.
 */
function belosvyat_pagination() {
	if ( function_exists( 'wp_pagenavi' ) ) {
		wp_pagenavi();
		return;
	}

	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'end_size'           => 1,
			'prev_text'          => '<span aria-hidden="true">&larr;</span><span class="screen-reader-text">' . __( 'Попередня сторінка', 'belosvyat' ) . '</span>',
			'next_text'          => '<span class="screen-reader-text">' . __( 'Наступна сторінка', 'belosvyat' ) . '</span><span aria-hidden="true">&rarr;</span>',
			'screen_reader_text' => __( 'Навігація сторінками', 'belosvyat' ),
			'aria_label'         => __( 'Сторінки', 'belosvyat' ),
		)
	);
}

/**
 * Навігація між сусідніми записами.
 */
function belosvyat_post_nav() {
	the_post_navigation(
		array(
			'prev_text'  => '<span class="post-nav__label">' . __( 'Попередній запис', 'belosvyat' ) . '</span><span class="post-nav__title">%title</span>',
			'next_text'  => '<span class="post-nav__label">' . __( 'Наступний запис', 'belosvyat' ) . '</span><span class="post-nav__title">%title</span>',
			'aria_label' => __( 'Записи', 'belosvyat' ),
		)
	);
}

/**
 * Рубрики у вигляді «пігулок».
 *
 * @param int $limit Скільки рубрик показати.
 */
function belosvyat_category_chips( $limit = 8 ) {
	$categories = get_categories(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
			'hide_empty' => true,
		)
	);

	if ( empty( $categories ) ) {
		return;
	}

	echo '<nav class="chips" aria-label="' . esc_attr__( 'Рубрики', 'belosvyat' ) . '"><ul class="chips__list">';

	foreach ( $categories as $category ) {
		printf(
			'<li class="chips__item"><a class="chip%s" href="%s">%s<span class="chip__count">%d</span></a></li>',
			is_category( $category->term_id ) ? ' is-current' : '',
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name ),
			(int) $category->count
		);
	}

	echo '</ul></nav>';
}

/**
 * Прибирає префікси «Рубрика:», «Мітка:» із заголовків архівів.
 *
 * @param string $prefix Префікс.
 * @return string
 */
function belosvyat_archive_title_prefix( $prefix ) {
	return '';
}
add_filter( 'get_the_archive_title_prefix', 'belosvyat_archive_title_prefix' );

/**
 * Хлібні крихти.
 */
function belosvyat_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items = array(
		sprintf( '<a href="%s">%s</a>', esc_url( home_url( '/' ) ), esc_html__( 'Головна', 'belosvyat' ) ),
	);

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();

		if ( ! empty( $categories ) ) {
			$items[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_category_link( $categories[0]->term_id ) ),
				esc_html( $categories[0]->name )
			);
		}

		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_permalink( $ancestor ) ),
				esc_html( get_the_title( $ancestor ) )
			);
		}

		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'Результати пошуку', 'belosvyat' ) . '</span>';
	} elseif ( is_404() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'Сторінку не знайдено', 'belosvyat' ) . '</span>';
	} elseif ( is_archive() ) {
		$items[] = '<span aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	}

	if ( count( $items ) < 2 ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Навігаційний ланцюжок', 'belosvyat' ) . '">'
		. implode( '<span class="breadcrumbs__sep" aria-hidden="true">/</span>', $items )
		. '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Бренд у шапці: логотип, або назва сайту, або резервна назва.
 */
function belosvyat_site_branding() {
	if ( has_custom_logo() ) {
		echo '<div class="brand__logo">';
		the_custom_logo();
		echo '</div>';
	}

	$title       = belosvyat_site_title();
	$description = get_bloginfo( 'description', 'display' );
	/*
	 * На головній h1 належить банеру, тому назва в шапці стає <p>.
	 * H1 у шапці лишається тільки тоді, коли банер вимкнено — щоб на
	 * сторінці завжди був рівно один заголовок першого рівня.
	 */
	$tag         = ( is_front_page() && ! is_paged() && ! belosvyat_hero_is_enabled() ) ? 'h1' : 'p';

	printf(
		'<%1$s class="brand__title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
		esc_attr( $tag ),
		esc_url( home_url( '/' ) ),
		esc_html( $title )
	);

	if ( $description ) {
		printf( '<p class="brand__tagline">%s</p>', esc_html( $description ) );
	}
}

/**
 * Українська форма множини.
 *
 * Рядки теми написані українською без файлу перекладу, тому _n() застосував би
 * англійське правило (одне число проти всіх інших) і давав би «3 матеріалів»
 * замість «3 матеріали». Тут реалізовано власне правило мови.
 *
 * @param int    $number Число.
 * @param string $one    Форма для 1, 21, 31…   («матеріал»).
 * @param string $few    Форма для 2–4, 22–24… («матеріали»).
 * @param string $many   Форма для 5–20, 25–30…(«матеріалів»).
 * @return string
 */
function belosvyat_plural( $number, $one, $few, $many ) {
	$number = absint( $number );
	$mod10  = $number % 10;
	$mod100 = $number % 100;

	if ( 1 === $mod10 && 11 !== $mod100 ) {
		return $one;
	}

	if ( $mod10 >= 2 && $mod10 <= 4 && ( $mod100 < 12 || $mod100 > 14 ) ) {
		return $few;
	}

	return $many;
}

/**
 * Чи ховати службовий заголовок сторінки.
 *
 * На кількох сторінках назва продубльована першим рядком самого тексту, тож
 * заголовок теми лише повторює її. Ховаємо його візуально — у розмітці <h1>
 * лишається для читалок і пошукових систем.
 *
 * Список ведемо за слагами, а не за ID: слаг однаковий і локально, і на бою.
 *
 * @return bool
 */
function belosvyat_page_title_is_hidden() {
	/**
	 * Слаги сторінок, де заголовок дублює текст.
	 *
	 * @param array $slugs Слаги сторінок.
	 */
	$slugs = apply_filters( 'belosvyat_hidden_page_titles', array( 'sklad-viddilu-2' ) );

	return ! empty( $slugs ) && is_page( $slugs );
}
