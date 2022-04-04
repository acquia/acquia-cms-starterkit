<?php

namespace AcquiaCMS\Cli\Helpers\Composer;

use AcquiaCMS\Cli\Helpers\Process\ProcessFacade;
use Symfony\Component\Console\Style\SymfonyStyle;

class ComposerFacade {
  public function __construct(SymfonyStyle $output, ProcessFacade $process_facade) {
    $this->output = $output;
    $this->processFacade = $process_facade;
  }

  public function setupDrupal() {

  }

  public function isDrupalSetup() {
    $this->runComposer(["config", "extra.drupal-scaffold"]);
  }

  private function runComposer(array $args) {
    array_merge(["composer"], $args);
    $this->processFacade->add($args);
    $this->processFacade->run();
  }
}
