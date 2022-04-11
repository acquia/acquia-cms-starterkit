<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

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
    $inputArgument = array_merge(["./vendor/bin/drush", "en", "--yes"], $args['modules']);
    $this->processManager->add($inputArgument);
    return $this->processManager->runAll();
  }

}
