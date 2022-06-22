<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to import site studio packagess.
 */
class SiteStudioPackageImport {

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
    $siteStudioPackageImportCommand = ["acms:import-site-studio-packages"];
    if (isset($args['no-interaction']) && $args['no-interaction']) {
      $siteStudioPackageImportCommand = array_merge($siteStudioPackageImportCommand, ["--yes"]);
    }
    return $this->drushCommand->prepare($siteStudioPackageImportCommand)->run();
  }

}
