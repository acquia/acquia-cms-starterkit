<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaRecommendedClient;

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
   * @var \AcquiaCMS\Cli\Http\Client\Github\AcquiaRecommendedClient
   */
  protected $client;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer
   *   Holds the composer command class object.
   * @param \AcquiaCMS\Cli\Http\Client\Github\AcquiaRecommendedClient $client
   *   Hold the Acquia Minimal http cliend object.
   */
  public function __construct(Composer $composer, AcquiaRecommendedClient $client) {
    $this->composerCommand = $composer;
    $this->client = $client;
  }

  /**
   * Run all the commands needed to download Drupal.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $jsonArray = json_decode($this->client->getFileContents("composer.json"));
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
    $requirePackages = array_filter($requirePackages, function ($package) {
      return $package != "acquia/acquia-cms-starterkit";
    }, ARRAY_FILTER_USE_KEY);
    $packages = array_map(function ($key, $value) {
      return $key . ":" . $value;
    }, array_keys($requirePackages), $requirePackages);
    $requireCommand = array_merge(["require", "-W"], $packages);
    return $this->composerCommand->prepare($requireCommand)->run();
  }

}
