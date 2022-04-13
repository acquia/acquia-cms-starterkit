<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

/**
 * Run the drush command to enable Drupal modules.
 */
class EnableModules {

  /**
   * An process manager object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  protected $processManager;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\ProcessManager $processManager
   *   Hold the process manager class object.
   */
  public function __construct(ProcessManager $processManager) {
    $this->processManager = $processManager;
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
      $command = array_merge(["./vendor/bin/drush", "en", "--yes"], $packages);
    }
    else {
      // Enable themes.
      $command = array_merge(["./vendor/bin/drush", "theme:enable"], [implode(",", $packages)]);
    }
    $this->processManager->add($command);

    // Set default and/or admin theme.
    if (isset($args['packages']['admin'])) {
      $command = array_merge([
        "./vendor/bin/drush",
        "config:set",
        "system.theme",
        "admin",
        "--yes",
      ], [$args['packages']['admin']]);
      $this->processManager->add($command);
    }
    if (isset($args['packages']['default'])) {
      $command = array_merge([
        "./vendor/bin/drush",
        "config:set",
        "system.theme",
        "default",
        "--yes",
      ], [$args['packages']['default']]);
      $this->processManager->add($command);
    }
    return $this->processManager->runAll();
  }

}
