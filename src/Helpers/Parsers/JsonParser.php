<?php

namespace AcquiaCMS\Cli\Helpers\Parsers;

/**
 * The JSON Parser class to parse modules/themes/packages from array.
 */
class JsonParser {

  /**
   * Creates the download packages array passing to composer command.
   *
   * @param array $packages
   *   An array of modules/themes/packages etc.
   *
   * @return array|string[]
   *   Returns an array of parsed packages.
   */
  public static function downloadPackages(array $packages) :array {
    return array_map(function ($package) {
      if (strpos($package, '/') === FALSE) {
        $package = "drupal/" . $package;
      }
      return $package;
    }, $packages);
  }

  /**
   * Creates the Install packages array passing to drush command.
   *
   * @param array $packages
   *   An array of modules/themes/packages etc.
   *
   * @return array|string[]
   *   Returns an array of parsed packages.
   */
  public static function installPackages(array $packages) :array {
    return array_map(function ($package) {
      $regex = '/(.*\/)?([a-zA-z0-9_-]+)(.*)/m';
      $replace = '$2';
      return preg_replace($regex, $replace, $package);
    }, $packages);
  }

}
