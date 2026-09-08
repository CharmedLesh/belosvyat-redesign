<?php
/**
 * Коментарі.
 *
 * Стару версію цього файлу було переписано вручну: там не було nonce, поле
 * e-mail мало підпис «Коментар», а змінні $req і $comment_author
 * використовувалися неініціалізованими. Тут усе виводиться штатними
 * функціями WordPress.
 *
 * @package Belosvyat
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="comments">

	<?php if ( have_comments() ) : ?>

		<h2 class="comments__title">
			<?php
			$belosvyat_count = (int) get_comments_number();

			printf(
				/* translators: %d — кількість коментарів. */
				esc_html__( '%1$d %2$s', 'belosvyat' ),
				$belosvyat_count,
				esc_html( belosvyat_plural( $belosvyat_count, 'коментар', 'коментарі', 'коментарів' ) )
			);
			?>
		</h2>

		<ol class="comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '<span aria-hidden="true">&larr;</span><span class="screen-reader-text">' . esc_html__( 'Попередні коментарі', 'belosvyat' ) . '</span>',
				'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Наступні коментарі', 'belosvyat' ) . '</span><span aria-hidden="true">&rarr;</span>',
			)
		);
		?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="comments__closed"><?php esc_html_e( 'Коментування закрито.', 'belosvyat' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'         => __( 'Залишити коментар', 'belosvyat' ),
			'title_reply_to'      => __( 'Відповісти %s', 'belosvyat' ),
			'cancel_reply_link'   => __( 'Скасувати відповідь', 'belosvyat' ),
			'label_submit'        => __( 'Надіслати', 'belosvyat' ),
			'class_submit'        => 'button',
			'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Ваша електронна адреса не буде опублікована.', 'belosvyat' ) . '</p>',
			'comment_field'       => sprintf(
				'<p class="comment-form-comment"><label for="comment">%1$s</label><textarea id="comment" name="comment" cols="45" rows="6" required></textarea></p>',
				esc_html__( 'Коментар', 'belosvyat' )
			),
		)
	);
	?>

</section>
