<?php

namespace Drupal\domain_theme_switch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Integration of DominThemeSwitcher.
 *
 * @group domain_theme_form
 */
class DomainThemeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'domain_theme_switch_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Use the Form API to define form elements.

    $doaminThemes = unserialize(\Drupal::state()->get('domainthemes'));
    $themes = \Drupal::service('theme_handler')->listInfo();
    $themeNames = array('' => '--Select--');
    foreach ($themes as $key => $value) {
      $themeNames[$key] = $key;
    }
    $allDomain = \Drupal::service('domain.loader')->loadOptionsList();
    foreach ($allDomain as $key => $value) {
      $form['domain' . $key] = array(
        '#type' => 'fieldset',
        '#title' => $value,
      );
      $form['domain' . $key][$key] = [
        '#type' => 'select',
        '#title' => t('Select Theme'),
        '#options' => $themeNames,
        '#default_value' => $doaminThemes[$key],
      ];
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save configuration'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate the form values.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $allDomain = \Drupal::service('domain.loader')->loadOptionsList();
    $domainTheme = array();
    foreach ($allDomain as $key => $value) {
      $domainTheme[$key] = $form_state->getValue($key);
    }

    \Drupal::state()->set('domainthemes', serialize(array_filter($domainTheme)));
    drupal_set_message("Domain theme configuration saved succefully");
  }

}
