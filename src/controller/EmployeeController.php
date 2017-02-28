<?php

namespace Drupal\employee\controller;

use Drupal\employee\EmployeeStorage;
use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EmployeeController {

  public function listEmployees() {
    $content = array();
    
    // Table header
    $header = array(
      'id' => t('Id'),
      'name' => t('Name'),
      'email' => t('Email'),
      'view' => '',
    );
    
    $rows = array();
    foreach(EmployeeStorage::getAll() as $row) {
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
    
    return $content;
  }

  public function viewEmployee($employee){
    if($employee == 'invalid'){
      drupal_set_message(t('Invalid employee record'), 'error');
      return new RedirectResponse(Drupal::url('employee.list'));
    }
    $departments = array(
        'dev' => 'Development',
        'hr' => 'Human Resource',
        'sale' => 'Sales',
        'marketing' => 'Marketing'
    );
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
          $departments[$employee->department],
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

    $content['cancel'] = array(
      '#type' => 'link',
      '#title' => 'Back',
      '#attributes' => array('class' => ['button']),
      '#url' => Url::fromRoute('employee.list'),
    );

    return $content;
  }
}