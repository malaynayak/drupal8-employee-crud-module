<?php

namespace Drupal\employee\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\employee\EmployeeStorage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

define("MAX_LIMIT", 7);
define("DEFAULT_LIMIT", 5);

/**
 * Provides a 'Employee' Block.
 *
 * @Block(
 *   id = "employees_block",
 *   admin_label = @Translation("Employee Block"),
 *   category = @Translation("Employee")
 * )
 */
class EmployeeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = [];

    $config = $this->getConfiguration();
    $limit = isset($config['limit']) ? $config['limit'] : DEFAULT_LIMIT;
    $content['table'] = [
      '#lazy_builder' => [static::class . '::lazyBuildEmployeeTable', [$limit]],
      '#create_placeholder' => TRUE,
    ];

    $content['more'] = [
      '#type' => 'link',
      '#title' => t('More'),
      '#url' => new Url('employee.list'),
      '#attributes' => ['class' => 'button'],
    ];
    return $content;
  }

  /**
   * Lazy builder.
   */
  public static function lazyBuildEmployeeTable($limit) {
    // Table header.
    $header = [
      'name' => t('Employee Id'),
      'message' => t('Employee Name'),
    ];
    $rows = [];
    foreach (EmployeeStorage::getAll($limit, 'id', 'DESC') as $id => $row) {
      $rows[] = [
        'data' => [$row->id, $row->name],
      ];
    }
    return [
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#attributes' => [
          'id' => 'bd-contact-block-table',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['limit'] = [
      '#type' => 'textfield',
      '#title' => t('Limit'),
      '#description' => t('Number of employees to show'),
      '#default_value' => isset($config['limit']) ?
      $config['limit'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('limit', $form_state->getValue('limit'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $limit = $form_state->getValue('limit');

    if (!is_numeric($limit)) {
      $form_state->setErrorByName('limit',
        t('Needs to be an integer'));
    }
    if ($limit > MAX_LIMIT) {
      $form_state->setErrorByName('limit',
        t('Must not exceed @max_limit', ['max_limit' => MAX_LIMIT]));
    }
  }

}
