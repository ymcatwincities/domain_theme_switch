<?php

namespace Drupal\domain_theme_switch\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Implements ThemeNegotiatorInterface.
 */
class ThemeSwitchNegotiator implements ThemeNegotiatorInterface {

  // Protected theme variable to set the default theme if no theme is selected.
  protected $theme = NULL;

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
    $switch_theme = TRUE;
    $route = \Drupal::routeMatch()->getRouteObject();

    $is_admin_route = \Drupal::service('router.admin_context')->isAdminRoute($route);
    $hasAdminPerm = \Drupal::currentUser()->hasPermission('administer permissions');

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
    $domain = \Drupal::service('domain.negotiator')->getActiveDomain();
    if ($domain != NULL) {
      $config = \Drupal::config('domain_theme_switch.settings');
      $this->theme = ($config->get($domain->id()) !== NULL) ? $config->get($domain->id()) : NULL;
    }
    return ($this->theme !== NULL) ? $this->theme : NULL;
  }

}
