<?php

namespace Drupal\employee;

/**
 * DAO class for employee table.
 */
class EmployeeStorage {

  /**
   * To get multiple employee records.
   *
   * @param int $limit
   *   The number of records to be fetched.
   * @param string $orderBy
   *   The field on which the sorting to be performed.
   * @param string $order
   *   The sorting order. Default is 'DESC'.
   */
  public static function getAll($limit = NULL, $orderBy = NULL, $order = 'DESC') {
    $query = \Drupal::database()->select('employee', 'e')
      ->fields('e');
    if ($limit) {
      $query->range(0, $limit);
    }
    if ($orderBy) {
      $query->orderBy($orderBy, $order);
    }
    $result = $query->execute()
      ->fetchAll();
    return $result;
  }

  /**
   * To check if an employee is valid.
   *
   * @param int $id
   *   The employee ID.
   */
  public static function exists($id) {
    $result = \Drupal::database()->select('employee', 'e')
      ->fields('e', ['id'])
      ->condition('id', $id, '=')
      ->execute()
      ->fetchField();
    return (bool) $result;
  }

  /**
   * To load an employee record.
   *
   * @param int $id
   *   The employee ID.
   */
  public static function load($id) {
    $result = \Drupal::database()->select('employee', 'e')
      ->fields('e')
      ->condition('id', $id, '=')
      ->execute()
      ->fetchObject();
    return $result;
  }

  /**
   * Check for duplicate email.
   *
   * @param string $email
   *   The email id.
   * @param int $id
   *   The employee id.
   */
  public static function checkUniqueEmail($email, $id = NULL) {
    $query = \Drupal::database()->select('employee', 'e')
      ->fields('e', ['id']);
    if ($id) {
      $query->condition('id', $id, '!=');
    }
    $query->condition('email', $email, '=');
    $result = $query->execute();
    if (empty($result->fetchObject())) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * To insert a new employee record.
   *
   * @param array $fields
   *   An array conating the employee data in key value pair.
   */
  public static function add(array $fields) {
    return \Drupal::database()->insert('employee')->fields($fields)->execute();
  }

  /**
   * To update an existing employee record.
   *
   * @param int $id
   *   The employee ID.
   * @param array $fields
   *   An array conating the employee data in key value pair.
   */
  public static function update($id, array $fields) {
    return \Drupal::database()->update('employee')->fields($fields)
      ->condition('id', $id)
      ->execute();
  }

  /**
   * To delete a specific employee record.
   *
   * @param int $id
   *   The employee ID.
   */
  public static function delete($id) {
    $record = self::load($id);
    if ($record->profile_pic) {
      file_delete($record->profile_pic);
    }
    return \Drupal::database()->delete('employee')->condition('id', $id)->execute();
  }

  /**
   * To activate/ block the employee record.
   *
   * @param int $id
   *   The employee ID.
   * @param int $status
   *   Set 1 for activatng and 0 for blocking.
   */
  public static function changeStatus($id, $status) {
    return self::update($id, ['status' => ($status) ? 1 : 0]);
  }

}
