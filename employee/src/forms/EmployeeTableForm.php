<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;
use Drupal\employee\EmployeeStorage;

/**
 * Employee list in tableselect format.
 */
class EmployeeTableForm implements FormInterface {
  /**
   * Databse Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */

  protected $db;

  /**
   * Search String.
   *
   * @var string
   */

  private $searchKey;

  /**
   * Constructs the EmployeeTableForm.
   *
   * @param \Drupal\Core\Database\Connection $con
   *   The database connection.
   * @param string $search_key
   *   The search string.
   */
  public function __construct(Connection $con, $search_key = '') {
    $this->db = $con;
    $this->searchKey = $search_key;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('database')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_table_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    // Table header.
    $header = [
      ['data' => t('ID'), 'field' => 'e.id'],
      'picture' => '',
      ['data' => t('Name'), 'field' => 'e.name'],
      ['data' => t('Email'), 'field' => 'e.email'],
      ['data' => t('Country'), 'field' => 'e.country'],
      ['data' => t('State'), 'field' => 'e.state'],
      ['data' => t('Status')],
      'actions' => 'Operations',
    ];
    $query = $this->db->select('employee', 'e')
      ->fields('e')
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->orderByHeader($header);
    $config = Drupal::config('employee.settings');
    $limit = ($config->get('page_limit')) ? $config->get('page_limit') : 10;
    $query->limit($limit);

    $search_key = $this->searchKey;
    if (!empty($this->searchKey)) {
      $query->condition('e.name', "%" .
        Html::escape($search_key) . "%", 'LIKE');
    }
    $results = $query->execute();
    $rows = [];
    foreach ($results as $row) {
      $ajax_link_attributes = [
        'attributes' => [
          'class' => 'use-ajax',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => ['width' => 700, 'height' => 400],
        ],
      ];
      $view_url = Url::fromRoute('employee.view',
        ['employee' => $row->id, 'js' => 'nojs']);
      $ajax_view_url = Url::fromRoute('employee.view',
        ['employee' => $row->id, 'js' => 'ajax'], $ajax_link_attributes);
      $ajax_view_link = Drupal::l($row->name, $ajax_view_url);
      $view_link = Drupal::l('View', Url::fromRoute('employee.view',
        ['employee' => $row->id, 'js' => 'nojs']));
      $mail_url = Url::fromRoute('employee.sendmail', ['employee' => $row->id],
        $ajax_link_attributes);
      $drop_button = [
        '#type' => 'dropbutton',
        '#links' => [
          'view' => [
            'title' => t('View'),
            'url' => $view_url,
          ],
          'edit' => [
            'title' => t('Edit'),
            'url' => Url::fromRoute('employee.edit', ['employee' => $row->id]),
          ],
          'delete' => [
            'title' => t('Delete'),
            'url' => Url::fromRoute('employee.delete', ['id' => $row->id]),
          ],
          'quick_edit' => [
            'title' => t('Quick Edit'),
            'url' => Url::fromRoute('employee.quickedit', ['employee' => $row->id],
              $ajax_link_attributes),
          ],
          'mail' => [
            'title' => t('Mail'),
            'url' => $mail_url,
          ],
        ],
      ];
      $profile_pic = File::load($row->profile_pic);
      if ($profile_pic) {
        $style = Drupal::entityTypeManager()->getStorage('image_style')->load('tiny_thumbnail');
        $profile_pic_url = $style->buildUrl($profile_pic->getFileUri());
      }
      else {
        $module_handler = Drupal::service('module_handler');
        $path = $module_handler->getModule('employee')->getPath();
        $profile_pic_url = $base_url . '/' . $path . '/assets/profile_placeholder_thumb.png';
      }
      $rows[$row->id] = [
        [sprintf("%04s", $row->id)],
        'picture' => [
          'data' => [
            '#type' => 'html_tag',
            '#tag' => 'img',
            '#attributes' => ['src' => $profile_pic_url],
          ],
        ],
        [$ajax_view_link],
        [$row->email],
        [$row->country],
        [$row->state],
        [($row->status) ? 'Active' : 'Blocked'],
        'actions' => [
          'data' => $drop_button,
        ],
      ];
    }

    $form['action'] = [
      '#type' => 'select',
      '#title' => t('Action'),
      '#options' => [
        'delete' => 'Delete Selected',
        'activate' => 'Activate Selected',
        'block' => 'Block Selected',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Apply to selected items',
      '#prefix' => '<div class="form-actions js-form-wrapper form-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $rows,
      '#attributes' => [
        'id' => 'employee-contact-table',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_ids = array_filter($form_state->getValue('table'));
    $selected_ids = array_map(function ($val) {
      $record = EmployeeStorage::load($val);
      return $record->name;
    }, $selected_ids);
    if (!array_filter($selected_ids)) {
      drupal_set_message(t('No employee record to selected'), 'error');
      $form_state->setRedirect('employee.list');
      return;
    }
    else {
      $request = Drupal::request();
      $session = $request->getSession();
      $session->set('employee', [
        'selected_items' => $selected_ids,
      ]);
      $form_state->setRedirect('employee.action', ['action' => $form_state->getValue('action')]);
      return;
    }
  }

}
