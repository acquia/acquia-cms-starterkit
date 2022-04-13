<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

/**
 * Provides the class to validate if current project is Drupal project.
 */
class ValidateDrupal {

  /**
   * An process manager object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  protected $processManager;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\ProcessManager $processManager
   *   Hold the process manager class object.
   * @param \AcquiaCMS\Cli\Cli $acquiaCmsCli
   *   Hold an Acquia CMS Cli object.
   */
  public function __construct(ProcessManager $processManager, Cli $acquiaCmsCli) {
    $this->processManager = $processManager;
    $this->acquiaCmsCli = $acquiaCmsCli;
  }

  /**
   * Run the commands to check if current project is Drupal project.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :bool {
    $jsonContents = $this->acquiaCmsCli->getRootComposer();
    $jsonContents = json_decode($jsonContents);
    if (isset($jsonContents->extra) && isset($jsonContents->extra->{'drupal-scaffold'})) {
      return TRUE;
    }
    return FALSE;
  }

}
