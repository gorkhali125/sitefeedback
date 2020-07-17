<?php

namespace Drupal\sitefeedback\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/**
 * Class TestForm.
 */
class SiteFeedbackForm extends FormBase {

  private $feedbackService;

  public function __construct() {
    //Check if the request is not via ajax. If not via ajax, return not found
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
      throw new NotFoundHttpException();
    }

    $this->feedbackService = \Drupal::service('sitefeedback.service');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sitefeedback_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $weight = 0;

    $form['feedback_description'] = [
      '#type' => 'item',
      '#description' => $this->t('We really appreciate your feedback and will try to address it as soon as possible.'),
      '#weight' => $weight++,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#id' => 'feedback_name',
      '#required' => TRUE,
      '#maxlength' => 255,
      '#attributes' => [
        'placeholder' => $this->t('Enter your name.'),
        'autocomplete' => 'off',
      ],
      '#suffix' => '<div class="error-msg" id="feedback_name-error-msg"></div>',
      '#weight' => $weight++,
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#id' => 'feedback_email',
      '#required' => TRUE,
      '#maxlength' => 255,
      '#attributes' => [
        'classes' => ['required'],
        'placeholder' => $this->t('Enter your email.'),
        'autocomplete' => 'off',
      ],
      '#suffix' => '<div class="error-msg" id="feedback_email-error-msg"></div>',
      '#weight' => $weight++,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#id' => 'feedback_message',
      '#required' => TRUE,
      '#rows' => 5,
      '#attributes' => [
        'placeholder' => $this->t('Enter your message.'),
        'autocomplete' => 'off',
      ],
      '#suffix' => '<div class="error-msg" id="feedback_message-error-msg"></div>',
      '#weight' => $weight++,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::feedbackSubmit',
      ],
      '#weight' => $weight++,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    $email = $form_values['email'];
    if (!empty($email) && !\Drupal::service('email.validator')
        ->isValid($email)) {
      $form_state->setErrorByName('email', 'Please enter a valid email.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public function feedbackSubmit(array &$form, FormStateInterface $form_state) {
    $errors = $form_state->getErrors();
    $required_fields = ['name', 'email', 'message'];

    unset($_SESSION['_symfony_flashes']['error']);

    $ajaxResponse = new AjaxResponse();
    if ($errors) {
      foreach ($required_fields as $rfield) {
        if (isset($errors[$rfield])) {
          $ajaxResponse
            ->addCommand(new InvokeCommand('#feedback_' . $rfield, 'addClass', ['error']));
        }
        else {
          $ajaxResponse
            ->addCommand(new InvokeCommand('#feedback_' . $rfield, 'removeClass', ['error']));
        }
      }
    }
    else {
      $form_values = $form_state->getValues();

      //Prepare the feedback array
      $feedback = [];
      $feedback['name'] = Html::escape($form_values['name']);
      $feedback['email'] = Html::escape($form_values['email']);
      $feedback['message'] = Html::escape($form_values['message']);

      //Save the feedback object
      $saved_feedback = $this->feedbackService->saveFeedback($feedback);
      if ($saved_feedback) {
        \Drupal::messenger()->addStatus($this->t('Feedback submitted successfully.'));
      }else{
        \Drupal::messenger()->addError($this->t('Some error occurred while saving the feedback.'));
      }
      $status_messages = array(
        '#type' => 'status_messages',
      );
      $ajaxResponse->addCommand(new AppendCommand('.region-highlighted', $status_messages));
      $ajaxResponse->addCommand(new CloseModalDialogCommand());
    }

    return $ajaxResponse;

  }

}
