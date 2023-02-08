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
 * Class to enable starter module,if user chosen yes for demo_content question.
 *
 * @Task(
 *   id = "enable_starter_module_task",
 *   weight = 8,
 * )
 */
class EnableStarterModuleTask extends BaseTask {

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
    $isDemoContent = $this->questions->getAnswer("demo_content");
    if ($isDemoContent != "yes") {
      return StatusCode::SKIP;
    }
    $output->writeln($this->style("Enabling Starter module for the starter-kit:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $this->drushCommand->prepare(["en", "acquia_cms_starter", "--yes"])->run();
    return StatusCode::OK;
  }

}
