<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

/**
 * Run the drush command to enable Drupal modules.
 */
class DownloadModules {

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
   * Run the drush commands to download Drupal modules & themes.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :bool {
    $modulesOrThemes = array_map(function ($moduleOrTheme) {
      return "drupal/$moduleOrTheme";
    }, $args['modules'], $args['themes']);
    $inputArgument = array_merge(["composer", "require"], $modulesOrThemes);
    $this->processManager->add($inputArgument);
    return $this->processManager->runAll();
  }

}
