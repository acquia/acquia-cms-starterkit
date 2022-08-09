<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to import site studio packagess.
 */
class InitNextjsApp {

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
    $initNextjsAppCommand = ["acms:headless:new-nextjs"];
    if (isset($args['--site-url']) && $args['--site-url']) {
      $initNextjsAppCommand = array_merge($initNextjsAppCommand, ["--site-url=" . $args['--site-url']]);
    }
    if (isset($args['--env-file']) && $args['--env-file']) {
      $initNextjsAppCommand = array_merge($initNextjsAppCommand, ["--env-file=" . $args['--env-file']]);
    }
    return $this->drushCommand->prepare($initNextjsAppCommand)->run();
  }

}
