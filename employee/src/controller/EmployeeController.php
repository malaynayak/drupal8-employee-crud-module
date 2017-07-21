<?php

namespace Drupal\employee\controller;

use Drupal\employee\EmployeeStorage;
use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;

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
      'edit' => '',
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
      $ajax_link_attributes = array(
        'attributes' => array(
          'class' => 'use-ajax',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => ['width' => 700, 'height' => 400]
        )
      );
      $ajax_url = Url::fromRoute('employee.view', array('employee'=>$row->id, 'js' => 'ajax'), 
        $ajax_link_attributes);

      $view_link = \Drupal::l('View', Url::fromRoute('employee.view', 
            array('employee'=>$row->id, 'js' => 'nojs')));
      
      $quick_edit_url = Url::fromRoute('employee.quickedit', array('employee'=>$row->id), 
        $ajax_link_attributes);
      $quick_edit_link = \Drupal::l('Quick Edit', $quick_edit_url);

      $rows[] = array(
        'data' => array(
          $row->id, 
          \Drupal::l($row->name, $ajax_url), 
          $row->email, 
          $view_link,
          $quick_edit_link
        )
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

    $content['#attached'] = array('library' => ['core/drupal.dialog.ajax']);
    return $content;
  }

  /**
   * To view an employee details
   */
  public function viewEmployee($employee, $js='nojs'){
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

    if ($js == 'ajax') {
      $modal_title = t('Employee #@id',array('@id' => $employee->id));
      $options = [
        'dialogClass' => 'popup-dialog-class',
        'width' => '70%',
        'height' => '80%'
      ];
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand($modal_title, $content, $options));
      return $response;
    } else {
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

  /**
   * Callback for opening the employee quick edit form in modal.
   */
  public function openQuickEditModalForm($employee = NULL) {
    if($employee == 'invalid'){
      drupal_set_message(t('Invalid employee record'), 'error');
      return new RedirectResponse(Drupal::url('employee.list'));
    }
    $response = new AjaxResponse();
    // Get the modal form using the form builder.
    $modal_form = \Drupal::formBuilder()->getForm('Drupal\employee\EmployeeQuickEditForm', $employee);

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand(t('Quick Edit Employee #@id',array('@id' => $employee->id)), $modal_form, ['width' => '800']));
    return $response;
  }
}