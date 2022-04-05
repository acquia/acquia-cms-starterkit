<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

/**
 * Run the drush command to install Drupal site.
 */
class SiteInstall {

  /**
   * A process manager object.
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
   * Run the drush commands to install Drupal.
   */
  public function execute(array $args = []) {
    $this->processManager->add(["./vendor/bin/drush", "site:install", "minimal", "--yes"]);
    return $this->processManager->runAll();
  }

}
