<?php

namespace Drupal\employee; 
use Drupal\Core\Database\Query;

class EmployeeStorage {

  static function getAll($limit=NULL) {
    $query = db_select('employee', 'e')
      ->fields('e');
    if($limit){
      $query->range(0,$limit);
    }
    $result = $query->execute()
      ->fetchAll();
	  return $result;
  }

  static function exists($id) {
    $result = db_select('employee', 'e')
      ->fields('e',array('id'))
      ->condition('id', $id,'=')
      ->execute()
      ->fetchField();
    return (bool) $result;
  }

  static function load($id) {
    $result = db_select('employee', 'b')
      ->fields('b')
      ->condition('id', $id,'=')
      ->execute()
      ->fetchObject();
    return $result;
  }

  static function add(array $fields) {
    db_insert('employee')->fields($fields)->execute();
  }

  static function update($id, $fields) {
    db_update('employee')->fields($fields)
    ->condition('id', $id)
    ->execute();
  }

  static function delete($id) {
    db_delete('employee')->condition('id', $id)->execute();
  }

}