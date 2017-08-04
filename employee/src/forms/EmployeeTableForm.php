<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\Html;

class EmployeeTableForm implements FormInterface {
  /*
   * Databse Connection
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /*
   * Search String
   */
  private $search_key;

  /**
   * Constructs the EmployeeTableForm.
   *
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The Form builder.
   */
  public function __construct(Connection $con, $search_key){
      $this->db = $con;
      $this->search_key = $search_key;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container){
      return new static(
        $container->get('database')
      );
  }

  /**
   * {@inheritdoc}
   */
  function getFormID() {
    return 'employee_table_form';
  }

  /**
   * {@inheritdoc}
   */
  function buildForm(array $form, FormStateInterface $form_state) {
    // Table header
    $header = [
      ['data' => t('ID'), 'field' => 'e.id'],
      ['data' => t('Name'), 'field' => 'e.name'],
      ['data' => t('Email'), 'field' => 'e.email'],
      ['data' => t('Country'), 'field' => 'e.country'],
      ['data' => t('State'), 'field' => 'e.state'],
      'actions' => 'Operations',
    ];
    $query = $this->db->select('employee','e')
      ->fields('e')
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->orderByHeader($header);
    $config = Drupal::config('employee.settings');
    $limit = ($config->get('page_limit'))?$config->get('page_limit'):10;
    $query->limit($limit);

    $search_key = $this->search_key;
    if(!empty($this->search_key)){
      $query->condition('e.name', "%" .
        Html::escape($search_key) . "%", 'LIKE');
    }
    $results = $query->execute();
    $rows = [];
    foreach($results as $row) {
      $ajax_link_attributes = [
        'attributes' => [
          'class' => 'use-ajax',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => ['width' => 700, 'height' => 400]
        ]
      ];
      $view_url = Url::fromRoute('employee.view',
        ['employee'=>$row->id, 'js' => 'nojs']);
      $ajax_view_url = Url::fromRoute('employee.view',
        ['employee'=>$row->id, 'js' => 'ajax'],$ajax_link_attributes);
      $ajax_view_link = Drupal::l($row->name, $ajax_view_url);
      $view_link = Drupal::l('View', Url::fromRoute('employee.view',
        ['employee'=>$row->id, 'js' => 'nojs']));
      $quick_edit_url = Url::fromRoute('employee.quickedit',
        ['employee'=>$row->id],$ajax_link_attributes);
      $mail_url = Url::fromRoute('employee.sendmail', ['employee'=>$row->id],
        $ajax_link_attributes);

      $drop_button = array(
        '#type' => 'dropbutton',
        '#links' => [
          'view' => [
            'title' => t('View'),
            'url' => $view_url
          ],
          'quick_edit' => [
            'title' => t('Quick Edit'),
            'url' => $quick_edit_url,
          ],
          'mail' => [
            'title' => t('Mail'),
            'url' => $mail_url,
          ],
        ],
      );
      $rows[] = [
          $row->id,
          $ajax_view_link,
          $row->email,
          $row->country,
          $row->state,
          'actions' => [
            'data'=>$drop_button
          ]
      ];
    }
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
  function validateForm(array &$form, FormStateInterface $form_state) {}

  function submitForm(array &$form, FormStateInterface $form_state) {}
}
