<?php

/**
 * @file
 */

namespace Drupal\logs_http\Logger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Logger\RfcLoggerTrait;

class LogsHttpLogger implements LogsHttpLoggerInterface {
  use RfcLoggerTrait;

  /**
   * A configuration object containing Logs http settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $logMessageParser;

  /**
   * The severity levels array.
   *
   * @var array
   */
  protected $severityLevels;

  /**
   * The cache of the events.
   *
   * @var array
   */
  protected $cache = [];

  /**
   * Clear the events by setting a new array to the variable.
   */
  public function reset() {
    $this->cache = [];
  }

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
    $this->logMessageParser = $parser;
    $this->severityLevels = RfcLogLevel::getLevels();
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
   * Register an event in the cache.
   *
   * To prevent multiple registration of the same error, we check that identical
   * events are not captured twice, thus reducing the final HTTP requests needed.
   *
   * @param $level
   *   The severity level.
   * @param message
   *   The message that contains the placeholders.
   * @param array $context
   *   The context as passed from the main Logger.
   */
  public function registerEvent($level, $message, array $context = []) {
    if (!$this->isEnabled()) {
      return;
    }

    // Populate the message placeholders and then replace them in the message.
    $message_placeholders = $this->logMessageParser->parseMessagePlaceholders($message, $context);
    $message = empty($message_placeholders) ? $message : strtr($message, $message_placeholders);

    $event = [
      'timestamp' => $context['timestamp'],
      'type' => $this->severityLevels[$level]->getUntranslatedString(),
      'ip' => $context['ip'],
      'request_uri' => $context['request_uri'],
      'referer' => $context['referer'],
      'uid' => $context['uid'],
      'link' => strip_tags($context['link']),
      'message' => $message,
      'severity' => $level,
    ];

    if (!empty($context['exception_trace'])) {
      // We avoid unserializing as it seems to causes Logs to fail to index
      // event as JSON.
      $event['exception_trace'] = base64_decode($context['exception_trace']);
    }

    if ($uuid = $this->config->get('uuid')) {
      $event['uuid'] = $uuid;
    }

    // Remove empty values, to prevent errors in the indexing of the JSON.
    $event = $this->arrayRemoveEmpty($event);

    // Prevent identical events.
    $event_clone = $event;
    unset($event_clone['timestamp']);
    $key = md5(serialize($event_clone));
    $this->cache[$key] = $event;
  }

  /**
   * Deep array filter; Remove empty values.
   *
   * @param $haystack
   *   The variable to filter.
   *
   * @return mixed
   */
  protected function arrayRemoveEmpty($haystack) {
    foreach ($haystack as $key => $value) {
      if (is_array($value)) {
        $haystack[$key] = $this->arrayRemoveEmpty($haystack[$key]);
      }

      if (empty($haystack[$key])) {
        unset($haystack[$key]);
      }
    }

    return $haystack;
  }

  /**
   * A getter for the current events.
   *
   * @return array
   *   Returns the current events.
   */
  public function getEvents() {
    return $this->cache;
  }

  /**
   * Check weather we should use Logs http module or not.
   *
   * Determine by checking the 'enabled' configuration, plus the 'url' must not
   * be empty.
   *
   * @return bool
   *   Returns TRUE if currently we should POST the data, otherwise returns FALSE.
   */
  public function isEnabled() {
    return $this->config->get('enabled') && !empty($this->getUrl());
  }

  /**
   * A getter for the url of the endpoint we should send the data to.
   *
   * @return array|mixed|null
   *   Returns the endpoint URL to POST data to.
   */
  public function getUrl() {
    return $this->config->get('url');
  }
}
