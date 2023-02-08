<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Tasks\TaskManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code
 *   ./vendor/bin/acms acms:install
 * @endcode
 */
class AcmsInstallCommand extends Command {

  /**
   * The TaskManager service object.
   *
   * @var \AcquiaCMS\Cli\Tasks\TaskManager
   */
  protected $taskManager;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Tasks\TaskManager $task_manager
   *   Provides the AcquiaCMS InstallerQuestions class object.
   */
  public function __construct(TaskManager $task_manager) {
    $this->taskManager = $task_manager;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition([
        new InputArgument('name', NULL, "Name of the starter kit"),
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site."),
      ])
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    try {
      $this->taskManager->configure($input, $output, $this);
      $this->taskManager->executeTasks();
    }
    catch (\Exception $e) {
      $output->writeln("<error>" . $e->getMessage() . "</error>");
      return StatusCode::ERROR;
    }
    return StatusCode::OK;
  }

}
