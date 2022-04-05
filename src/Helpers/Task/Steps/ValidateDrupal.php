<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

/**
 * Provides the class to validate if current project is Drupal project.
 */
class ValidateDrupal {

  /**
   * An process manager object.
   *
   * @var AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  protected $processManager;

  /**
   * Constructs an object.
   *
   * @param AcquiaCMS\Cli\Helpers\Process\ProcessManager $processManager
   *   Hold the process manager class object.
   */
  public function __construct(ProcessManager $processManager) {
    $this->processManager = $processManager;
  }

  /**
   * Run the commands to check if current project is Drupal project.
   */
  public function execute($args = []) {
    $this->processManager->add(["composer", "config", "extra.drupal-scaffold"]);
    $process = $this->processManager->getLastProcess();
    $process->setTty(FALSE);
    return $this->processManager->runAll();
  }

}
