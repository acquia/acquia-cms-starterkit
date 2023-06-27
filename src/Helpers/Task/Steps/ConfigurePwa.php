<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Run the drush command to import site studio packagess.
 */
class ConfigurePwa {

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected $drushCommand;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container) {
    $this->drushCommand = $container->get(Drush::class);
  }

  /**
   * Run the drush commands to configure PWA.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args): void {
    $pwaSiteName = $args['keys']['pwa_site_name'] ?? '';
    $pwaShortName = $args['keys']['pwa_short_name'] ?? '';
    // Set PWA site application name.
    $command = [
      "config:set",
      "pwa.config",
      "site_name",
      $pwaSiteName,
      "--yes",
    ];
    $this->drushCommand->prepare($command)->run();

    // Set PWA site application short name.
    $command = [
      "config:set",
      "pwa.config",
      "short_name",
      $pwaShortName,
      "--yes",
    ];
    $this->drushCommand->prepare($command)->run();

    // Set PWA site application short name.
    $command = [
      "config:set",
      "pwa.config",
      "theme_color",
      "#26a3dd",
      "--yes",
    ];
    $this->drushCommand->prepare($command)->run();

    // Set PWA site application short name.
    $command = [
      "config:set",
      "pwa.config",
      "background_color",
      "#000000",
      "--yes",
    ];
    $this->drushCommand->prepare($command)->run();

    // Set PWA site application short name.
    $command = [
      "config:set",
      "pwa.config",
      "display",
      "fullscreen",
      "--yes",
    ];
    $this->drushCommand->prepare($command)->run();
  }

}
