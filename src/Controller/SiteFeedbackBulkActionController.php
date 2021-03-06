<?php

namespace Drupal\sitefeedback\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SiteFeedbackBulkActionController.
 *
 * @package Drupal\sitefeedback\Controller
 */
class SiteFeedbackBulkActionController {

  /**
   * Feedback Service.
   *
   * @var mixed
   */
  private $feedbackService;

  /**
   * SiteFeedbackBulkActionController constructor.
   */
  public function __construct() {
    // Check if the request is not via ajax. If not via ajax, return not found.
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
      throw new NotFoundHttpException();
    }

    $this->feedbackService = \Drupal::service('sitefeedback.service');
  }

  /**
   * Bulk operation based on the selected action.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request coming to the callback.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response with message and the respective status.
   */
  public function bulkAction(Request $request) {
    if ($request->getMethod() != 'POST') {
      return new JsonResponse(['message' => 'Method Not Allowed.', 'status' => 405], 405);
    }

    $operation = $request->request->get('operation');
    if (!in_array($operation, ['mark_done', 'delete'])) {
      return new JsonResponse(['message' => 'Operation Not Allowed.', 'status' => 404], 404);
    }

    $ids = $request->request->get('ids');

    if ($operation == 'mark_done') {
      if ($this->feedbackService->updateFeedbackStatus($ids)) {
        return new JsonResponse(['message' => 'Selected feedback marked as done successfully.', 'status' => 200], 200);
      }
    }
    else {
      if ($this->feedbackService->deleteFeedback($ids)) {
        return new JsonResponse(['message' => 'Selected feedback deleted successfully.', 'status' => 200], 200);
      }
    }

    return new JsonResponse(['message' => 'Unknown operation.', 'status' => 404], 404);

  }

}
