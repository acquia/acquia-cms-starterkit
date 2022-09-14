<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;
use AcquiaCMS\Cli\Helpers\Task\SharedFactory;

/**
 * Run the drush command to install Drupal site.
 */
class SiteInstall {

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  public $drushCommand;

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
    SharedFactory::setData('password');
    $siteInstallCommand = $args['command'] ?? [
      "site:install",
      "minimal",
      "--site-name=" . $args['name'],
      "--account-pass=" . SharedFactory::getData('password'),
    ];
    if (isset($args['no-interaction']) && $args['no-interaction']) {
      $siteInstallCommand = array_merge($siteInstallCommand, ["--yes"]);
    }
    return $this->drushCommand->prepare($siteInstallCommand)->run();
  }

}
