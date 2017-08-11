<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\employee\EmployeeStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmployeeDeleteForm extends ConfirmFormBase {

  protected $id;

  function getFormID() {
    return 'employee_delete';
  }

  function getQuestion() {
    return t('Are you sure you want to delete employee %id?', array('%id' => $this->id));
  }

  function getConfirmText() {
    return t('Delete');
  }

  function getCancelRoute() {
    return new Url('employee.list');
  }

  function getCancelUrl() {
    return new Url('employee.list');
  }

  function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    if(!EmployeeStorage::exists($id)){
      drupal_set_message(t('Invalid employee record'), 'error');
      return new RedirectResponse(Drupal::url('employee.list'));
    }
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  function submitForm(array &$form, FormStateInterface $form_state) {
    EmployeeStorage::delete($this->id);
    drupal_set_message(t('Employee %id has been deleted.', array('%id' => $this->id)));
    $form_state->setRedirect('employee.list');
    return;
  }
}
