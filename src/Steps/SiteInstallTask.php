<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface;
use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to install Drupal site.
 *
 * @Task(
 *   id = "site_install_task",
 *   weight = 35,
 * )
 */
class SiteInstallTask extends BaseTask {

  /**
   * Holds the starter_kit_manager service object.
   *
   * @var \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface
   */
  protected $starterKitManager;

  /**
   * Holds the drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected $drushCommand;

  /**
   * Constructs the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush_command
   *   A drush command object.
   * @param \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface $starter_kit_manager
   *   The starter_kit_manager service object.
   */
  public function __construct(Drush $drush_command, StarterKitManagerInterface $starter_kit_manager) {
    $this->drushCommand = $drush_command;
    $this->starterKitManager = $starter_kit_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('drush_command'),
      $container->get('starter_kit_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int {
    $output->writeln($this->style("Installing Site:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $selectedStarterKit = $this->starterKitManager->selectedStarterKit();
    $siteInstallCommand = [
      "site:install",
      "minimal",
      "--site-name=" . $selectedStarterKit->getName(),
    ];
    if (!$input->isInteractive()) {
      $siteInstallCommand = array_merge($siteInstallCommand, ["--yes"]);
    }
    $this->drushCommand->prepare($siteInstallCommand)->run();
    return StatusCode::OK;
  }

}
