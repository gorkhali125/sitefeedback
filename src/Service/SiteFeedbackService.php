<?php

namespace Drupal\sitefeedback\Service;

/**
 * Class SiteFeedbackService.
 */
class SiteFeedbackService {

  private $database;

  /**
   * SiteFeedbackService constructor.
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * @param $feedback
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   * @throws \Exception
   * Save a feedback
   */
  public function saveFeedback($feedback){
    $is_new = !isset($feedback['sfid']);

    $feedback['uid'] = \Drupal::currentUser()->id();
    $feedback['changed'] = \Drupal::time()->getRequestTime();

    if ($is_new) {
      $feedback['sfid'] = 0;
      $feedback['created'] = \Drupal::time()->getRequestTime();
    }

    $query = \Drupal::database()->merge('sitefeedback');
    $query->key(['sfid' => $feedback['sfid']]);
    $query->fields($feedback);

    return $query->execute();
  }

  /**
   * @param $id
   *
   * @return bool
   * Check if a feedback exists or not by id
   */
  public function feedbackExists($id){
    $query = $this->database->select('sitefeedback', 'sf');
    $query->fields('sf', ['sfid']);
    $query->condition('sf.sfid', $id);
    $result = $query->execute()->fetchCol();

    return count($result) > 0;
  }

  /**
   * @param $id
   *
   * @return mixed
   * Load a feedback by id
   */
  public function loadFeedback($id){
    $query = $this->database->select('sitefeedback', 'sf');
    $query->fields('sf');
    $query->condition('sf.sfid', $id);
    $result = $query->execute()->fetchObject();

    return $result;
  }

  /**
   * @param $ids
   *
   * @return int
   * Delete feedback by ids
   */
  public function deleteFeedback($ids = []){
    return $this->database->delete('sitefeedback')
      ->condition('sfid', $ids, 'IN')
      ->execute();
  }

  /**
   * @param array $ids
   *
   * @return \Drupal\Core\Database\StatementInterface|int|string|null
   * Update status of feedback as Done
   */
  public function updateFeedbackStatus($ids = []){
    return $this->database->update('sitefeedback')
      ->fields([
        'status' => 1
      ])
      ->condition('sfid', $ids, 'IN')
      ->execute();
  }

}
