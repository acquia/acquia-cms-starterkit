<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Helpers\Process\Commands\Generic;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code
 *   ./vendor/bin/acms acms:install
 * @endcode
 */
class AcmsInstallCommand extends Command {

  use StatusMessageTrait;
  use UserInputTrait;

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Generic
   */
  protected $genericCommand;

  /**
   * User selected bundle.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * Constructs an instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   */
  public function __construct(ContainerInterface $container) {
    $this->genericCommand = $container->get(Generic::class);
    $this->filesystem = $container->get(Filesystem::class);
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition([
        new InputArgument('name', NULL, "Name of the starter kit"),
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
      ])
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    $build_command = $install_command = [];
    $build_command[] = $input->getArgument('name');
    if ($input->getOption('no-interaction')) {
      $install_command[] = '--no-interaction';
    }
    $site_uri = $input->getOption('uri');
    if ($this->filesystem->exists('./vendor/bin/acms')) {
      $this->genericCommand->setCommand('./vendor/bin/acms');
    }
    else {
      $this->genericCommand->setCommand('./bin/acms');
    }
    $install_command = array_merge($install_command, ['--uri=' . $site_uri]);
    $build_command = array_merge($build_command, $install_command);
    $build_command = array_merge(['acms:build'], $build_command);
    $install_command = array_merge([
      'site:install',
      '--without-product-info',
    ], $install_command);

    // Execute acms acms:build.
    $this->genericCommand->prepare($build_command)->run();

    // Execute acms site:install.
    $this->genericCommand->prepare($install_command)->run();
    return StatusCodes::OK;
  }

}
