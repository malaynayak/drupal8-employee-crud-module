<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmployeeSearchForm implements FormInterface {

  function getFormID() {
    return 'employee_search_form';
  }

  function buildForm(array $form, FormStateInterface $form_state,
    $employee = NULL) {
    $form_state->setAlwaysProcess(false);

    $form['#method'] = 'GET';
    $form['#token'] = FALSE;
    // The status messages that will contain any form errors.
    $form['search'] = [
      '#type' => 'search',
      '#default_value' => $_GET['search'] ?? ''
    ];

    $form['actions']['#prefix'] =
      '<div class="form-actions js-form-wrapper form-wrapper">';
    $form['actions']['#suffix'] = '</div>';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Search',
      '#attributes' => array('class' => ['form-actions',
        'button', 'button--primary']),
    );
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => 'Clear',
      '#attributes' => array('class' => ['button','form-actions']),
      '#url' => Url::fromRoute('employee.list'),
    );
    return $form;
  }
  function validateForm(array &$form, FormStateInterface $form_state) {}
  function submitForm(array &$form, FormStateInterface $form_state) {}
}
