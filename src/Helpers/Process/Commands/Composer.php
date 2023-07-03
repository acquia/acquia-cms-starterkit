<?php

namespace AcquiaCMS\Cli\Helpers\Process\Commands;

/**
 * A Composer class to execute composer commands.
 */
class Composer extends CommandBase {

  /**
   * {@inheritdoc}
   */
  public function getBaseCommand(): string {
    return 'composer';
  }

}
