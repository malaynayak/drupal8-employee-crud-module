<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\employee\EmployeeStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Employee quick edit form.
 */
class EmployeeQuickEditForm implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_quick_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form,
  FormStateInterface $form_state,
    $employee = NULL) {
    if ($employee) {
      if ($employee == 'invalid') {
        drupal_set_message(t('Invalid employee record'), 'error');
        return new RedirectResponse(Drupal::url('employee.list'));
      }
      $form['eid'] = [
        '#type' => 'hidden',
        '#value' => $employee->id,
      ];
    }

    $form['#prefix'] = '<div id="quick_edit_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => TRUE,
      '#default_value' => $employee->name,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
      '#default_value' => $employee->email,
    ];

    $form['department'] = [
      '#type' => 'select',
      '#title' => t('Department'),
      '#options' => [
        '' => 'Select Department',
        'Development' => 'Development',
        'HR' => 'HR',
        'Sales' => 'Sales',
        'Marketing' => 'Marketing',
      ],
      '#required' => TRUE,
      '#default_value' => ($employee) ? $employee->department : '',
    ];

    $form['general']['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Active?'),
      '#default_value' => ($employee) ? $employee->status : 1,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => 'Cancel',
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute('employee.list'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!empty($email) && (filter_var($email,
        FILTER_VALIDATE_EMAIL) === FALSE)) {
      $form_state->setErrorByName('email', t('Invalid email'));
    }
    if (!empty($email) && !EmployeeStorage::checkUniqueEmail($email, $form_state->getValue('eid'))) {
      $form_state->setErrorByName('email', t('The email has already been taken!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitModalFormAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#quick_edit_form', $form));
    }
    else {
      $fields = [
        'name' => SafeMarkup::checkPlain($form_state->getValue('name')),
        'email' => SafeMarkup::checkPlain($form_state->getValue('email')),
        'department' => $form_state->getValue('department'),
        'status' => $form_state->getValue('status'),
      ];

      $id = $form_state->getValue('eid');
      if (!empty($id) && EmployeeStorage::exists($id)) {
        EmployeeStorage::update($id, $fields);
        drupal_set_message(t('Employee updated sucessfully'));
      }

      $form_state->setRedirect('employee.list');
      $response->addCommand(new RedirectCommand(Url::fromRoute('employee.list')->toString()));
    }
    return $response;
  }

}
