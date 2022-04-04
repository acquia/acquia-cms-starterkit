<?php

namespace AcquiaCMS\Cli\Helper;

/**
 * Helper class for acquia starter kit.
 */
class ComposerHelper {

  /**
   * Get the project working directory.
   *
   * @return string
   *   The current project directory.
   */
  public function getProjectRootPath(): string {
    return getcwd();
  }

  /**
   * Get modules and themes with 'drupal/' prefix.
   *
   * @param array $modules
   *   The array of modules that need to added in the project.
   * @param array $themes
   *   The array of themes that need to added in the project.
   *
   * @return string
   *   Build string with all modules and themes.
   *   Ex drupal/module_name_1 drupal/theme_name_1 etc.
   */
  public function getRequiredModulesThemes(array $modules, array $themes): string {
    array_walk($themes, function (&$theme, $key) {
      $theme = "drupal/$theme:^1.0";
    });
    array_walk($modules, function (&$module, $key) {
      $module = "drupal/$module:^1.0";
    });
    return implode(" ", array_merge($modules, $themes));
  }

  /**
   * Get list of modules that needs to be enabled.
   *
   * @param array $modules
   *   The array of modules.
   *
   * @return string
   *   The list of module with space separated.
   */
  public function getModuleList(array $modules): string {
    return implode(" ", array_merge($modules));
  }

  /**
   * Get list of themes that needs to be enabled.
   *
   * @param array $themes
   *   The array of themes.
   *
   * @return string
   *   The list of themes with space separated.
   */
  public function getThemeList(array $themes): string {
    return implode(" ", array_merge($themes));
  }

}
