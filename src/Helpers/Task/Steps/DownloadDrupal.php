<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient;

/**
 * Run the composer command to downlod the latest Drupal.
 */
class DownloadDrupal {

  /**
   * A composer command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

  /**
   * An acquia minimal client object.
   *
   * @var \AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient
   */
  protected $acquiaMinimalClient;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer
   *   Holds the composer command class object.
   * @param \AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient $acquiaMinimalClient
   *   Hold the Acquia Minimal http cliend object.
   */
  public function __construct(Composer $composer, AcquiaMinimalClient $acquiaMinimalClient) {
    $this->composerCommand = $composer;
    $this->acquiaMinimalClient = $acquiaMinimalClient;
  }

  /**
   * Run all the commands needed to download Drupal.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $jsonArray = json_decode($this->acquiaMinimalClient->getFileContents("composer.json"));
    foreach ($jsonArray->repositories as $repoName => $data) {
      $this->composerCommand->prepare([
        "config",
        "--json",
        "repositories.$repoName",
        json_encode($data),
      ])->run();
    }
    foreach ($jsonArray->extra as $key => $data) {
      $this->composerCommand->prepare([
        "config",
        "--json",
        "extra.$key",
        json_encode($data),
      ])->run();
    }
    foreach ($jsonArray->config->{'allow-plugins'} as $plugin => $value) {
      $this->composerCommand->prepare([
        "config",
        "--no-plugins",
        "allow-plugins.$plugin",
        $value,
      ])->run();
    }
    $requirePackages = (array) $jsonArray->require;
    $packages = array_map(function ($key, $value) {
      return $key . ":" . $value;
    }, array_keys($requirePackages), $requirePackages);
    $requireCommand = array_merge(["require", "-W"], $packages);
    return $this->composerCommand->prepare($requireCommand)->run();
  }

}
