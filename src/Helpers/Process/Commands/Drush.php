<?php

namespace AcquiaCMS\Cli\Helpers\Process\Commands;

/**
 * A Drush class to execute drush commands.
 */
class Drush extends CommandBase {

  /**
   * {@inheritdoc}
   */
  public function getBaseCommand() :string {
    return './vendor/bin/drush';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommand(array $commands) : array {
    $uri = $this->input->getParameterOption('--uri');
    $commands = parent::getCommand($commands);
    if ($uri) {
      $commands = array_merge($commands, ['--uri=' . $uri]);
    }
    return $commands;
  }

}
