<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface;
use AcquiaCMS\Cli\Helpers\FileSystem\FileLoader;
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
 *   weight = 25,
 * )
 */
class DownloadPackagesTask extends BaseTask {

  /**
   * Holds the starter_kit_manager service object.
   *
   * @var \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface
   */
  protected $starterKitManager;

  /**
   * Holds the composer command service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;


  /**
   * Holds the file_loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * The JSON data object.
   *
   * @var array
   */
  protected $rootComposer;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface $starter_kit_manager
   *   The starter_kit_manager service object.
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer_command
   *   The composer command service object.
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   The file_loader service object.
   * @param string $projectDir
   *   The project root directory.
   */
  public function __construct(StarterKitManagerInterface $starter_kit_manager, Composer $composer_command, FileLoader $fileLoader, string $projectDir) {
    $this->starterKitManager = $starter_kit_manager;
    $this->composerCommand = $composer_command;
    $this->rootComposer = $fileLoader->load("$projectDir/composer.json");
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('starter_kit_manager'),
      $container->get('composer_command'),
      $container->get('file_loader'),
      $container->getParameter('app.base_dir')
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
    $selectedStarterKit = $this->starterKitManager->selectedStarterKit();
    $modules = $selectedStarterKit->getModules();
    $themes = $selectedStarterKit->getThemes();

    $packagesToRequire = [];
    if (isset($modules['require'])) {
      $packagesToRequire = array_merge($packagesToRequire, $modules['require']);
    }
    if (isset($themes['require'])) {
      $packagesToRequire = array_merge($packagesToRequire, $themes['require']);
    }
    $packagesToRequire = JsonParser::downloadPackages($packagesToRequire);
    $rootComposer = $this->rootComposer;
    $hasDrush = $rootComposer["require"]["drush/drush"] ?? "";
    $hasMinimStability = $rootComposer["minimum-stability"] ?? "";
    $hasPreferStable = $rootComposer["prefer-stable"] ?? "";
    $hasEnablePatching = $rootComposer["extra"]["enable-patching"] ?? "";
    if (!$hasDrush) {
      $this->composerCommand->prepare([
        "require",
        "drush/drush:^11 || ^12",
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
