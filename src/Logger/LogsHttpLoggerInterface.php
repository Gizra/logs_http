<?php

namespace Drupal\logs_http\Logger;

use Psr\Log\LoggerInterface;

/**
 * Describes a Logs Http Logger instance.
 */
interface LogsHttpLoggerInterface extends LoggerInterface {

  /**
   * Clear the events.
   */
  public function reset();

  /**
   * Register an event in the cache.
   *
   * Modules extending this service, in need for different data to be added
   * to the event will likely override this method in the following way:
   *
   * @code
   * public function registerEvent($level, string $message, array $context = []) {
   *   $event = parent::registerEvent($level, $message, $context);
   *
   *   // Update our custom value(s).
   *   $event['foo'] = 'bar';
   *
   *   return $event;
   * }
   * @endcode
   *
   * @param int $level
   *   The severity level.
   * @param string $message
   *   The message that contains the placeholders.
   * @param array $context
   *   The context as passed from the main Logger.
   *
   * @return array
   *   The event that was created, before it's added to the static cache.
   */
  public function registerEvent($level, string $message, array $context = []);

  /**
   * A getter for the current events.
   *
   * @return array
   *   List of events.
   */
  public function getEvents();

  /**
   * Check weather we should use Logs http module or not.
   *
   * @return bool
   *   TRUE if enabled.
   */
  public function isEnabled();

  /**
   * A getter for the url of the endpoint we should send the data to.
   *
   * @return string
   *   The URL.
   */
  public function getUrl();

}
