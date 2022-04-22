<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to install Drupal site.
 */
class SiteInstall {

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected $drushCommand;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush
   *   Holds the drush command class object.
   */
  public function __construct(Drush $drush) {
    $this->drushCommand = $drush;
  }

  /**
   * Run the drush commands to install Drupal.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $siteInstallCommand = ["site:install", "minimal"];
    if ($args['no-interaction']) {
      $siteInstallCommand = array_merge($siteInstallCommand, ["--yes"]);
    }
    return $this->drushCommand->prepare($siteInstallCommand)->run();
  }

}
