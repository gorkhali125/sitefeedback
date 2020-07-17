<?php

namespace Drupal\sitefeedback\Controller;


use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SiteFeedbackViewController {

  private $feedbackService;

  public function __construct() {
    $this->feedbackService = \Drupal::service('sitefeedback.service');
  }


  public function viewFeedback($id) {
    if (!$this->feedbackService->feedbackExists($id)) {
      throw new NotFoundHttpException();
    }

    $feedback = $this->feedbackService->loadFeedback($id);
    $feedback->username = ($feedback->uid == 0) ? t('Anonymous') : User::load($feedback->uid)->label();
    $feedback->name = html_entity_decode($feedback->name, ENT_QUOTES);
    $feedback->message = html_entity_decode($feedback->message, ENT_QUOTES);

    return [
      '#theme' => 'feedback_view',
      '#feedback' => $feedback,
    ];
  }

}
