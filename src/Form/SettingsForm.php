<?php

/**
 * @file
 */

namespace Drupal\logs_http\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures Logs http settings.
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
    return ['logs_http.settings'];
  }

  /**
   * Holds the name of the keys we holds in the variable.
   */
  public function defaultKeys() {
    return [
      'enabled',
      'url',
      'severity_level',
      'uuid',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('logs_http.settings');

    $form['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#description' => $this->t('Enable Logs HTTP'),
      '#default_value' => $config->get('enabled'),
    );

    $form['url'] = array(
      '#type' => 'url',
      '#title' => $this->t('Endpoint'),
      '#description' => $this->t('The URL to POST the data to.'),
      '#default_value' => $config->get('url'),
    );

    $form['severity_level'] = array(
      '#type' => 'select',
      '#title' => $this->t('Watchdog Severity'),
      '#options' => RfcLogLevel::getLevels(),
      '#default_value' => $config->get('severity_level'),
      '#description' => $this->t('The minimum severity level to be reached before an event is sent to Logs.'),
    );

    $form['uuid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Unique ID'),
      '#description' => $this->t('An arbitrary ID that will identify the environment.'),
      '#default_value' => $config->get('uuid'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('logs_http.settings');

    foreach ($this->defaultKeys() as $key) {
      $config->set($key, $form_state->getValue($key));
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }
}
