<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient;

/**
 * Run the composer command to downlod the latest Drupal.
 */
class DownloadDrupal {

  /**
   * A process manager object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  protected $processManager;

  /**
   * An acquia minimal client object.
   *
   * @var \AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient
   */
  protected $acquiaMinimalClient;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\ProcessManager $processManager
   *   Hold the process manager class object.
   * @param \AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient $acquiaMinimalClient
   *   Hold the Acquia Minimal http cliend object.
   */
  public function __construct(ProcessManager $processManager, AcquiaMinimalClient $acquiaMinimalClient) {
    $this->processManager = $processManager;
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
      $this->processManager->add([
        "./vendor/bin/composer",
        "config",
        "--json",
        "repositories.$repoName",
        json_encode($data),
      ]);
    }
    foreach ($jsonArray->extra as $key => $data) {
      $this->processManager->add([
        "./vendor/bin/composer",
        "config",
        "--json",
        "extra.$key",
        json_encode($data),
      ]);
    }
    foreach ($jsonArray->config->{'allow-plugins'} as $plugin => $value) {
      $this->processManager->add([
        "./vendor/bin/composer",
        "config",
        "--no-plugins",
        "allow-plugins.$plugin",
        $value,
      ]);
    }
    $requirePackages = (array) $jsonArray->require;
    $packages = array_map(function ($key, $value) {
      return $key . ":" . $value;
    }, array_keys($requirePackages), $requirePackages);
    $requireCommand = array_merge(["./vendor/bin/composer", "require", "-W"], $packages);
    $this->processManager->add($requireCommand);
    return $this->processManager->runAll();
  }

}
