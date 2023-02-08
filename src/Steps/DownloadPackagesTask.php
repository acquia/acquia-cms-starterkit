<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\FileSystem\FileLoader;
use AcquiaCMS\Cli\Helpers\InstallQuestions;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to download modules/themes packages etc.
 *
 * @Task(
 *   id = "download_packages_task",
 *   weight = 3,
 * )
 */
class DownloadPackagesTask extends BaseTask {

  /**
   * Holds the file_loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * Holds the composer command service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

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
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer_command
   *   The composer command service object.
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   The file_loader service object.
   */
  public function __construct(InstallQuestions $questions, Composer $composer_command, FileLoader $fileLoader) {
    $this->fileLoader = $fileLoader;
    $this->composerCommand = $composer_command;
    $this->questions = $questions;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('install_questions'),
      $container->get('composer_command'),
      $container->get('file_loader'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int {
    $output->writeln($this->style("Downloading all packages/modules/themes required by the starter-kit:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $selected_starter_kit = $this->questions->getAnswer("starter_kit");
    $modulesToRequire = $this->fileLoader->getModules($selected_starter_kit);
    $modulesToRequire = $modulesToRequire['require'] ?? [];
    $themesToRequire = $this->fileLoader->getThemes($selected_starter_kit);
    $themesToRequire = $themesToRequire['require'] ?? [];
    $packagesToRequire = [];
    if ($modulesToRequire) {
      $packagesToRequire = JsonParser::downloadPackages(array_merge($modulesToRequire, $themesToRequire));
    }
    $rootComposer = $this->fileLoader->getRootComposer();
    $hasDrush = $rootComposer["require"]["drush/drush"] ?? "";
    $hasMinimStability = $rootComposer["minimum-stability"] ?? "";
    $hasPreferStable = $rootComposer["prefer-stable"] ?? "";
    $hasEnablePatching = $rootComposer["extra"]["enable-patching"] ?? "";
    if (!$hasDrush) {
      $this->composerCommand->prepare([
        "require",
        "drush/drush:^10.3 || ^11",
      ])->run();
    }
    if ($hasMinimStability && $hasMinimStability != "dev") {
      $this->composerCommand->prepare([
        "config",
        "minimum-stability",
        "dev",
      ])->run();
    }
    if ($hasPreferStable !== "" && $hasPreferStable != TRUE) {
      $this->composerCommand->prepare([
        "config",
        "prefer-stable",
        "true",
      ])->run();
    }
    if ($hasEnablePatching !== "" && $hasEnablePatching != TRUE) {
      $this->composerCommand->prepare([
        "config",
        "extra.enable-patching",
        "true",
      ])->run();
    };
    $this->composerCommand->prepare(array_merge(["require", "-W"], $packagesToRequire))->run();
    return StatusCode::OK;
  }

  /**
   * {@inheritdoc}
   */
  public function postExecute(InputInterface $input, OutputInterface $output): int {
    $this->composerCommand->prepare(["update", "--lock"])->run();
    return parent::postExecute($input, $output);
  }

}
