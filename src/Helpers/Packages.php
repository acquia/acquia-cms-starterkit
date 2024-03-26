<?php

namespace AcquiaCMS\Cli\Helpers;

use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;

/**
 * A class for determining package versions.
 */
class Packages implements PackagesInterface {

  /**
   * An object of composer command.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

  /**
   * Stores an array of installed packages.
   */
  protected array $packages;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer
   *   The composer command object.
   */
  public function __construct(Composer $composer) {
    $this->composerCommand = $composer;
    $this->packages = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getInstalledPackages($reset = FALSE): array {
    if ($reset && $this->packages || !$this->packages) {
      $output = $this->composerCommand->prepare([
        "show",
        "--format=json",
      ])->runQuietly([], FALSE);
      $json_output = json_decode($output);
      if (json_last_error() === JSON_ERROR_NONE && $json_output->installed) {
        $this->packages = array_reduce($json_output->installed, function ($carry, $item) {
          $carry[$item->name] = $item;
          return $carry;
        });
      }
    }
    return $this->packages;
  }

}
