<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

class EnableModules {
  public function __construct(ProcessManager $processManager) {
    $this->processManager = $processManager;
  }

  public function execute($args = []) {
    $inputArgument = array_merge(["./vendor/bin/drush", "en", "--yes"], $args['modules']);
    $this->processManager->add($inputArgument);
    return $this->processManager->runAll();
  }

}
