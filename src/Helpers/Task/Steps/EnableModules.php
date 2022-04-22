<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to enable Drupal modules.
 */
class EnableModules {

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
    $packages = JsonParser::installPackages($args['packages']['install']);

    if ($args['type'] == "modules") {
      // Install modules.
      $command = array_merge(["en", "--yes"], $packages);

      // Also install toolbar(core) module, allowing user for easily navigation.
      $command[] = "toolbar";
    }
    else {

      // Enable olivero theme (if not selected).
      // @todo Provide this configurable.
      if (!isset($args['packages']['default'])) {
        $packages[] = 'olivero';
      }

      // Enable themes.
      $command = array_merge(["theme:enable"], [implode(",", $packages)]);
    }
    $this->drushCommand->prepare($command)->run();

    // Set default and/or admin theme.
    if (isset($args['packages']['admin'])) {
      $command = array_merge([
        "config:set",
        "system.theme",
        "admin",
        "--yes",
      ], [$args['packages']['admin']]);
      $this->drushCommand->prepare($command)->run();
    }

    if ($args['type'] == "themes") {
      // Set default theme as olivero (if not defined)
      $theme = $args['packages']['default'] ?? "olivero";

      $command = array_merge([
        "config:set",
        "system.theme",
        "default",
        "--yes",
      ], [$theme]);
      $this->drushCommand->prepare($command)->run();
    }

    return StatusCodes::OK;
  }

}
