<?php
/**
 * Головна сторінка сайту.
 *
 * Тексти розділів живуть у Кастомайзері («Вигляд → Налаштувати → Головна
 * сторінка»), а зображення беруться зі сторінок «Флора» та «Фауна». Так на
 * сайті немає двох копій тих самих галерей: правка сторінки-джерела одразу
 * змінює головну.
 *
 * Вміст самої сторінки «Головна» навмисно не виводиться — вона потрібна лише
 * як ціль для «Налаштування → Читання».
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="content content--front">

	<?php
	get_template_part( 'template-parts/front/section', 'about' );
	get_template_part( 'template-parts/front/section', 'flora' );
	get_template_part( 'template-parts/front/section', 'fauna' );
	get_template_part( 'template-parts/front/section', 'news' );
	get_template_part( 'template-parts/front/section', 'tour' );
	?>

</main>

<?php
get_footer();
