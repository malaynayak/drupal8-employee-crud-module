<?php

namespace Drupal\employee\events;

use Symfony\Component\EventDispatcher\Event;
use Drupal\employee\EmployeeStorage;

class EmployeeWelcomeEvent extends Event {

  /**
   * The Employee Id
   **/
  private $employee_id;

  /**
   * Constructs the EmployeeWelcomeEvent.
   *
   * @param int $employee_id
   *   The Employee Id
   **/
  public function __construct($employee_id){
    $this->employee_id = $employee_id;
  }

  /**
   * Loads employee details
   * @return mixed the employee details
   **/
  public function getEmployeeInfo(){
    return EmployeeStorage::load($this->employee_id);
  }
}
