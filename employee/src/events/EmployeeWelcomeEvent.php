<?php

namespace Drupal\employee\events;

use Symfony\Component\EventDispatcher\Event;
use Drupal\employee\EmployeeStorage;

/**
 * Employee welcome event.
 */
class EmployeeWelcomeEvent extends Event {

  /**
   * The Employee Id.
   *
   * @var int
   */
  private $employeeId;

  /**
   * Constructs the EmployeeWelcomeEvent.
   *
   * @param int $employee_id
   *   The Employee Id.
   */
  public function __construct($employee_id) {
    $this->employeeId = $employee_id;
  }

  /**
   * Loads employee details.
   *
   * @return mixed
   *   The employee details.
   */
  public function getEmployeeInfo() {
    return EmployeeStorage::load($this->employeeId);
  }

}
