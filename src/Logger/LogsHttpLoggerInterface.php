<?php

namespace Drupal\logs_http\Logger;

use Psr\Log\LoggerInterface;

/**
 * Describes a Logs Http Logger instance.
 */
Interface LogsHttpLoggerInterface extends LoggerInterface {

  /**
   * Clear the events.
   */
  public function reset();

  /**
   * Register an event.
   *
   * @param $level
   * @param message
   * @param array $context
   */
  public function registerEvent($level, $message, array $context = []);

  /**
   * A getter for the current events.
   *
   * @return array
   */
  public function getEvents();

  /**
   * Check weather we should use Logs http module or not.
   *
   * @return bool
   */
  public function isEnabled();

  /**
   * A getter for the url of the endpoint we should send the data to.
   *
   * @return array|mixed|null
   */
  public function getUrl();
}
