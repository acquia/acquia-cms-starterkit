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
 * Class to install Drupal site.
 *
 * @Task(
 *   id = "site_install_task",
 *   weight = 4,
 * )
 */
class SiteInstallTask extends BaseTask {

  /**
   * Holds the file-loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * Holds the drush command object.
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
   * Constructs the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\InstallQuestions $questions
   *   An install_questions service object.
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush_command
   *   A drush command object.
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   A file loader service object.
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
    $output->writeln($this->style("Installing Site:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $selected_starter_kit = $this->questions->getAnswer("starter_kit");
    $starter_kits = $this->fileLoader->getStarterKits();
    $name = $starter_kits[$selected_starter_kit]['name'];
    $siteInstallCommand = [
      "site:install",
      "minimal",
      "--site-name=" . $name,
    ];
    if (!$input->isInteractive()) {
      $siteInstallCommand = array_merge($siteInstallCommand, ["--yes"]);
    }
    $this->drushCommand->prepare($siteInstallCommand)->run();
    return StatusCode::OK;
  }

}
