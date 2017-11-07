<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\employee\EmployeeStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Employee bulck action form.
 */
class EmployeeBulkActionForm extends ConfirmFormBase {

  /**
   * The action name.
   *
   * @var string
   */

  private $action;

  /**
   * The request.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */

  protected static $session;

  /**
   * The records on which the action to be performed.
   *
   * @var mixed
   */

  private $records;

  /**
   * Constructs the EmployeeController.
   *
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The session service.
   */
  public function __construct(Session $session) {
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('session')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_bulk_action';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to %action selected employees?',
      ['%action' => $this->action]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getPageTitle() {
    return t('Are you sure you want to %action selected employees?',
      ['%action' => $this->action]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Confirm');
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
  public function buildForm(array $form, FormStateInterface $form_state, $action = NULL) {
    $this->action = $action;
    $session_employee = $this->session->get('employee');
    if ($this->records = $session_employee['selected_items']) {
      $form['employees'] = [
        '#theme' => 'item_list',
        '#items' => $this->records,
      ];
    }
    else {
      drupal_set_message(t('No employee record to process.'), 'error');
      return new RedirectResponse(Drupal::url('employee.list'));
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $request = \Drupal::request();

    $batch = [
      'title' => t('Applying action @action to selected employees', ['@action' => $this->action]),
      'operations' => [
        [
          'Drupal\employee\forms\EmployeeBulkActionForm::performBatchAction',
          [$this->records, $this->action],
        ],
      ],
      'finished' => 'Drupal\employee\forms\EmployeeBulkActionForm::onFinishBatchCallback',
    ];
    batch_set($batch);
    $this->session->remove('employee');
    $form_state->setRedirect('employee.list');
  }

  /**
   * Batch operation callback.
   */
  public static function performBatchAction($records, $action, &$context) {
    switch ($action) {
      case 'delete':
        $message = "Deleting the employees";
        break;

      case 'activate':
        $message = "Activating the employees";
        break;

      case 'block':
        $message = "Blocking the employees";
        break;

      default:
        $message = "Deleting the employees";
    }

    foreach ($records as $id => $name) {
      switch ($action) {
        case 'delete':
          $result = EmployeeStorage::delete($id);
          break;

        case 'activate':
          $result = EmployeeStorage::changeStatus($id, 1);
          break;

        case 'block':
          $result = EmployeeStorage::changeStatus($id, 0);
          break;

        default:
          $result = EmployeeStorage::delete($id);
      }
      $results[] = $result;
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  /**
   * Finish callback for batch process.
   */
  public static function onFinishBatchCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One employee record processed.', '@count employee records processed.'
      );
      drupal_set_message($message);
    }
    else {
      $message = t('Finished with an error.');
      drupal_set_message($message, 'error');
    }
  }

}
