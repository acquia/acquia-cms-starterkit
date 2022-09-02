<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Run the drush command to import site studio packagess.
 */
class InitNextjsApp {

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
   * Run the drush commands to install Drupal.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $initNextjsAppCommand = ["acms:headless:new-nextjs"];
    if (isset($args['--site-url']) && $args['--site-url']) {
      $initNextjsAppCommand = array_merge($initNextjsAppCommand, ["--site-url=" . $args['--site-url']]);
    }
    if (isset($args['--site-name']) && $args['--site-name']) {
      $initNextjsAppCommand = array_merge($initNextjsAppCommand, ["--site-name=" . $args['--site-name']]);
    }
    if (isset($args['--env-file']) && $args['--env-file']) {
      $initNextjsAppCommand = array_merge($initNextjsAppCommand, ["--env-file=" . $args['--env-file']]);
    }

    return $this->drushCommand->prepare($initNextjsAppCommand)->run();
  }

}
