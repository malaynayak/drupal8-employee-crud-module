<?php

namespace Drupal\employee\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\employee\events\EmployeeWelcomeEvent;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Session\AccountProxy;

/**
 * Event subscriber for employee welcome event.
 */
class EmployeeWelcomeSubscriber implements EventSubscriberInterface {

  /**
   * The Mail Manager.
   *
   * @var Drupal\Core\Mail\MailManager
   */

  protected $mailManager;

  /**
   * The Logger Factory.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactory
   */

  protected $logger;

  /**
   * The Account Proxy.
   *
   * @var Drupal\Core\Session\AccountProxy
   */

  protected $account;

  /**
   * Constructs the EmployeeWelcomeSubscriber.
   *
   * @param \Drupal\Core\Mail\MailManager $mail_manager
   *   The Mail Manager Plugin.
   * @param Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The Logger Factory.
   * @param Drupal\Core\Session\AccountProxy $account
   *   The Account Proxy.
   */
  public function __construct(MailManager $mail_manager,
    LoggerChannelFactory $logger,
  AccountProxy $account) {
    $this->mailManager = $mail_manager;
    $this->logger = $logger;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['employee.welcome.mail'][] = ['sendWelcomeMail', 0];
    return $events;
  }

  /**
   * Responds to the event "employee.welcome.mail".
   *
   * @param Drupal\employee\events\EmployeeWelcomeEvent $event
   *   The event object.
   */
  public function sendWelcomeMail(EmployeeWelcomeEvent $event) {
    $employee = $event->getEmployeeInfo();
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'employee';
    $key = 'send_welcome_mail';
    $to = $employee->email;
    $langcode = $this->account->getPreferredLangcode();
    $send = TRUE;
    $params['employee'] = $employee;
    $result = $this->mailManager->mail('employee',
      'send_welcome_mail', $to, $langcode, $params, NULL, $send);
    $this->setLogMessage('Employee ' . $employee->id
        . ' added sucessfully and welcome mail has been sent !!');
  }

  /**
   * To set a log message.
   *
   * @param string $message
   *   The message to log.
   */
  private function setLogMessage($message) {
    $this->logger->get('default')
      ->info($message);
  }

}
