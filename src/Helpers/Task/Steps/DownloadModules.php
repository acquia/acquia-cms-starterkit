<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Cli;
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
        "composer",
        "require",
        "drush/drush:^10.3 || ^11",
      ]);
    }
    if (!isset($composerContents->{'minimum-stability'}) || (isset($composerContents->{'minimum-stability'}) && $composerContents->{'minimum-stability'} != "dev")) {
      $this->processManager->add([
        "composer",
        "config",
        "minimum-stability",
        "dev",
      ]);
    }
    $modulesOrThemes = array_map(function ($moduleOrTheme) {
      return "drupal/$moduleOrTheme";
    }, $args['modules'], $args['themes']);
    $inputArgument = array_merge(["composer", "require", "-W"], $modulesOrThemes);
    $this->processManager->add($inputArgument);
    return $this->processManager->runAll();
  }

}
