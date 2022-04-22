<?php

namespace AcquiaCMS\Cli\Helpers\Process\Commands;

/**
 * A Drush class to execute drush commands.
 */
class Drush extends CommandBase {

  /**
   * {@inheritdoc}
   */
  public function getCommand() :string {
    return './vendor/bin/drush';
  }

}
