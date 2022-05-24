<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;

/**
 * Provides the class to validate if current project is Drupal project.
 */
class ValidateDrupal {

  /**
   * A composer command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer
   *   Holds the composer command class object.
   */
  public function __construct(Composer $composer) {
    $this->composerCommand = $composer;
  }

  /**
   * Run the commands to check if current project is Drupal project.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :string {
    $output = $this->composerCommand->prepare([
      'show',
      'drupal/core',
      '--format=json',
    ])->runQuietly([], FALSE);
    $version = '';
    $json_output = json_decode($output);
    if (json_last_error() === JSON_ERROR_NONE) {
      $version = implode(', ', $json_output->versions);
    }
    return $version;
  }

}
