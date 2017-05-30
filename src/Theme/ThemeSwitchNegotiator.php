<?php

namespace Drupal\domain_theme_switch\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Session\AccountInterface;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Implements ThemeNegotiatorInterface.
 */
class ThemeSwitchNegotiator implements ThemeNegotiatorInterface {

  /**
   * Protected theme variable to set the default theme againt the domain.
   *
   * @var string
   *   Return theme name for the curret domain.
   */
  protected $theme = NULL;


  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $negotiator;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new EntityConverter.
   *
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The route admin context service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\domain\DomainNegotiatorInterface $negotiator
   *   The domain negotiator.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(AdminContext $admin_context,
      AccountInterface $current_user,
      DomainNegotiatorInterface $negotiator,
      ConfigFactoryInterface $config_factory) {
    $this->adminContext  = $admin_context;
    $this->currentUser   = $current_user;
    $this->negotiator    = $negotiator;
    $this->configFactory = $config_factory;
  }

  /**
   * Whether this theme negotiator should be used to set the theme.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match object.
   *
   * @return bool
   *   TRUE if this negotiator should be used or FALSE to let other negotiators
   *   decide.
   */
  public function applies(RouteMatchInterface $route_match) {
    $switch_theme   = TRUE;
    $is_admin_route = $this->adminContext->isAdminRoute($route_match->getRouteObject());
    $hasAdminPerm   = $this->currentUser->hasPermission('administer permissions');

    if ($is_admin_route === TRUE && $hasAdminPerm === TRUE) {
      $switch_theme = FALSE;
    }
    return $switch_theme;
  }

  /**
   * Determine the active theme for the request.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match object.
   *
   * @return string|null
   *   The name of the theme, or NULL if other negotiators, like the configured
   *   default one, should be used instead.
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    $domain = $this->negotiator->getActiveDomain();
    if ($domain !== NULL) {
      $config = $this->configFactory->get('domain_theme_switch.settings');
      $this->theme = ($config->get($domain->id()) !== NULL) ? $config->get($domain->id()) : NULL;
    }
    return ($this->theme !== NULL) ? $this->theme : NULL;
  }

}
