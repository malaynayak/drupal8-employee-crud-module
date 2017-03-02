<?php
/**
 * @file
 * Contains Drupal\employee\plugin\block\EmployeeBlock.
 */

namespace Drupal\employee\Plugin\Block;
 
use Drupal\Core\Block\BlockBase;
use Drupal\employee\EmployeeStorage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

define("MAX_LIMIT", 7);
define("DEFAULT_LIMIT", 5);
/**
 * Provides a 'Employee' Block
 *
 * @Block(
 *   id = "employees_block",
 *   admin_label = @Translation("Employee Block"),
 *   category = @Translation("Employee")
 * )
 */
class EmployeeBlock extends BlockBase{
  /**
   * {@inheritdoc}
   */
  public function build() {
  	// Table header
  	$header = array(
  	  'name' => t('Employee Id'),
  	  'message' => t('Employee Name'),
  	);
  	$rows = array();
    
    $config = $this->getConfiguration();
    $limit = isset($config['limit']) ? $config['limit'] : DEFAULT_LIMIT;

    foreach(EmployeeStorage::getAll($limit,'id', 'DESC') as $id=>$row) {
      $rows[] = array(
        'data' => array($row->id, $row->name)
      );
    }

    $content = array();
    $content['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => array(
        'id' => 'bd-contact-block-table',
      ),
    );

    $content['more'] = array(
      '#type' => 'link',
      '#title' => t('More'), 
      '#url' => new Url('employee.list'),
      '#attributes' => array('class' => 'button')
    );
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['limit'] = array(
      '#type' => 'textfield',
      '#title' => t('Limit'),
      '#description' => t('Number of employees to show'),
      '#default_value' => isset($config['limit']) ? 
        $config['limit'] : '',
    );
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('limit', $form_state->getValue('limit'));
  }

   /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $limit = $form_state->getValue('limit');

    if (!is_numeric($limit)) {
      $form_state->setErrorByName('limit', 
        t('Needs to be an integer'));
    } 
    if($limit > MAX_LIMIT){
      $form_state->setErrorByName('limit', 
        t('Must not exceed '.MAX_LIMIT));
    }
  }
} 