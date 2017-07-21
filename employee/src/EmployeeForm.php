<?php

namespace Drupal\employee;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\employee\EmployeeStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmployeeForm implements FormInterface {

  function getFormID() {
    return 'employee_add';
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

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => true,
      '#default_value' => ($employee)?$employee->name:''
    );

    $form['email'] = array(
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => true,
      '#default_value' => ($employee)?$employee->email:''
    );
    
    $form['department'] = array(
      '#type' => 'select',
      '#title' => t('Department'),
      '#options' => array(
        '' => 'Select Department',
        'Development' => 'Development',
        'HR' => 'HR',
        'Sales' => 'Sales',
        'Marketing' => 'Marketing'
      ),
      '#required' => true,
      '#default_value' => ($employee)?$employee->department:''
    );

    $form['address'] = array(
      '#type' => 'textarea',
      '#title' => t('Address'),
      '#required' => true,
      '#default_value' => ($employee)?$employee->address:''
    );
    
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => (isset($employee->id))?'Save':'Add'
    );
    
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => 'Cancel',
      '#attributes' => array('class' => ['button']),
      '#url' => Url::fromRoute('employee.list'),
    );

    return $form;
  }

  function validateForm(array &$form, FormStateInterface $form_state) {
      $email = $form_state->getValue('email');
      if (!empty($email) && (filter_var($email, 
        FILTER_VALIDATE_EMAIL) === false)) {
        $form_state->setErrorByName('email', t('Invalid email'));
      }
      $id = $form_state->getValue('eid');
      if(!empty($id)){
        if (!EmployeeStorage::checkUniqueEmail($email,$id)) {
          $form_state->setErrorByName('email', t('This email has already been taken!'));
        }
      } else {
        if (!EmployeeStorage::checkUniqueEmail($email)) {
          $form_state->setErrorByName('email', t('The email has already been taken!'));
        }
      }
  }

  function submitForm(array &$form, FormStateInterface $form_state) {
    $fields = array(
      'name' => SafeMarkup::checkPlain($form_state->getValue('name')),
      'email' => SafeMarkup::checkPlain($form_state->getValue('email')),
      'department' => $form_state->getValue('department'),
      'address' => SafeMarkup::checkPlain($form_state->getValue('address'))
    );

    $id = $form_state->getValue('eid');
    if(!empty($id) && EmployeeStorage::exists($id)){
      EmployeeStorage::update($id,$fields);
      $message = 'Employee updated sucessfully';
    } else {
      EmployeeStorage::add($fields);
      $message = 'Employee created sucessfully';
    }

    drupal_set_message(t($message));
    $form_state->setRedirect('employee.list');
    return;
  }

}