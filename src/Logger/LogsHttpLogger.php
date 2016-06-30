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
   * A configuration object containing Logs http settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected static $config;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * The severity levels array.
   *
   * @var array
   */
  protected $severityLevels;

  /**
   * Cache the events.
   *
   * @var array
   */
  protected static $cache = [];

  /**
   * WIP.
   * On a fatal error Drupal is not creating an instance of this class.
   */
  public static function validateConfig() {
    if (!static::$config) {
      static::$config = \Drupal::service('config.factory')->get('logs_http.settings');
    }
  }

  /**
   * Clear the events by setting a new array to the variable.
   */
  public static function reset() {
    static::$cache = [];
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
    static::$config = $config_factory->get('logs_http.settings');
    $this->parser = $parser;
    $this->severityLevels = RfcLogLevel::getLevels();
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    static::validateConfig();
    if ($level > static::$config->get('severity_level')) {
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
  protected function registerEvent($level, $message, array $context = []) {
    if (!$this->isEnabled()) {
      return;
    }

    // Populate the message placeholders and then replace them in the message.
    $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);
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

    if ($uuid = static::$config->get('uuid')) {
      $event['uuid'] = $uuid;
    }

    // Remove empty values, to prevent errors in the indexing of the JSON.
    $event = $this->arrayRemoveEmpty($event);

    // Prevent identical events.
    $event_clone = $event;
    unset($event_clone['timestamp']);
    $key = md5(serialize($event_clone));
    static::$cache[$key] = $event;
  }

  /**
   * Deep array filter.
   *
   * Remove empty values.
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
   *  Returns the current events.
   */
  public static function getEvents() {
    return static::$cache;
  }

  /**
   * Check if currently the configuration are set to send the errors and the url
   * on the configuration is not empty.
   *
   * @return bool
   *  Returns TRUE if currently we should POST the data, otherwise returns FALSE.
   */
  public static function isEnabled() {
    static::validateConfig();
    return !!static::$config->get('enabled') && !empty(static::getUrl());
  }

  /**
   * A getter for the url of the endpoint we should send the data to.
   *
   * @return array|mixed|null
   *  Returns the endpoint URL to POST data to.
   */
  public static function getUrl() {
    static::validateConfig();
    return static::$config->get('url');
  }
}
