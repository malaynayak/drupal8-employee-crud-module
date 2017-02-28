<?php
/**
 * @file
 * Contains Drupal\employee\plugin\block\EmployeeBlock.
 */

namespace Drupal\bd_contact\Plugin\Block;
 
use Drupal\Core\Block\BlockBase;
use Drupal\bd_contact\EmployeeStorage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a 'BD contact' Block
 *
 * @Block(
 *   id = "employees_block",
 *   admin_label = @Translation("Employee Block"),
 *   category = @Translation("Employee")
 * )
 */
class BDContactBlock extends BlockBase{
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
    
    foreach(EmployeeStorage::getAll(3) as $id=>$row) {
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

    $content['add'] = array(
      '#type' => 'link',
      '#title' => t('More'), 
      '#url' => new Url('employee.list'),
      '#attributes' => array('class' => 'button')
    );
    return $content;
  }

  // /**
  //  * {@inheritdoc}
  //  */
  // public function blockForm($form, FormStateInterface $form_state) {
  //   $form = parent::blockForm($form, $form_state);

  //   // Retrieve existing configuration for this block.
  //   $config = $this->getConfiguration();

  //   // Add a form field to the existing block configuration form.
  //   $form['fax_number'] = array(
  //     '#type' => 'textfield',
  //     '#title' => t('Fax number'),
  //     '#default_value' => isset($config['fax_number']) ? $config['fax_number'] : '',
  //   );
    
  //   return $form;
  // }
} 