<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Exception\ListException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Holds the io service object.
   *
   * @var \AcquiaCMS\Cli\ConsoleIO
   */
  protected $io;

  /**
   * Constructs an instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container service object.
   */
  public function __construct(ContainerInterface $container) {
    parent::__construct();
    $this->io = $container->get('io');
    $this->taskManager = $container->get('task_manager');
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition([
        new InputArgument('name', NULL, "Name of the starter kit"),
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
      ])
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    try {
      $this->io->setInputOutput($input, $output);
      $this->taskManager->configure($this);
      $this->taskManager->executeTasks();
    }
    catch (ListException $e) {
      $e->displayMessage($output);
      return StatusCode::ERROR;
    }
    catch (\Exception $e) {
      $output->writeln($output->getFormatter()->format($e->getMessage()));
      return StatusCode::ERROR;
    }
    return StatusCode::OK;
  }

}
