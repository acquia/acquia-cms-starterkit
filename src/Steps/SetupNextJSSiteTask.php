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
 * Class to setup next.js site, if user chosen yes for next_js question.
 *
 * @Task(
 *   id = "setup_nextjs_task",
 *   weight = 9,
 * )
 */
class SetupNextJSSiteTask extends BaseTask {

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
    $isDemoContent = $this->questions->getAnswer("nextjs_app");
    if ($isDemoContent != "yes") {
      return StatusCode::SKIP;
    }
    $output->writeln($this->style("Initiating NextJs App for the starter-kit:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $site_url = $this->questions->getAnswer("nextjs_app_site_url");
    $site_name = $this->questions->getAnswer("nextjs_app_site_name");
    $env_file = $this->questions->getAnswer("nextjs_app_env_file");
    $command = ["acms:headless:new-nextjs"];
    if ($site_url) {
      $command = array_merge($command, ["--site-url=" . $site_url]);
    }
    if ($site_name) {
      $command = array_merge($command, ["--site-name=" . $site_url]);
    }
    if ($env_file) {
      $command = array_merge($command, ["--env-file=" . $site_url]);
    }
    $this->drushCommand->prepare($command)->run();
    return StatusCode::OK;
  }

}
