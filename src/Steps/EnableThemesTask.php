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
 * Class to enable themes based on user selected starter_kit.
 *
 * @Task(
 *   id = "enable_themes_task",
 *   weight = 45,
 * )
 */
class EnableThemesTask extends BaseTask {

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
    $output->writeln($this->style("Enabling themes for the starter-kit:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $selectedStarterKit = $this->starterKitManager->selectedStarterKit();
    $themes = $selectedStarterKit->getThemes();
    $themesToInstall = $themes['install'] ?? [];
    $adminTheme = $selectedStarterKit->getDefaultTheme();
    $defaultTheme = $selectedStarterKit->getDefaultTheme();
    if ($adminTheme && !in_array($adminTheme, $themesToInstall)) {
      $themesToInstall[] = $adminTheme;
    }
    if ($defaultTheme && !in_array($defaultTheme, $themesToInstall)) {
      $themesToInstall[] = $defaultTheme;
    }
    // Enable themes.
    $command = array_merge(["theme:enable"], [implode(",", $themesToInstall)]);
    $this->drushCommand->prepare($command)->run();

    // Set default and/or admin theme.
    if ($adminTheme) {
      $command = array_merge([
        "config:set",
        "system.theme",
        "admin",
        "--yes",
      ], [$adminTheme]);
      $this->drushCommand->prepare($command)->run();

      // Use admin theme as acquia_claro.
      $command = array_merge([
        "config:set",
        "node.settings",
        "use_admin_theme",
        "--yes",
      ], [TRUE]);
      $this->drushCommand->prepare($command)->run();
    }

    if ($defaultTheme) {
      $command = array_merge([
        "config:set",
        "system.theme",
        "default",
        "--yes",
      ], [$defaultTheme]);
      $this->drushCommand->prepare($command)->run();
    }
    // Add user selected starter-kit to state.
    // @todo Move code to somewhere else.
    $command = array_merge([
      "state:set",
      "acquia_cms.starter_kit",
    ], [$selectedStarterKit->getId()]);
    $this->drushCommand->prepare($command)->run();

    return StatusCode::OK;
  }

}
