<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\employee\EmployeeStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Employee delete form.
 */
class EmployeeDeleteForm extends ConfirmFormBase {

  protected $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete employee %id?', ['%id' => $this->id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return new Url('employee.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('employee.list');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    if (!EmployeeStorage::exists($id)) {
      drupal_set_message(t('Invalid employee record'), 'error');
      return new RedirectResponse(Drupal::url('employee.list'));
    }
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    EmployeeStorage::delete($this->id);
    drupal_set_message(t('Employee %id has been deleted.', ['%id' => $this->id]));
    $form_state->setRedirect('employee.list');
  }

}
