<?php

/**
 * @file
 * Contains LogsHttpRegisterEventTestCase.
 */

namespace Drupal\Tests\logs_http\Kernel;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\KernelTests\KernelTestBase;
use Drupal\logs_http\Logger\LogsHttpLogger;

/**
 * Test registration of an event.
 *
 * @group logs_http
 */
class LogsHttpRegisterEventTest extends KernelTestBase {

  private $logsHttpConfig;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['logs_http'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Installing needed schema.
    $this->installConfig(['logs_http']);

    // Setup the configuration.
    $this->logsHttpConfig = \Drupal::configFactory()->getEditable('logs_http.settings');
    $this->logsHttpConfig->set('enabled', TRUE);
    $this->logsHttpConfig->set('url', 'http://www.example.com');
    $this->logsHttpConfig->set('severity_level', RfcLogLevel::ERROR);
    $this->logsHttpConfig->save();
  }

  /**
   * Test registration of an event.
   */
  function testRegisterEvent() {
    // Test severity.
    \Drupal::logger('logs_http')->notice('Notice 1');
    $events = LogsHttpLogger::getEvents();
    $this->assertFalse($events, 'No notice events registered, as severity level was to high.');

    // Set severity.
    $this->logsHttpConfig->set('severity_level', RfcLogLevel::NOTICE);
    $this->logsHttpConfig->save();

    // Test single event.
    LogsHttpLogger::reset();
    \Drupal::logger('logs_http')->error('Notice 1');
    $events = LogsHttpLogger::getEvents();
    $this->assertEquals(1, count($events), 'Notice events registered.');

    // Test multiple events.
    LogsHttpLogger::reset();

    // A duplicated event.
    \Drupal::logger('logs_http')->notice('Notice 1');
    \Drupal::logger('logs_http')->notice('Notice 1');

    \Drupal::logger('logs_http')->notice('Notice 2');
    $events = LogsHttpLogger::getEvents();
    $this->assertEquals(2, count($events), 'Multiple events registered');

    // Get the elements (as they are keyed by an md5 hash).
    $event1 = array_shift($events);
    $event2 = array_shift($events);

    $this->assertEquals('Notice 1', $event1['message'], 'Correct first event registered.');
    $this->assertEquals('Notice 2', $event2['message'], 'Correct second event registered.');
  }
}
