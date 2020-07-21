<?php

namespace Drupal\sitefeedback\Service;

/**
 * Class SiteFeedbackService.
 */
class SiteFeedbackService {

  /**
   * The database connection holder.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * SiteFeedbackService constructor.
   */
  public function __construct() {
    $this->database = \Drupal::database();
  }

  /**
   * Save a feedback.
   *
   * @param array $feedback
   *   Feedback array.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   Feedback save status.
   *
   * @throws \Exception
   */
  public function saveFeedback(array $feedback) {
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
   * Check if a feedback exists or not.
   *
   * @param int $id
   *   Feedback id.
   *
   * @return bool
   *   True or False.
   */
  public function feedbackExists($id) {
    $query = $this->database->select('sitefeedback', 'sf');
    $query->fields('sf', ['sfid']);
    $query->condition('sf.sfid', $id);
    $result = $query->execute()->fetchCol();

    return count($result) > 0;
  }

  /**
   * Load a feedback by id.
   *
   * @param int $id
   *   Feedback id.
   *
   * @return mixed
   *   The loaded feedback or empty array.
   */
  public function loadFeedback($id) {
    $query = $this->database->select('sitefeedback', 'sf');
    $query->fields('sf');
    $query->condition('sf.sfid', $id);
    $result = $query->execute()->fetchObject();

    return $result;
  }

  /**
   * Delete feedback by ids.
   *
   * @param array $ids
   *   Feedback ids.
   *
   * @return int
   *   The delete status.
   */
  public function deleteFeedback(array $ids = []) {
    return $this->database->delete('sitefeedback')
      ->condition('sfid', $ids, 'IN')
      ->execute();
  }

  /**
   * Update status of feedback as Done.
   *
   * @param array $ids
   *   Feedback ids.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|string|null
   *   The update status.
   */
  public function updateFeedbackStatus(array $ids = []) {
    return $this->database->update('sitefeedback')
      ->fields([
        'status' => 1,
      ])
      ->condition('sfid', $ids, 'IN')
      ->execute();
  }

}
