<?php

namespace Drupal\domain_theme_switch\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Implements ThemeNegotiatorInterface.
 */
class ThemeSwitchNegotiator implements ThemeNegotiatorInterface {

  // protected theme variable to set the default theme if no theme is selected.
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
    $switchTheme = TRUE;
    $route = \Drupal::routeMatch()->getRouteObject();
    $is_admin_route = \Drupal::service('router.admin_context')->isAdminRoute($route);
    $current_user = \Drupal::currentUser();
    $user_roles = $current_user->getRoles();
    $has_admin_role = FALSE;
    if (in_array("administrator", $user_roles)) {
      $has_admin_role = TRUE;
    }
    if ($is_admin_route === TRUE && $has_admin_role === TRUE) {
      $switchTheme = FALSE;
    }
    $negotiator = \Drupal::service('domain.negotiator');
    $domain = $negotiator->getActiveDomain();
    if ($domain != NULL) {
      $config = \Drupal::config('domain_theme_switch.DomainThemeSwitchConfig');
      $this->theme = ($config->get($domain->id()) !== NULL) ? $config->get($domain->id()) : NULL;
    }
    return $switchTheme;
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
    return $this->theme;
  }

}
