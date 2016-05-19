<?php

/**
 * @file
 * Contains LogsHttpRegisterEventTestCase.
 */

namespace Drupal\logs_http\Tests;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\logs_http\Logger\LogsHttpLogger;
use Drupal\simpletest\WebTestBase;

/**
 * Test registration of an event.
 *
 * @group logs_http
 */
class LogsHttpRegisterEventTestCase extends WebTestBase {

  private $logsHttpConfig;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('logs_http');

  /**
   * Test registration of an event.
   */
  function testRegisterEvent() {
    // Trying to set the configuration on the setup method keep fails.
    $this->logsHttpConfig = \Drupal::configFactory()->getEditable('logs_http.settings');
    $this->logsHttpConfig->set('url', 'http://example.com');

    // Test severity.
    \Drupal::logger('logs_http')->notice('Notice 1');
    $events = LogsHttpLogger::getEvents();
    $this->assertFalse($events, 'No notice events registered, as severity level was to high.');

    // Set severity.
    $this->logsHttpConfig->set('severity_level', RfcLogLevel::NOTICE);

    // Test single event.
    LogsHttpLogger::reset();
    \Drupal::logger('logs_http')->notice('Notice 1');
    $events = LogsHttpLogger::getEvents();
    $this->assertEqual(count($events), 1, 'Notice events registered.');

    // Test multiple events.
    LogsHttpLogger::reset();
    // A duplcaited event
    \Drupal::logger('logs_http')->notice('Notice 1');
    \Drupal::logger('logs_http')->notice('Notice 1');

    \Drupal::logger('logs_http')->notice('Notice 2');
    $events = LogsHttpLogger::getEvents();
    $this->assertEqual(count($events), 2, 'Multiple events registered');

    // Get the elements (as they are keyed by an md5 hash).
    $event1 = array_shift($events);
    $event2 = array_shift($events);

    $this->assertEqual($event1['message'], 'Notice 1', 'Correct first event registered.');
    $this->assertEqual($event2['message'], 'Notice 2', 'Correct second event registered.');
  }
}
