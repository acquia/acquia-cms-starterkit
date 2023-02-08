<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\FileSystem\FileLoader;
use AcquiaCMS\Cli\Helpers\InstallQuestions;
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
 *   weight = 6,
 * )
 */
class EnableThemesTask extends BaseTask {

  /**
   * Holds the file_loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * Holds the composer command service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected $drushCommand;

  /**
   * Holds the install_questions service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallQuestions
   */
  protected $questions;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\InstallQuestions $questions
   *   The install_questions service object.
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush_command
   *   The composer command service object.
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   The file_loader service object.
   */
  public function __construct(InstallQuestions $questions, Drush $drush_command, FileLoader $fileLoader) {
    $this->fileLoader = $fileLoader;
    $this->drushCommand = $drush_command;
    $this->questions = $questions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('install_questions'),
      $container->get('drush_command'),
      $container->get('file_loader'),
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
    $selected_starter_kit = $this->questions->getAnswer("starter_kit");
    $themes = $this->fileLoader->getThemes($selected_starter_kit);
    $themesToInstall = $themes['install'] ?? [];
    if (isset($themes['admin'])) {
      $themesToInstall[] = $themes['admin'];
    }
    if (isset($themes['default'])) {
      $themesToInstall[] = $themes['default'];
    }
    // Enable themes.
    $command = array_merge(["theme:enable"], [implode(",", $themesToInstall)]);
    $this->drushCommand->prepare($command)->run();

    // Set default and/or admin theme.
    if (isset($themes['admin'])) {
      $command = array_merge([
        "config:set",
        "system.theme",
        "admin",
        "--yes",
      ], [$themes['admin']]);
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

    if (isset($themes['default'])) {
      $command = array_merge([
        "config:set",
        "system.theme",
        "default",
        "--yes",
      ], [$themes['default']]);
      $this->drushCommand->prepare($command)->run();
    }
    // Add user selected starter-kit to state.
    // @todo Move code to somewhere else.
    $command = array_merge([
      "state:set",
      "acquia_cms.starter_kit",
    ], [$selected_starter_kit]);
    $this->drushCommand->prepare($command)->run();

    return StatusCode::OK;
  }

}
