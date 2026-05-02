<?php
/**
 * Class FrmQuizzesScoredController
 *
 * @package FrmQuizzes
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmQuizzesScoredController {

	/**
	 * Gets check data for a field.
	 *
	 * @param FrmFieldValue $field_value Field value object.
	 * @param object        $form_action Quiz action object.
	 * @return array See {@see FrmQuizzes::get_default_field_check_result()}.
	 */
	public static function check_field( $field_value, $form_action ) {
		$scoring = new FrmQuizzes( compact( 'form_action' ) );
		return $scoring->check_field( $field_value );
	}

	/**
	 * Enqueues scripts.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		FrmQuizzesAppHelper::maybe_inline_css( 'result' );
	}

	/**
	 * AJAX handler for setting manual score.
	 */
	public static function ajax_set_manual_score() {
		check_ajax_referer( 'frm_quizzes_ajax' );
		if ( ! current_user_can( 'frm_edit_entries' ) ) {
			wp_send_json_error( __( 'You don\'t have permission to do that.', 'formidable-quizzes' ) );
		}

		FrmQuizzesManualHelper::maybe_set_manual_score();
	}

	/**
	 * Gets form submission success message.
	 *
	 * @param string $message  Success message.
	 * @param object $form     Form object.
	 * @param int    $entry_id Entry ID.
	 * @return string
	 */
	public static function get_success_message( $message, $form, $entry_id ) {
		$quiz_action = FrmQuizzesFormActionHelper::get_quiz_action_from_form( $form->id, true );
		if ( ! $quiz_action ) {
			return $message;
		}

		$show_result = $quiz_action->post_content['show_result'];
		if ( ! $show_result ) {
			return $message;
		}

		$atts = array(
			'id'             => $entry_id,
			'inline_style'   => 0,
			'class'          => 'frm-line-table',
			'show_image'     => true,
			'size'           => 'thumbnail',
			'include_extras' => 'section,html,score', // 'score' will include 1/1 with the questions.
		);

		if ( $show_result === 'correct_answers' ) {
			$atts['format'] = 'quiz_' . $show_result;
			$atts['class'] .= ' frm_quizzes_result';
		}

		if ( $show_result === 'user_answers' || $show_result === 'correct_answers' ) {
			$message .= self::show_result_with_answers( $atts );
		} else {
			$atts                = compact( 'entry_id', 'message' );
			$atts['form_action'] = $quiz_action;

			ob_start();
			self::show_result_score( $atts );
			$message = ob_get_clean();
		}

		return $message;
	}

	/**
	 * Shows result in case Show user answer.
	 *
	 * @since 2.0
	 *
	 * @param array $atts The array of options to send to FrmProEntriesController::show_entry_shortcode().
	 *
	 * @return string
	 */
	private static function show_result_with_answers( $atts ) {
		$content  = '<div class="with_frm_style">';
		$content .= '<div class="frm-summary-page-wrapper">';
		$content .= FrmProEntriesController::show_entry_shortcode( $atts );
		$content .= '</div>';
		$content .= '</div>';
		return $content;
	}

	/**
	 * Shows result in case Show score.
	 *
	 * @since 2.0
	 *
	 * @param array $atts Includes entry_id, message, and form_action.
	 */
	public static function show_result_score( $atts ) {
		self::enqueue_scripts();

		$scoring       = new FrmQuizzes( $atts );
		$total_score   = $scoring->get_score();
		$answers_count = $scoring->get_question_count();
		$message       = $atts['message'];
		$percent       = ( $total_score / $answers_count ) * 100;
		$class         = $percent < 80 ? '' : 'frm_high_score';
		$bg_img        = apply_filters( 'frm_quiz_bg', FrmQuizzesAppController::plugin_url() . '/images/result-bg.png', compact( 'percent' ) );

		include FrmQuizzesAppController::path() . '/views/quiz/show-score.php';
	}

	/**
	 * Deletes embedded form fields from quiz scoring fields if an embedded field is deleted from a form with Scored quiz action.
	 *
	 * @since 3.1.3
	 * @param int $field_id
	 * @return void
	 */
	public static function update_score_fields( $field_id ) {
		$field = FrmDb::get_row( 'frm_fields', array( 'id' => $field_id ), 'form_id, field_options' );
		if ( ! $field ) {
			return;
		}

		FrmAppHelper::unserialize_or_decode( $field->field_options );

		if ( empty( $field->field_options['form_select'] ) ) {
			return;
		}

		$quiz_action = FrmQuizzesFormActionHelper::get_quiz_action_from_form( $field->form_id, true );

		if ( ! $quiz_action || ( isset( $quiz_action->post_content['quiz_type'] ) && $quiz_action->post_content['quiz_type'] === 'outcome' ) ) {
			return;
		}

		$embedded_fields = FrmDb::get_col( 'frm_fields', array( 'form_id' => $field->field_options['form_select'] ) );

		if ( is_array( $quiz_action->post_content ) ) {
			/**
			 * @psalm-suppress InvalidPropertyAssignmentValue
			 */
			$quiz_action->post_content['enable'] = array_diff( $quiz_action->post_content['enable'], $embedded_fields );

			/**
			 * @psalm-suppress InvalidPropertyAssignmentValue
			 */
			$quiz_action->post_content['quiz'] = array_filter(
				$quiz_action->post_content['quiz'],
				function( $quiz ) use ( $embedded_fields ) {
					return ! in_array( $quiz['id'], $embedded_fields, true );
				}
			);
		}

		$quiz_action->post_content = FrmAppHelper::prepare_and_encode( $quiz_action->post_content );

		FrmFormAction::clear_cache();

		/**
		 * @psalm-suppress InvalidArgument
		 */
		wp_update_post( $quiz_action, false, false );
	}
}
