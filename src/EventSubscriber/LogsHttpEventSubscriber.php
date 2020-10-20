<?php

namespace Drupal\logs_http\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Event subscribed for Logs http.
 */
class LogsHttpEventSubscriber implements EventSubscriberInterface {

  /**
   * Initializes Logs http module requirements.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRequest(GetResponseEvent $event) {
    drupal_register_shutdown_function('logs_http_shutdown');
    set_exception_handler('_logs_http_exception_handler');
  }

  /**
   * Implements EventSubscriberInterface::getSubscribedEvents().
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    // Setting high priority for this subscription in order to execute it soon
    // enough.
    $events[KernelEvents::REQUEST][] = ['onRequest', 1000];

    return $events;
  }

}
