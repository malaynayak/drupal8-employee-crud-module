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

class EmployeeMailForm implements FormInterface {

  function getFormID() {
    return 'employee_mail_form';
  }

  function buildForm(array $form, FormStateInterface $form_state,
    $employee = NULL) {
    if($employee){
      if($employee == 'invalid'){
        drupal_set_message(t('Invalid employee record'), 'error');
        return new RedirectResponse(Drupal::url('employee.list'));
      }
      $form['eid'] = array(
        '#type' => 'hidden',
        '#value' => $employee->id
      );
    }

    $form['#prefix'] = '<div id="employee_mail_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['subject'] = array(
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#required' => true,
    );

    $form['message'] = array(
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => true,
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Send',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
          'callback' => [$this, 'sendMailAjax'],
          'event' => 'click',
      ],
    );

    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => 'Cancel',
      '#attributes' => array('class' => ['button']),
      '#url' => Url::fromRoute('employee.list'),
    );

    return $form;
  }

  function validateForm(array &$form, FormStateInterface $form_state) {}

  function submitForm(array &$form, FormStateInterface $form_state) {}

  function sendMailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#employee_mail_form', $form));
    } else {
      $id = $form_state->getValue('eid');
      if(!empty($id) && EmployeeStorage::exists($id)){
        $employee = EmployeeStorage::load($id);
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'employee';
        $key = 'send_employee_mail';
        $to = $employee->email;
        $params['subject'] = $form_state->getValue('subject');
        $params['message'] = $form_state->getValue('message');
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        $message = 'Email sent sucessfully';
        drupal_set_message(t($message));
        $form_state->setRedirect('employee.list');
        $response->addCommand(new RedirectCommand(Url::fromRoute('employee.list')->toString()));
    }
    }
    return $response;
  }
}
