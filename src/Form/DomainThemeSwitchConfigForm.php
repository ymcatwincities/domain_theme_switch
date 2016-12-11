<?php

namespace Drupal\domain_theme_switch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
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
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Construct function.
   *
   * @param DomainLoader $domain_loader
   *   Load the domain records.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
      DomainLoader $domain_loader,
      ThemeHandlerInterface $theme_handler
  ) {
    parent::__construct($config_factory);
    $this->domainLoader = $domain_loader;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Create function return static domain loader configuration.
   *
   * @param ContainerInterface $container
   *   Load the ContainerInterface.
   *
   * @return \static
   *   return domain loader configuration.
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory'),
        $container->get('domain.loader'),
        $container->get('theme_handler')
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
   * Function to get the list of installed themes.
   *
   * @return type array.
   *   List of theme available.
   */
  public function getThemeList() {
    $themeName = array_keys($this->themeHandler->listInfo());
    return array_combine($themeName, $themeName);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('domain_theme_switch.settings');
    $themeNames = array_merge(
        array('' => '--Select--'),
        $this->getThemeList());
    $domains = $this->domainLoader->loadMultipleSorted();
    foreach ($domains as $domain) {
      $domainId = $domain->id();
      $hostname = $domain->get('name');
      $form['domain' . $domainId] = array(
        '#type' => 'fieldset',
        '#title' => $this->t('Select Theme for @domain_name', array(
          '@domain_name' => $hostname
        ))
      );
      $form['domain' . $domainId][$domainId] = [
        '#type' => 'select',
        '#options' => $themeNames,
        '#default_value' => $config->get($domainId),
      ];
    }
    if (count($domains) === 0) {
      $form['domain_theme_switch_message'] = array(
        '#markup' => $this->t('We did not find any domain records
         please @link to create the domain.', array(
          '@link' => $this->l($this->t('click here'),
              Url::fromRoute('domain.admin'))
            )
        ),
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
  public function validateForm(array &$form,
      FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form,
      FormStateInterface $form_state
    ) {
    parent::submitForm($form, $form_state);
    $domains = $this->domainLoader->loadMultipleSorted();
    $config = $this->config('domain_theme_switch.settings');
    foreach ($domains as $domain_key => $domain) {
      $config->set($domain_key, $form_state->getValue($domain_key));
    }
    $config->save();
  }

}
