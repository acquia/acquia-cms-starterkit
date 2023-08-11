<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
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

  use StatusMessageTrait, UserInputTrait;

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
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Constructs an instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   */
  public function __construct(ContainerInterface $container, Cli $cli) {
    $this->genericCommand = $container->get(Generic::class);
    $this->filesystem = $container->get(Filesystem::class);
    $this->acquiaCmsCli = $cli;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $defaultDefinitions = [
      new InputArgument('name', NULL, "Name of the starter kit"),
      new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
    ];
    $options = $this->acquiaCmsCli->getOptions('both');
    $quOptions = [];
    foreach ($options as $option => $value) {
      // If default value is there.
      if ($value['default_value']) {
        $quOptions[] = new InputOption('enable-' . $option, '', InputOption::VALUE_OPTIONAL, $value['description'], $value['default_value']);
      }
      else {
        $quOptions[] = new InputOption($option, '', InputOption::VALUE_OPTIONAL, $value['description']);
      }
    }
    $finalDefinition = array_merge($defaultDefinitions, $quOptions);
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition($finalDefinition)
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
