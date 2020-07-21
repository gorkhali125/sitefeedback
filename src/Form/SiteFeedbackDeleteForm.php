<?php

namespace Drupal\sitefeedback\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TestForm.
 */
class SiteFeedbackDeleteForm extends ConfirmFormBase {

  /**
   * Feedback Service.
   *
   * @var mixed
   */
  private $feedbackService;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->feedbackService = \Drupal::service('sitefeedback.service');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sitefeedback_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('sitefeedback.admin_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this feedback?');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    if (!$this->feedbackService->feedbackExists($id)) {
      throw new NotFoundHttpException();
    }

    $form['id'] = [
      '#type' => 'item',
      '#value' => $id,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    if ($form_values['confirm']) {
      if ($this->feedbackService->deleteFeedback([$form_values['id']])) {
        \Drupal::messenger()->addMessage($this->t('Feedback deleted successfully.'));
      }
      else {
        \Drupal::messenger()->addError($this->t('Some error occurred while deleting feedback.'));
      }
      $url = Url::fromRoute('sitefeedback.admin_form');
      $form_state->setRedirectUrl($url);
    }
  }

}
