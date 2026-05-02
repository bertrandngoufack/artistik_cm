<?php
/**
 * View file for Quiz form action settings
 *
 * @package FrmQuizzes
 * @since 2.0.0
 *
 * @var WP_Post          $form_action Form action post object.
 * @var array            $settings    The common settings.
 * @var FrmQuizzesAction $this        Quiz action object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to access this file directly.' );
}

$current_quiz_type = FrmQuizzesFormActionHelper::get_setting_value( $form_action, 'quiz_type' );
?>
<input class="frm-quiz-type-setting" type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'quiz_type' ) ); ?>" value="<?php echo esc_attr( $current_quiz_type ); ?>">
<?php if ( 'scored' === $current_quiz_type ) { ?>
	<p class="frm_form_field">
		<button id="frm_quizzes_edit_quiz" type="button" class="button-primary frm-button-primary">
			<?php esc_html_e( 'Customize Quiz Scoring', 'formidable-quizzes' ); ?>
		</button>
	</p>
<?php } ?>

<?php if ( 'outcome' === $current_quiz_type ) { ?>
	<p class="frm_form_field">
		<button type="button" class="button-primary frm-button-primary frm-quizzes-add-outcome">
			<?php esc_html_e( 'Add another outcome', 'formidable-quizzes' ); ?>
		</button>
	</p>
<?php } ?>
