<?php

/**
 * @file
 * Logs HTTP module admin page.
 */

/**
 * Logs HTTP POST API Configuration Form.
 */
function logs_http_admin_settings($form, &$form_state) {
  $form['logs_http_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Logs HTTP API'),
    '#description' => t('Enable Logs HTTP POST'),
    '#default_value' => variable_get('logs_http_enabled', TRUE),
  );

  $form['logs_http_debugging_mode'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debugging mode'),
    '#description' => t('If selected, each log entry will contain a lot more details about the current http request.'),
    '#default_value' => variable_get('logs_http_debugging_mode', FALSE),
  );

  $form['logs_http_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Endpoint'),
    '#description' => t('The URL to POST the data to.'),
    '#default_value' => variable_get('logs_http_url', NULL),
  );

  $options = array(
    WATCHDOG_EMERGENCY => t('Emergency'),
    WATCHDOG_ALERT => t('Alert'),
    WATCHDOG_CRITICAL => t('Critical'),
    WATCHDOG_ERROR => t('Error'),
    WATCHDOG_WARNING => t('Warning'),
    WATCHDOG_NOTICE => t('Notice'),
    WATCHDOG_INFO => t('Info'),
    WATCHDOG_DEBUG => t('Debug'),
  );

  $form['logs_http_severity_level'] = array(
    '#type' => 'select',
    '#title' => t('Watchdog Severity'),
    '#options' => $options,
    '#default_value' => variable_get('logs_http_severity_level', WATCHDOG_ERROR),
    '#description' => t('The minimum severity level to be reached before an event is pushed to Logs.'),
  );

  $form['logs_http_uuid'] = array(
    '#type' => 'textfield',
    '#title' => t('Unique ID'),
    '#description' => t('An arbitrary ID that will identify the environment.'),
    '#default_value' => variable_get('logs_http_uuid'),
  );

  return system_settings_form($form);
}

