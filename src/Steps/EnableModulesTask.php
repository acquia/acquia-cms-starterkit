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
 * Class to enable modules based on user selected starter_kit.
 *
 * @Task(
 *   id = "enable_modules_task",
 *   weight = 40,
 * )
 */
class EnableModulesTask extends BaseTask {

  /**
   * Holds the starter_kit_manager service object.
   *
   * @var \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface
   */
  protected $starterKitManager;

  /**
   * Holds the drush command service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected $drushCommand;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush_command
   *   The composer command service object.
   * @param \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface $starter_kit_manager
   *   The starter_kit_manager service object.
   */
  public function __construct(Drush $drush_command, StarterKitManagerInterface $starter_kit_manager) {
    $this->starterKitManager = $starter_kit_manager;
    $this->drushCommand = $drush_command;
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
    $output->writeln($this->style("Enabling modules for the starter-kit:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $selectedStarterKit = $this->starterKitManager->selectedStarterKit();
    $modulesToInstall = $selectedStarterKit->getModules();
    $modulesToInstall = $modulesToInstall['install'] ?? [];
    $modulesToInstall = array_filter($modulesToInstall, function ($module) {
      return $module !== 'acquia_cms_starter';
    });
    $this->drushCommand->prepare(array_merge(["en", "--yes"], $modulesToInstall))->run();
    return StatusCode::OK;
  }

}
