<?php

namespace AcquiaCMS\Cli\Helpers\Process;


use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessFactory {
    // Hold the class instance.
    private ?ProcessFacade $instance = null;

    public function __construct(SymfonyStyle $output) {
      $this->output = $output;
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public function getInstance() {
      if (!$this->instance) {
          $this->instance = new ProcessFacade($this->output);
      }
      return $this->instance;
    }
}