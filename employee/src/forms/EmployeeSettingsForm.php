<?php

namespace Drupal\employee\forms;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\core\Form\FormStateInterface;

define("MAX_PAGE_LENGTH", 25);

/**
 * Configuration settings form.
 */
class EmployeeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'employee.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('employee.settings');
    $form['page_limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Page Limit'),
      '#default_value' => $config->get('page_limit'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $limit = $form_state->getValue('page_limit');
    if (!empty($limit)) {
      if (filter_var($limit, FILTER_VALIDATE_INT) === FALSE) {
        $form_state->setErrorByName('page_limit', t('Page limit must be an integer'));
      }
      if ($limit > MAX_PAGE_LENGTH) {
        $form_state->setErrorByName('page_limit', t('Page limit must be less than @page_length',
          ['@page_length' => MAX_PAGE_LENGTH]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config('employee.settings')
      // Set the submitted configuration setting.
      ->set('page_limit', $form_state->getValue('page_limit'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
