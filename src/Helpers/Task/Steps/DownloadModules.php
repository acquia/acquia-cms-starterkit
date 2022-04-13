<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Process\ProcessManager;

/**
 * Run the drush command to enable Drupal modules.
 */
class DownloadModules {

  /**
   * An process manager object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  protected $processManager;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\ProcessManager $processManager
   *   Hold the process manager class object.
   * @param \AcquiaCMS\Cli\Cli $acquiaCmsCli
   *   Hold an Acquia CMS Cli object.
   */
  public function __construct(ProcessManager $processManager, Cli $acquiaCmsCli) {
    $this->acquiaCmsCli = $acquiaCmsCli;
    $this->processManager = $processManager;
  }

  /**
   * Run the drush commands to download Drupal modules & themes.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $composerContents = $this->acquiaCmsCli->getRootComposer();
    $composerContents = json_decode($composerContents);
    if (!isset($composerContents->require->{'drush/drush'})) {
      $this->processManager->add([
        "./vendor/bin/composer",
        "require",
        "drush/drush:^10.3 || ^11",
      ]);
    }
    if (!isset($composerContents->{'minimum-stability'}) || (isset($composerContents->{'minimum-stability'}) && $composerContents->{'minimum-stability'} != "dev")) {
      $this->processManager->add([
        "./vendor/bin/composer",
        "config",
        "minimum-stability",
        "dev",
      ]);
    }
    if (!isset($composerContents->{'prefer-stable'}) || (isset($composerContents->{'prefer-stable'}) && $composerContents->{'prefer-stable'} != "true")) {
      $this->processManager->add([
        "./vendor/bin/composer",
        "config",
        "prefer-stable",
        "true",
      ]);
    }
    $packages = array_merge($args['modules']['install'], $args['themes']['install']);
    $packages = JsonParser::downloadPackages($packages);
    $inputArgument = array_merge(["./vendor/bin/composer", "require", "-W"], $packages);
    $this->processManager->add($inputArgument);
    return $this->processManager->runAll();
  }

}
