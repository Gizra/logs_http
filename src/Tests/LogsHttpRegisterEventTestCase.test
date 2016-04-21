<?php

/**
 * @file
 * Contains LogsHttpRegisterEventTestCase.
 */

namespace Drupal\logs_http\Tests;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\simpletest\WebTestBase;


class LogsHttpRegisterEventTestCase extends WebTestBase {

  private $logsHttpConfig;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('logs_http');

  public static function getInfo() {
    return array(
      'name' => 'Register event',
      'description' => 'Test registration of an event.',
      'group' => 'Logs HTTP',
    );
  }

  public function __construct($test_id) {
    parent::__construct($test_id);

    // Getting the 'logs_http' configuration object.
    $this->logsHttpConfig = \Drupal::configFactory()->getEditable('logs_http.settings');
  }

  function setUp() {
    parent::setUp();

    // Add a dummy URL.
    $this->logsHttpConfig->set('url', 'http://example.com');
  }

  /**
   * Test registration of an event.
   */
  function testRegisterEvent() {
    // Test severity.
    \Drupal::logger('logs_http')->notice('Notice 1');
    $events = logs_http_get_registered_events();
    $this->assertFalse($events, 'No notice events registered, as severity level was to high.');

    // Set severity.
    $this->logsHttpConfig->set('severity_level', RfcLogLevel::NOTICE);

    // Test single event.
    drupal_static_reset('logs_http_events');
    \Drupal::logger('logs_http')->notice('Notice 1');
    $events = logs_http_get_registered_events();
    $this->assertEqual(count($events), 1, 'Notice events registered.');

    // Test multiple events.
    drupal_static_reset('logs_http_events');
    // A duplcaited event
    \Drupal::logger('logs_http')->notice('Notice 1');
    \Drupal::logger('logs_http')->notice('Notice 1');

    \Drupal::logger('logs_http')->notice('Notice 2');
    $events = logs_http_get_registered_events();
    $this->assertEqual(count($events), 2, 'Multiple events registered');

    // Get the elements (as they are keyed by an md5 hash).
    $event1 = array_shift($events);
    $event2 = array_shift($events);

    $this->assertEqual($event1['message'], 'Notice 1', 'Correct first event registered.');
    $this->assertEqual($event2['message'], 'Notice 2', 'Correct second event registered.');
  }
}
