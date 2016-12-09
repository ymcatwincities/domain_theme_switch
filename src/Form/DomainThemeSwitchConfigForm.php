<?php

namespace Drupal\domain_theme_switch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\domain\DomainLoader;

/**
 * Class DomainThemeSwitchConfigForm.
 *
 * @package Drupal\domain_theme_switch\Form
 */
class DomainThemeSwitchConfigForm extends ConfigFormBase {

  /**
   * Drupal\domain\DomainLoader definition.
   *
   * @var \Drupal\domain\DomainLoader
   */
  protected $domainLoader;

  /**
   * 
   * @param DomainLoader $domain_loader
   */
  public function __construct(DomainLoader $domain_loader
  ) {
    $this->domainLoader = $domain_loader;
  }

  /**
   * 
   * @param ContainerInterface $container
   * @return \static
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('domain.loader')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'domain_theme_switch.settings',
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
    $config = $this->config('domain_theme_switch.settings');
    $themes = \Drupal::service('theme_handler')->listInfo();
    $themeNames = array('' => '--Select--');
    foreach ($themes as $key => $value) {
      $themeNames[$key] = $key;
    }
    $domains = $this->domainLoader->loadMultipleSorted();
    foreach ($domains as $domain) {
      $domainid = $domain->id();
      $hostname = $domain->get('name');
      $form['domain' . $domainid] = array(
        '#type' => 'fieldset',
        '#title' => t('Select Theme for @domain_name', array('@domain_name' => $hostname))
      );
      $form['domain' . $domainid][$domainid] = [
        '#type' => 'select',
        '#title' => t(''),
        '#options' => $themeNames,
        '#default_value' => $config->get($domainid),
      ];
    }
    if (count($domains) === 0) {
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
    $config = $this->config('domain_theme_switch.settings');
    foreach ($all_domain as $domain_key => $domain_value) {
      $config->set($domain_key, $form_state->getValue($domain_key));
    }
    $config->save();
  }

}
