<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to enable Drupal themes.
 */
class EnableThemes {

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
   * Run the drush commands to enable Drupal modules.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    // Get default, admin theme.
    $packages = [
      $args['themes']['admin'],
      $args['themes']['default'],
    ];

    // Enable themes.
    $command = array_merge(["theme:enable"], [implode(",", $packages)]);
    $this->drushCommand->prepare($command)->run();

    // Set admin theme.
    if (isset($args['themes']['admin'])) {
      $command = array_merge([
        "config:set",
        "system.theme",
        "admin",
        "--yes",
      ], [$args['themes']['admin']]);
      $this->drushCommand->prepare($command)->run();

      // Use admin theme as acquia_claro.
      $command = array_merge([
        "config:set",
        "node.settings",
        "use_admin_theme",
        "--yes",
      ], [TRUE]);
      $this->drushCommand->prepare($command)->run();
    }

    // Set default theme.
    $command = array_merge([
      "config:set",
      "system.theme",
      "default",
      "--yes",
    ], [$args['themes']['default']]);
    $this->drushCommand->prepare($command)->run();

    return StatusCodes::OK;
  }

}
