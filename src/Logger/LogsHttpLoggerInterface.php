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
   *   If no event was created, since the service is disabled, then an empty
   *   array. Otherwise an array with the following values:
   *   - hash: The hash of the event. This is a result of md5, and it's just a
   *   quick way for us to make sure we don't send over duplicate events
   *   occurred in a single request.
   *   - event: The array of the event.
   *   - is_unique: TRUE indicating if this is the first time we encounter
   *   this event in this specific request. Otherwise, if duplicate, FALSE.
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
