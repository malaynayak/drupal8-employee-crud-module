<?php

namespace Drupal\employee\forms;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\employee\EmployeeStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\employee\events\EmployeeWelcomeEvent;
use Drupal\file\Entity\File;

class EmployeeForm implements FormInterface {

  function getFormID() {
    return 'employee_add';
  }

  function buildForm(array $form, FormStateInterface $form_state,
    $employee = NULL) {
    if($employee){
      if($employee == 'invalid'){
        drupal_set_message(t('Invalid employee record'), 'error');
        return new RedirectResponse(Drupal::url('employee.list'));
      }
      $form['eid'] = array(
        '#type' => 'hidden',
        '#value' => $employee->id
      );
    }
    $form['#attributes']['novalidate'] = '';
    $form['general'] = array(
      '#type' => 'details',
      "#title" => "General Details",
      '#open' => TRUE
    );

    $form['general']['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => true,
      '#default_value' => ($employee)?$employee->name:''
    );

    $form['general']['email'] = array(
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => true,
      '#default_value' => ($employee)?$employee->email:''
    );

    $form['general']['department'] = array(
      '#type' => 'select',
      '#title' => t('Department'),
      '#options' => array(
        '' => 'Select Department',
        'Development' => 'Development',
        'HR' => 'HR',
        'Sales' => 'Sales',
        'Marketing' => 'Marketing'
      ),
      '#required' => true,
      '#default_value' => ($employee)?$employee->department:''
    );

    $form['general']['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Active?'),
      '#default_value' => ($employee)?$employee->status:1
    );

    $form['address_details'] = array(
      '#type' => 'details',
      "#title" => "Address Details",
      '#open' => TRUE
    );

    $form['address_details']['address'] = array(
      '#type' => 'textarea',
      '#title' => t('Address'),
      '#required' => true,
      '#default_value' => ($employee)?$employee->address:''
    );

    $form['address_details']['country'] = array(
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => $this->getCountries(),
      '#required' => true,
      '#default_value' => ($employee)?$employee->country:'',
      '#ajax' => [
        'callback' => array($this, 'loadStates'),
        'event' => 'change',
        'wrapper' => 'states',
      ],
    );
    $changed_country = $form_state->getValue('country');
    if($employee){
      if(!empty($changed_country)){
        $selected_country = $changed_country;
      } else {
        $selected_country = $employee->country;
      }
    } else {
      $selected_country = $changed_country;
    }

    $states = $this->getStates($selected_country);
    $form['address_details']['state'] = array(
      '#type' => 'select',
      '#prefix' => '<div id="states">',
      '#title' => t('State'),
      '#options' => $states,
      '#required' => true,
      '#suffix' => '</div>',
      '#default_value' => ($employee)?$employee->state:'',
      '#validated' => TRUE
    );

    $form['upload'] = array(
      '#type' => 'details',
      "#title" => "Profile Pic",
      '#open' => TRUE
    );

    $form['upload']['profile_pic'] = array(
      '#type' => 'managed_file',
      '#upload_location' => 'public://employee_images/',
      '#multiple' => false,
      '#upload_validators' => array(
        'file_validate_extensions' => array('png gif jpg jpeg jfif'),
        'file_validate_size' => array(25600000),
        //'file_validate_image_resolution' => array('800x600', '400x300'),
      ),
      '#title' => t('Upload a Profile Picture'),
      '#default_value' => ($employee)?array($employee->profile_pic):'',
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save'
    );

    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => 'Cancel',
      '#attributes' => array('class' => ['button', 'button--primary']),
      '#url' => Url::fromRoute('employee.list'),
    );
    return $form;
  }

  function loadStates(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['address_details']['state'];
  }

  function getCountries(){
    return [
      '' => 'Select Country',
      'India' => 'India',
      'Usa' => "Usa",
      'Russia' => "Russia"
    ];
  }

  function getStates($selected_country){
   $states = [
     'India' =>  [
       '' => 'Select State',
       'Odisha'=>'Odisha',
       'Telangana'=>'Telangana',
       'Gujarat'=>'Gujarat',
       'Rajasthan'=>'Rajasthan',
     ],
     'Usa' =>  [
       '' => 'Select State',
       'Texas'=>'Texas',
       'Californea'=>'Californea',
     ],
     'Russia' => [
       '' => 'Select State',
       'Moscow'=>'Moscow',
       'Saints Petesberg'=>'Saints Petesberg',
     ]
   ];
   return ($selected_country) ? $states[$selected_country] : [''=>'Select State'];
  }
  function validateForm(array &$form, FormStateInterface $form_state) {
      $email = $form_state->getValue('email');
      if (!empty($email) && (filter_var($email,
        FILTER_VALIDATE_EMAIL) === false)) {
        $form_state->setErrorByName('email', t('Invalid email'));
      }
      $id = $form_state->getValue('eid');
      if(!empty($id)){
        if (!EmployeeStorage::checkUniqueEmail($email,$id)) {
          $form_state->setErrorByName('email', t('This email has already been taken!'));
        }
      } else {
        if (!EmployeeStorage::checkUniqueEmail($email)) {
          $form_state->setErrorByName('email', t('The email has already been taken!'));
        }
      }
  }

  function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('eid');
    $file_usage = Drupal::service('file.usage');
    $profile_pic_fid = NUll;
    $image = $form_state->getValue('profile_pic');
    if(!empty($image)){
      $profile_pic_fid = $image[0];
    }
    $fields = array(
      'name' => SafeMarkup::checkPlain($form_state->getValue('name')),
      'email' => SafeMarkup::checkPlain($form_state->getValue('email')),
      'department' => $form_state->getValue('department'),
      'country' => $form_state->getValue('country'),
      'state' => $form_state->getValue('state'),
      'address' => SafeMarkup::checkPlain($form_state->getValue('address')),
      'status' => $form_state->getValue('status'),
      'profile_pic' => $profile_pic_fid,
    );
    if(!empty($id) && EmployeeStorage::exists($id)){
      $employee = EmployeeStorage::load($id);
      if($profile_pic_fid){
        if($profile_pic_fid !== $employee->profile_pic){
          file_delete($employee->profile_pic);
          $file = File::load($profile_pic_fid);
          $file->setPermanent();
          $file->save();
          $file_usage->add($file, 'employee', 'file', $id);
        }
      } else {
        file_delete($employee->profile_pic);
      }
      EmployeeStorage::update($id,$fields);
      $message = 'Employee updated sucessfully';
    } else {
      $new_employee_id = EmployeeStorage::add($fields);
      if($profile_pic_fid){
        $file = File::load($profile_pic_fid);
        $file->setPermanent();
        $file->save();
        $file_usage->add($file, 'employee', 'file', $new_employee_id);
      }
      //$this->dispatchEmployeeWelcomeMailEvent($new_employee_id);
      $message = 'Employee created sucessfully';
    }
    drupal_set_message(t($message));
    $form_state->setRedirect('employee.list');
    return;
  }

  private function dispatchEmployeeWelcomeMailEvent($employee_id){
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new EmployeeWelcomeEvent($employee_id);
    $dispatcher->dispatch('employee.welcome.mail', $event);
  }

}
