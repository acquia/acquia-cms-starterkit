<?php

namespace AcquiaCMS\Cli\Helpers\Process\Commands;

/**
 * A Drush class to execute drush commands.
 */
class Drush extends CommandBase {

  /**
   * {@inheritdoc}
   */
  public function getBaseCommand(): string {
    return 'drush';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommand(array $commands): array {
    $uri = $this->input->getParameterOption('--uri');
    $commands = parent::getCommand($commands);
    if (!empty(shell_exec(sprintf("which %s", escapeshellarg('./vendor/bin/drush'))))) {
      $commands[0] = './vendor/bin/drush';
    }
    if ($uri) {
      $commands = array_unique(array_merge($commands, ['--uri=' . $uri]));
    }
    return $commands;
  }

}
