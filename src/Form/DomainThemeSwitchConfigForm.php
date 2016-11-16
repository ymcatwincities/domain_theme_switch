<?php

namespace Drupal\domain_theme_switch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class DomainThemeSwitchConfigForm.
 *
 * @package Drupal\domain_theme_switch\Form
 */
class DomainThemeSwitchConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'domain_theme_switch.DomainThemeSwitchConfig',
    ];
  }

  /**
   * Form ID is domain_theme_switch_config_form.
   *
   * @return string
   *   Return form ID.
   */
  public function getFormId() {
    return 'domain_theme_switch_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('domain_theme_switch.DomainThemeSwitchConfig');
    $themes = \Drupal::service('theme_handler')->listInfo();
    $themeNames = array('' => '--Select--');
    foreach ($themes as $key => $value) {
      $themeNames[$key] = $key;
    }
    $all_domain = \Drupal::service('domain.loader')->loadOptionsList();
    foreach ($all_domain as $key => $value) {
      $form['domain' . $key] = array(
        '#type' => 'fieldset',
        '#title' => t('Select Theme for @domain_name', array('@domain_name' => $value))
      );
      $form['domain' . $key][$key] = [
        '#type' => 'select',
        '#title' => t(''),
        '#options' => $themeNames,
        '#default_value' => $config->get($key),
      ];
    }
    if (count($all_domain) === 0) {
      $form['domain_theme_switch_message'] = array(
        '#markup' => t('We did not find any domain records please @link to create the domain.', array('@link' => \Drupal::l(t('click here'), Url::fromRoute('domain.admin')))),
      );
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Validate function for the form.
   *
   * @param array $form
   *   Form items.
   * @param FormStateInterface $form_state
   *   Formstate for validate.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $all_domain = \Drupal::service('domain.loader')->loadOptionsList();
    $config = $this->config('domain_theme_switch.DomainThemeSwitchConfig');
    foreach ($all_domain as $domain_key => $domain_value) {
      $config->set($domain_key, $form_state->getValue($domain_key));
    }
    $config->save();
  }

}
