<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient;

class DownloadDrupal {
  protected $message = "Converting current project to Drupal project.";
  public function __construct(ProcessManager $processManager, AcquiaMinimalClient $acquiaMinimalClient) {
    $this->processManager = $processManager;
    $this->acquiaMinimalClient = $acquiaMinimalClient;
  }

  public function execute($args = []) {
    $jsonArray = json_decode($this->acquiaMinimalClient->getFileContents("composer.json"));
    foreach($jsonArray->repositories as $repoName => $data) {
      $this->processManager->add(["composer", "config", "--json", "repositories.$repoName", json_encode($data)]);
    }
    foreach($jsonArray->extra as $key => $data) {
      $this->processManager->add(["composer", "config", "--json", "extra.$key", json_encode($data)]);
    }
    foreach($jsonArray->config->{'allow-plugins'} as $plugin => $value) {
      $this->processManager->add(["composer", "config", "--no-plugins", "allow-plugins.$plugin", $value]);
    }
    $requirePackages = (array) $jsonArray->require;
    $packages = array_map(function ($key, $value) {
      return $key . ":" . $value;
    }, array_keys($requirePackages), $requirePackages);
    $requireCommand = array_merge(["composer", "require", "-W"], $packages);
    $this->processManager->add($requireCommand);
    return $this->processManager->runAll();
  }
}
