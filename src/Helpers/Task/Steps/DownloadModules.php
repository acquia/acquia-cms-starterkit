<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;

/**
 * Run the drush command to enable Drupal modules.
 */
class DownloadModules {

  /**
   * A composer command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer
   *   Holds the composer command class object.
   * @param \AcquiaCMS\Cli\Cli $acquiaCmsCli
   *   Hold an Acquia CMS Cli object.
   */
  public function __construct(Composer $composer, Cli $acquiaCmsCli) {
    $this->acquiaCmsCli = $acquiaCmsCli;
    $this->composerCommand = $composer;
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
      $this->composerCommand->prepare([
        "require",
        "drush/drush:^10.3 || ^11",
      ])->run();
    }
    if (!isset($composerContents->{'minimum-stability'}) || (isset($composerContents->{'minimum-stability'}) && $composerContents->{'minimum-stability'} != "dev")) {
      $this->composerCommand->prepare([
        "config",
        "minimum-stability",
        "dev",
      ])->run();
    }
    if (!isset($composerContents->{'prefer-stable'}) || (isset($composerContents->{'prefer-stable'}) && $composerContents->{'prefer-stable'} != "true")) {
      $this->composerCommand->prepare([
        "config",
        "prefer-stable",
        "true",
      ])->run();
    }
    if (!isset($composerContents->{'extra.enable-patching'}) || (isset($composerContents->{'extra.enable-patching'}) && $composerContents->{'extra.enable-patching'} != "true")) {
      $this->composerCommand->prepare([
        "config",
        "extra.enable-patching",
        "true",
      ])->run();
    }
    $packages = array_merge($args['modules']['require'], $args['themes']['require']);
    $packages = JsonParser::downloadPackages($packages);
    $inputArgument = array_merge(["require", "-W"], $packages);
    return $this->composerCommand->prepare($inputArgument)->run();
  }

}
