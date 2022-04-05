<?php

namespace AcquiaCMS\Cli\Helpers\Process;

/**
 * Factory to provide the ProcessManager class object.
 */
class ProcessFactory {

  /**
   * The ProcessManager object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  private $instance = NULL;

  /**
   * Returns the instance of factory manager class.
   */
  public function getInstance() {
    if (!$this->instance) {
      $this->instance = new ProcessManager($this->output);
    }
    return $this->instance;
  }

}
