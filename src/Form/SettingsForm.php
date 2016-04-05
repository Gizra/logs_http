<?php

/**
 * @file
 * Contains \Drupal\devel\Form\SettingsForm.
 */

namespace Drupal\logs_http\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Defines a form that configures devel settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'logs_http_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'logs_http.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $form['logs_http_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Logs HTTP API'),
      '#description' => t('Enable Logs HTTP POST'),
      '#default_value' => \Drupal::state()->get('logs_http_enabled', TRUE),
    );

    $form['logs_http_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Endpoint'),
      '#description' => t('The URL to POST the data to.'),
      '#default_value' => \Drupal::state()->get('logs_http_url', NULL),
    );

    $options = RfcLogLevel::getLevels();

    $form['logs_http_severity_level'] = array(
      '#type' => 'select',
      '#title' => t('Watchdog Severity'),
      '#options' => $options,
      '#default_value' => \Drupal::state()->get('logs_http_severity_level', RfcLogLevel::ERROR),
      '#description' => t('The minimum severity level to be reached before an event is pushed to Logs.'),
    );

    $form['logs_http_uuid'] = array(
      '#type' => 'textfield',
      '#title' => t('Unique ID'),
      '#description' => t('An arbitrary ID that will identify the environment.'),
      '#default_value' => \Drupal::state()->get('logs_http_uuid'),
    );

    return parent::buildForm($form, $form_state);
  }
}
