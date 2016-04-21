<?php

/**
 * @file
 * Contains \Drupal\logs_http\Logger\LogsHttpLogger.
 */

namespace Drupal\logs_http\Logger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

class LogsHttpLogger implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * A configuration object containing logs_http settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * The severity levels array.
   */
  protected $severity_levels;

  /**
   * Constructs a LogsHttpLogger object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LogMessageParserInterface $parser) {
    $this->config = $config_factory->get('logs_http.settings');
    $this->parser = $parser;
    $this->severity_levels = RfcLogLevel::getLevels();
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    if ($level > $this->config->get('severity_level')) {
      // Severity level is above the ones we want to log.
      return;
    }

    $this->registerEvent($level, $message, $context);
  }

  /**
   * Register an event in a static cache.
   *
   * To prevent multiple registration of the same error, we check that identical
   * events are not captured twice, thus reducing the final HTTP requests needed.
   *
   * @param $level
   *  The severity level.
   * @param message
   *  The message that contains the placeholders.
   * @param array $context
   *  The context as passed from the main Logger.
   */
  protected function registerEvent($level, $message, array $context = array()) {
    if (!logs_http_get_http_url()) {
      return;
    }

    // Populate the message placeholders and then replace them in the message.
    $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);
    $message = empty($message_placeholders) ? $message : strtr($message, $message_placeholders);

    $events = &drupal_static('logs_http_events', array());

    $event = array(
      'timestamp' => $context['timestamp'],
      'type' => $this->severity_levels[$level]->getUntranslatedString(),
      'ip' => $context['ip'],
      'request_uri' => $context['request_uri'],
      'referer' => $context['referer'],
      'uid' => $context['uid'],
      'link' => strip_tags($context['link']),
      'message' => $message,
      'severity' => $level,
    );

    if (!empty($context['exception_trace'])) {
      // @todo: We avoid unserializing as it seems to causes Logs to fail
      // to index event as JSON.
      $event['exception_trace'] = base64_decode($context['exception_trace']);
    }

    if ($uuid = $this->config->get('uuid')) {
      $event['uuid'] = $uuid;
    }

    // Remove empty values, to prevent errors in the indexing of the JSON.
    $event = logs_http_array_remove_empty($event);

    // Prevent identical events.
    $event_clone = $event;
    unset($event_clone['timestamp']);
    $key = md5(serialize($event_clone));
    $events[$key] = $event;
  }
}
