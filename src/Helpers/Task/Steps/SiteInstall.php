<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

class SiteInstall {
  public function __construct(ProcessManager $processManager) {
    $this->processManager = $processManager;
  }

  public function execute($args = []) {
    $this->processManager->add(["./vendor/bin/drush", "site:install", "--yes"]);
    return $this->processManager->runAll();
  }
}
