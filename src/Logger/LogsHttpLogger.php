<?php

/**
 * @file
 * Contains \Drupal\logs_http\Logger\LogsHttpLogger.
 */

namespace Drupal\logs_http\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

class LogsHttpLogger implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    if ($level > \Drupal::configFactory()->getEditable('logs_http.settings')->get('severity_level')) {
      // Severity level is above the ones we want to log.
      return;
    }

    $this->registerEvent($level, $context);
  }

  /**
   * Register an event in a static cache.
   *
   * To prevent multiple registration of the same error, we check that identical
   * events are not captured twice, thus reducing the final HTTP requests needed.
   *
   * @param $level
   *  The severity level.
   * @param array $context
   *  The context as passed from the main Logger.
   */
  protected function registerEvent($level, array $context = array()) {
    if (!logs_http_get_http_url()) {
      return;
    }

    $events = &drupal_static('logs_http_events', array());

    $event = array(
      'timestamp' => $context['timestamp'],
      'type' => $context['%type'],
      'ip' => $context['ip'],
      'request_uri' => $context['request_uri'],
      'referer' => $context['referer'],
      'uid' => $context['uid'],
      'link' => strip_tags($context['link']),
      // 'message' => empty($log_entry['variables']) ? $log_entry['message'] : strtr($log_entry['message'], $log_entry['variables']),
      'message' => $context['@message']['string'],
      'severity' => $level,
    );

//    if (!empty($log_entry['variables']['exception_trace'])) {
//      // @todo: We avoid unserializing as it seems to causes Logs to fail
//      // to index event as JSON.
//      $event['exception_trace'] = base64_decode($log_entry['variables']['exception_trace']);
//    }

    if ($uuid = \Drupal::configFactory()->getEditable('logs_http.settings')->get('uuid')) {
      $event['uuid'] = $uuid;
    }

    // Remove empty values, to prevent errors in the indexing of the JSON.
//    $event = logs_http_array_remove_empty($event);

    // Prevent identical events.
    $event_clone = $event;
    unset($event_clone['timestamp']);
    $key = md5(serialize($event_clone));
    $events[$key] = $event;
  }
}
