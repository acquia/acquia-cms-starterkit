<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to toggle modules based on env.
 */
class ToggleModules {

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
   * Run the drush commands to toggle modules.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $toggleModulesCommand = ["acms:toggle:modules"];
    if (isset($args['no-interaction']) && $args['no-interaction']) {
      $toggleModulesCommand = array_merge($toggleModulesCommand, ["--yes"]);
    }
    return $this->drushCommand->prepare($toggleModulesCommand)->run();
  }

}
