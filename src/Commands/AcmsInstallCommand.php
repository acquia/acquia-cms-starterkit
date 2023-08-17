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
  protected function configure(): void {
    $definitions = [
      new InputArgument('name', NULL, "Name of the starter kit"),
      new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
    ];
    $definitions = array_merge($definitions, $this->getDrushOptions());
    $options = $this->acquiaCmsCli->getOptions();
    foreach ($options as $option => $value) {
      // If default value is there.
      if ($value['default_value']) {
        $definitions[] = new InputOption($option, '', InputOption::VALUE_OPTIONAL, $value['description'], $value['default_value']);
      }
      else {
        $definitions[] = new InputOption($option, '', InputOption::VALUE_OPTIONAL, $value['description']);
      }
    }
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition($definitions)
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $buildCommand = $installCommand = [];
    $siteUri = $input->getOption('uri');
    $starterKitName = $input->getArgument('name');
    // Default install command option and argument.
    $installCommand = $input->getOption('no-interaction') ? [
      '--uri=' . $siteUri,
      '--no-interaction',
    ] : ['--uri=' . $siteUri];
    // Default build command option and argument.
    $buildCommand = $starterKitName ? [
      'acms:build',
      $starterKitName,
    ] : ['acms:build'];

    if ($this->filesystem->exists('./vendor/bin/acms')) {
      $this->genericCommand->setCommand('./vendor/bin/acms');
    }
    else {
      $this->genericCommand->setCommand('./bin/acms');
    }

    // Final build command.
    $buildCommand = array_merge($buildCommand, $installCommand);

    // Final install command.
    $installCommand = array_merge([
      'site:install',
      '--without-product-info',
    ], $installCommand);

    $filterArgs = array_filter($input->getOptions());

    // Get questions arguments/options for build command.
    $buildArgs = $this->acquiaCmsCli->filterOptionsByStarterKit('build', $filterArgs, $starterKitName);
    // Execute acms acms:build.
    $this->genericCommand->prepare(array_merge($buildCommand, $buildArgs))->run();

    // Get questions arguments/options for install command.
    $installArgs = $this->acquiaCmsCli->filterOptionsByStarterKit('install', $filterArgs, $starterKitName);
    // Execute acms site:install.
    $this->genericCommand->prepare(array_merge($installCommand, $installArgs))->run();

    return StatusCodes::OK;
  }

}
