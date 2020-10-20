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
   * @param int $level
   *   The severity level.
   * @param string $message
   *   The message that contains the placeholders.
   * @param array $context
   *   The context as passed from the main Logger.
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
