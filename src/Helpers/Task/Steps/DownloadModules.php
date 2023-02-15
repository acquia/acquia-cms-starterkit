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
    $composerContentsExtra = $composerContents->{'extra'};
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
    if (!isset($composerContentsExtra->{'enable-patching'}) || (isset($composerContentsExtra->{'enable-patching'}) && !$composerContentsExtra->{'enable-patching'})) {
      $this->composerCommand->prepare([
        "config",
        "extra.enable-patching",
        "true",
      ])->run();
    }
    $packages = array_merge($args['modules']['require'], $args['themes']['require']);

    // Update allow plugin section for php-http/discovery. The acquia_cms_place
    // module has indirect dependency to plugin php-http/discovery. So, below
    // check we've added for the acquia_cms_article module, as the module
    // acquia_cms_place is not directly getting installed by an starter-kit.
    $allowedPlugins = (array) $composerContents->config->{'allow-plugins'};
    if (in_array('acquia_cms_article', $args['modules']['require']) && !array_key_exists('php-http/discovery', $allowedPlugins)) {
      $this->composerCommand->prepare([
        "config",
        "--no-plugins",
        "allow-plugins.php-http/discovery",
        "true",
      ])->run();
    }
    // Add nnnick/chartjs, swagger-api/swagger-ui library in installer paths.
    $installerPathsLibrary = $composerContentsExtra->{'installer-paths'}->{'docroot/libraries/{$name}'};
    if (!in_array('nnnick/chartjs', $installerPathsLibrary)) {
      $this->composerCommand->prepare([
        "config",
        'extra.installer-paths.docroot/libraries/{$name}',
        '["nnnick/chartjs"]',
        "--json",
        "--merge",
      ])->run();
    }
    if ($args['name'] == 'Acquia CMS Headless') {
      if (!in_array('swagger-api/swagger-ui', $installerPathsLibrary)) {
        $this->composerCommand->prepare([
          "config",
          'extra.installer-paths.docroot/libraries/{$name}',
          '["swagger-api/swagger-ui"]',
          "--json",
          "--merge",
        ])->run();
      }
    }
    // Add mnsami/composer-custom-directory-installer package.
    if (!array_key_exists('mnsami/composer-custom-directory-installer', $allowedPlugins)) {
      $this->composerCommand->prepare([
        "config",
        "--no-plugins",
        "allow-plugins.mnsami/composer-custom-directory-installer",
        "true",
      ])->run();
    }
    $packages = JsonParser::downloadPackages($packages);
    $inputArgument = array_merge(["require", "-W"], $packages);
    $this->composerCommand->prepare($inputArgument)->run();
    return $this->composerCommand->prepare(["update", "--lock"])->run();
  }

}
