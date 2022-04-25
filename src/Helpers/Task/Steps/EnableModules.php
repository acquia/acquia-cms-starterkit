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
   * Set the API keys before module install.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\InstallerQuestions
   */
  protected $installerQuestions;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush
   *   Holds the drush command class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\InstallerQuestions $installer_questions
   *   Holds the InstallerQuestion class object.
   */
  public function __construct(Drush $drush, InstallerQuestions $installer_questions) {
    $this->drushCommand = $drush;
    $this->installerQuestions = $installer_questions;
  }

  /**
   * Run the drush commands to enable Drupal modules.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $packages = JsonParser::installPackages($args['packages']['install']);
    $keys = [];
    if ($args['type'] == "modules") {
      // Install modules.
      $command = array_merge(["en", "--yes"], $packages);

      // Also install toolbar(core) module, allowing user for easily navigation.
      $command[] = "toolbar";

      $keys = $this->installerQuestions->execute($args['starter_kit']);
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
    $args['keys']['STARTER_KIT_PROGRESS'] = 1;
    $this->drushCommand->prepare($command)->run($args['keys']);

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
