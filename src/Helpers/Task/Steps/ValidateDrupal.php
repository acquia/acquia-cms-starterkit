<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;
use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Provides the class to validate if current project is Drupal project.
 */
class ValidateDrupal {

  /**
   * A composer command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected Composer $composerCommand;

  /**
   * Flag indicating if Drupal is in composer.lock.
   *
   * @var bool
   */
  protected $isInComposer = FALSE;

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected Drush $drushCommand;

  /**
   * Flag indicating if Drupal is installed.
   *
   * @var bool
   */
  protected $isInstalled = FALSE;



  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer
   *   Holds the composer command class object.
   */
  public function __construct(Composer $composer, Drush $drush) {
    $this->composerCommand = $composer;
    $this->drushCommand = $drush;
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
      $this->isInComposer = TRUE;
      $version = implode(', ', $json_output->versions);
    }

    $statusCommand = [
      "status",
      "--format=json",
    ];
    $status_information = $this->drushCommand->prepare($statusCommand)->runQuietly([], FALSE);
    $json_output = json_decode($status_information, TRUE);
    if (json_last_error() === JSON_ERROR_NONE) {
      $this->isInstalled = isset($json_output['bootstrap']) && ($json_output['bootstrap'] == 'Successful');
    }

    return $version;
  }

  /**
   * Indicate if Drupal is available in composer.
   */
  public function isInComposer(): bool {
    return $this->isInComposer;
  }

  /**
   * Indicate if Drupal is installed and bootstrapped.
   */
  public function isInstalled(): bool {
    return $this->isInstalled;
  }

}
