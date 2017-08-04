<?php

namespace Drupal\employee;
use Drupal\Core\Database\Query;

class EmployeeStorage {

  /**
   * To get multiple employee records
   * @param integer $limit is the number of records to be fetched
   * @param string $orderBy is the field on which the sorting to be performed
   * @param string $order is the sorting order. Default is 'DESC'
   */
  static function getAll($limit=NULL,$orderBy=NULL,$order='DESC') {
    $query = db_select('employee', 'e')
      ->fields('e');
    if($limit){
      $query->range(0,$limit);
    }
    if($orderBy){
     $query->orderBy($orderBy, $order);
    }
    $result = $query->execute()
      ->fetchAll();
	  return $result;
  }

  /**
   * To check if an employee is valid
   * @param integer $id is the employee ID
   */
  static function exists($id) {
    $result = db_select('employee', 'e')
      ->fields('e',array('id'))
      ->condition('id', $id,'=')
      ->execute()
      ->fetchField();
    return (bool) $result;
  }

  /**
   * To load an employee record
   * @param integer $id is the employee ID
   */
  static function load($id) {
    $result = db_select('employee', 'e')
      ->fields('e')
      ->condition('id', $id,'=')
      ->execute()
      ->fetchObject();
    return $result;
  }

  /**
   * check for duplicate email
   * @param String $email is the email id
   * @param integer $id is the employee id
   */
  static function checkUniqueEmail($email, $id = NULL) {
    $query = db_select('employee', 'e')
      ->fields('e',['id']);
    if($id){
      $query->condition('id', $id,'!=');
    }
    $query->condition('email', $email,'=');
    $result = $query->execute();
    if(empty($result->fetchObject())){
      return true;
    } else {
      return false;
    }
  }

  /**
   * To insert a new employee record
   * @param array $fields is an array conating the employee data
   * in key => value pair.
   */
  static function add(array $fields) {
    return db_insert('employee')->fields($fields)->execute();
  }

  /**
   * To update an existing employee record
   * @param integer $id is the employee ID
   * @param array $fields is an array conating the employee data
   * in key => value pair.
   */
  static function update($id, $fields) {
    db_update('employee')->fields($fields)
    ->condition('id', $id)
    ->execute();
  }

  /**
   * To delete a specific employee record
   * @param integer $id is the employee ID
   */
  static function delete($id) {
    db_delete('employee')->condition('id', $id)->execute();
  }

}
