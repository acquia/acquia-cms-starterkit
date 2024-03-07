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
    // Command Arguments.
    $definitions = [
      new InputArgument('name', InputArgument::OPTIONAL, "Name of the starter kit"),
      new InputArgument('profile', InputArgument::IS_ARRAY,
        "An install profile name. Defaults to <info>minimal</info> unless an install profile is marked as a distribution. " . PHP_EOL .
      "Additional info for the install profile may also be provided with additional arguments. The key is in the form [form name].[parameter name]"),
    ];
    // Options of drush and acms install + build.
    $options = array_merge($this->getDrushOptions(), $this->acquiaCmsCli->getOptions());
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      // Prepare command options.
      ->setDefinition(array_merge($definitions, $this->configureOptions($options)))
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
    $installCommand = ($input->getOption('no-interaction') || $input->getOption('yes')) ? [
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
    $envOptions = $this->acquiaCmsCli->envOptions($filterArgs);
    $filterArgs = !empty($envOptions) ?
    array_merge($filterArgs, $envOptions) : $filterArgs;

    // Get questions arguments/options for build command.
    $buildArgs = $this->acquiaCmsCli->filterOptionsByStarterKit('build', $filterArgs, $starterKitName);
    // Execute acms acms:build.
    $this->genericCommand->prepare(array_merge($buildCommand, $buildArgs))->run();
    // Get build information if starterkit set from the prompt.
    $buildInfo = $this->acquiaCmsCli->getBuildInformtaion($siteUri);
    $starterKitName = $starterKitName ?? $buildInfo['starter_kit'];
    // Get questions arguments/options for install command.
    $installArgs = $this->acquiaCmsCli->filterOptionsByStarterKit('install', $filterArgs, $starterKitName);
    // Execute acms site:install.
    $this->genericCommand->prepare(array_merge($installCommand, $installArgs))->run();

    return StatusCodes::OK;
  }

}
