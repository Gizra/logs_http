<?php

/**
 * @file
 * Documents API functions for logs_http module.
 */

/**
 * Alter the event which is going to be sent to the log service.
 *
 * Additionally you can stop sending an event to the log service by setting
 * $event['send'] to FALSE. Useful for local environments, where this
 * feature needs to be disabled.
 */
function hook_logs_http_event_alter(&$event) {
  if (Settings::get('environment') == 'local') {
    $event['send'] = FALSE;
  }
}
