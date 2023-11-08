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
 * Class to setup next.js site, if user chosen yes for next_js question.
 *
 * @Task(
 *   id = "setup_nextjs_task",
 *   weight = 60,
 * )
 */
class SetupNextJSSiteTask extends BaseTask {

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
    $installUserResponse = $this->starterKitManager->getAnswers("install");
    $isNextJsApp = $installUserResponse["nextjs-app"] ?? "";
    if ($isNextJsApp != "yes") {
      return StatusCode::SKIP;
    }
    $output->writeln($this->style("Initiating NextJs App for the starter-kit:", 'headline'));
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $installUserResponse = $this->starterKitManager->getAnswers("install");
    $site_url = $installUserResponse["nextjs-app-site-url"] ?? "";
    $site_name = $installUserResponse["nextjs-app-site-name"] ?? "";
    $env_file = $installUserResponse["nextjs-app-env-file"] ?? "";
    $command = ["acms:headless:new-nextjs"];
    if ($site_url) {
      $command = array_merge($command, ["--site-url=" . $site_url]);
    }
    if ($site_name) {
      $command = array_merge($command, ["--site-name=" . $site_name]);
    }
    if ($env_file) {
      $command = array_merge($command, ["--env-file=" . $env_file]);
    }
    $this->drushCommand->prepare($command)->run();
    return StatusCode::OK;
  }

}
