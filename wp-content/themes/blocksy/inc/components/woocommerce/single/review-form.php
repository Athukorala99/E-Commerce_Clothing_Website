<?php

if (! function_exists('blocksy_product_review_comment_form_args')) {
	function blocksy_product_review_comment_form_args($comment_form) {
		$comment_form['comment_field'] = str_replace(
			'comment-form-comment',
			'comment-form-field-textarea',
			$comment_form['comment_field']
		);

		$comment_form['submit_button'] = '<button name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s">%4$s</button>';

		if (! is_user_logged_in()) {
			$author = '<p class="comment-form-field-input-author"><label for="author">' . esc_html__( 'Name', 'blocksy' ) . '<span class="required">&nbsp;*</span></label><input id="author" name="author" type="text" value="" size="30" required></p>';

			$email = '<p class="comment-form-field-input-email"><label for="email">' . esc_html__( 'Email', 'blocksy' ) . '<span class="required">&nbsp;*</span></label><input id="email" name="email" type="email" value="" size="30" required></p>';
			
			unset($comment_form['fields']['email']);
			unset($comment_form['fields']['author']);

			$comment_form['comment_field'] = str_replace(
				'<p class="comment-form-field-textarea">',
				$author . $email .
				'<p class="comment-form-field-textarea">',
				$comment_form['comment_field']
			);
		}

		return $comment_form;
	}
}

add_filter(
	'woocommerce_product_review_comment_form_args',
	'blocksy_product_review_comment_form_args',
	10, 1
);