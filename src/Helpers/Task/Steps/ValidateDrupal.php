<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

class ValidateDrupal {
  public function __construct(ProcessManager $processManager) {
    $this->processManager = $processManager;
  }

  public function execute($args = []) {
    $this->processManager->add(["composer", "config", "extra.drupal-scaffold"]);
    $process = $this->processManager->getLastProcess();
    $process->setTty(FALSE);
    return $this->processManager->runAll();
  }
}
