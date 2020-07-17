<?php

namespace Drupal\sitefeedback\Form;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class TestForm.
 */
class SiteFeedbackAdminForm extends FormBase {

  private $database;

  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sitefeedback_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->database = \Drupal::database();

    $markDone = '<a class="feedback-mark-done-btn" data-operation="mark_done">'
      . $this->t('Mark as Done') .
      '</a>';

    $deleteSelected = '<a class="feedback-delete-btn" data-operation="delete">'
      . $this->t('Delete Selected') .
      '</a>';

    $form['action_confirm'] = [
      '#type' => 'markup',
      '#markup' => '<div class="feedback-action-confirm">' . $this->t('Are you sure you want to perform this operation?') . '</div>',
    ];

    $form['action-links'] = [
      '#markup' => '<div class="action-wrapper"><ul class="action-links"><li>' . $markDone . '&nbsp;&nbsp;&nbsp;' . $deleteSelected . '</li></ul></div>',
    ];

    $form['search_area'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Search'),
      '#prefix' => '<div class="search-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['search_area']['search_feedback'] = [
      '#type' => 'textfield',
      '#id' => 'feedback-search',
      '#attributes' => [
        'placeholder' => $this->t('Search Feedback...'),
      ],
    ];

    $header = [
      'name' => ['data' => $this->t('Name'), 'field' => 'name'],
      'email' => ['data' => $this->t('Email'), 'field' => 'email'],
      'message' => [
        'data' => $this->t('Message'),
        'field' => 'message',
      ],
      'status' => ['data' => $this->t('Status'), 'field' => 'status'],
      'created' => [
        'data' => $this->t('Created'),
        'sort' => 'desc',
        'field' => 'created',
      ],
      'operations' => ['data' => $this->t('Actions')],
    ];

    $query = $this->database->select('sitefeedback', 'sf')
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->fields('sf');
    $query
      ->orderByHeader($header)
      ->limit(25);

    $count_query = $this->database->select('sitefeedback', 'sf');
    $count_query->addExpression('COUNT(*)');

    $userInput = $form_state->getUserInput();
    if (!empty($userInput) && isset($userInput['search_feedback']) && ($userInput['search_feedback'] != '')) {
      $search_text = $userInput['search_feedback'];
      $orCondition = new Condition('OR');
      $orCondition->condition('sf.name', '%' . $search_text . '%', 'LIKE');
      $orCondition->condition('sf.email', '%' . $search_text . '%', 'LIKE');
      $orCondition->condition('sf.message', '%' . $search_text . '%', 'LIKE');
      $query->condition($orCondition);
      $count_query->condition($orCondition);
    }

    $query->setCountQuery($count_query);

    $result = $query->execute();

    $options = [];

    foreach ($result as $row) {
      $options[$row->sfid] = [
        'name' => ['data' => html_entity_decode($row->name, ENT_QUOTES)],
        'email' => ['data' => $row->email],
        'message' => ['data' => html_entity_decode($row->message, ENT_QUOTES)],
        'status' => ['data' => ($row->status == 0) ? $this->t('In Progress') : $this->t('Done')],
        'created' => [
          'data' => \Drupal::service('date.formatter')
            ->format($row->created, 'd.M.Y'),
        ],
        'operations' => [
          'data' => [
            [
              '#prefix' => '<ul class="links inline">',
            ],
            [
              '#prefix' => '<li>',
              '#suffix' => '</li>',
              '#type' => 'link',
              '#title' => $this->t('View'),
              '#url' => Url::fromRoute('sitefeedback.view_feedback', ['id' => $row->sfid]),
              '#attributes' => ['class' => ['view']],
            ],
            [
              '#prefix' => '<li>',
              '#suffix' => '</li>',
              '#type' => 'markup',
              '#markup' => "<a href='mailto:$row->email'>" . $this->t('Reply') . "</a>",
            ],
            [
              '#prefix' => '<li>',
              '#suffix' => '</li>',
              '#type' => 'link',
              '#title' => $this->t('Delete'),
              '#url' => Url::fromRoute('sitefeedback.delete_feedback', ['id' => $row->sfid]),
              '#attributes' => ['class' => ['delete']],
            ],
            [
              '#suffix' => '</ul>',
            ],
          ],
        ],
      ];
    }

    $form['feedbacks'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No feedbacks available.'),
      '#id' => 'sitefeedback-admin-table',
    ];

    $form['#attached']['library'][] = 'sitefeedback/sitefeedbackadmin';
    $form[] = ['#type' => 'pager'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

}
