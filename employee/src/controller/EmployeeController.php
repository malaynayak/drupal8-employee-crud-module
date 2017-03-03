<?php

namespace Drupal\employee\controller;

use Drupal\employee\EmployeeStorage;
use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmployeeController {
 
 /**
  * Lists all the employess
  */
  public function listEmployees() {
    $content = array();
    
    // Table header
    $header = array(
      array('data' => t('Id'), 'field' => 'e.id'),
      array('data' => t('Name'), 'field' => 'e.name'),
      array('data' => t('Email'), 'field' => 'e.email'),
      'view' => '',
    );

    $db = Drupal::database();
    $query = $db->select('employee','e')
      ->fields('e')
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $query->orderByHeader($header);

    $config = Drupal::config('employee.settings');
    $limit = ($config->get('page_limit'))?$config->get('page_limit'):10;
    $query->limit($limit);
    $results = $query->execute();
    $rows = array();
    foreach($results as $row) {
      // Row with attributes on the row and some of its cells.
      $rows[] = array(
        'data' => array($row->id, $row->name, $row->email, 
          \Drupal::l('View', Url::fromRoute('employee.view',
            array('employee'=>$row->id))))
      );
    }

    $content['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array(
        'id' => 'bd-contact-table',
      ),
    );
    $content['pager'] = array(
      '#type' => 'pager',
    );
    return $content;
  }

  /**
   * To view an employee details
   */
  public function viewEmployee($employee){
    if($employee == 'invalid'){
      drupal_set_message(t('Invalid employee record'), 'error');
      return new RedirectResponse(Drupal::url('employee.list'));
    }
    
    $rows = array(
        array(
          array('data' => 'Id', 'header' => TRUE),
          $employee->id,
        ),
        array(
          array('data' => 'Name', 'header' => TRUE),
          $employee->name,
        ),
        array(
          array('data' => 'Email', 'header' => TRUE),
          $employee->email,
        ),
        array(
          array('data' => 'Department', 'header' => TRUE),
          $employee->department,
        ),
        array(
          array('data' => 'Address', 'header' => TRUE),
          $employee->address,
        ),
    );
    $content['details'] = array(
        '#type' => 'table',
        '#rows' => $rows,
        '#attributes' => array('class' => array('employee-detail'))
    );

    $content['edit'] = array(
      '#type' => 'link',
      '#title' => 'Edit',
      '#attributes' => array('class' => ['button button--primary']),
      '#url' => Url::fromRoute('employee.edit',array('employee' => $employee->id))
    );

    $content['delete'] = array(
      '#type' => 'link',
      '#title' => 'Delete',
      '#attributes' => array('class' => ['button']),
      '#url' => Url::fromRoute('employee.delete',array('id' => $employee->id)),
    );

    return $content;
  }
}