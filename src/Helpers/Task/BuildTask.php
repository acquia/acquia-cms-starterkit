<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes the task needed to run site:build command.
 */
class BuildTask {

  use StatusMessageTrait;

  /**
   * Holds the Acquia CMS cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Holds an array of defined starter kits.
   *
   * @var mixed
   */
  protected $starterKits;

  /**
   * Holds the Validate Drupal step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal
   */
  protected $validateDrupal;

  /**
   * Holds the validate Drupal step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal
   */
  protected $downloadDrupal;

  /**
   * Holds the symfony console command object.
   *
   * @var \Symfony\Component\Console\Command\Command
   */
  protected $command;

  /**
   * Holds the symfony console input object.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Holds the symfony console output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * Holds the download modules & themes step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules
   */
  protected $downloadModules;

  /**
   * User selected bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Cli $cli
   *   An Acquia CMS cli class object.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   */
  public function __construct(Cli $cli, ContainerInterface $container) {
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $container->get(ValidateDrupal::class);
    $this->downloadDrupal = $container->get(DownloadDrupal::class);
    $this->downloadModules = $container->get(DownloadModules::class);
  }

  /**
   * Configures the BuildTask class object.
   *
   * @poram Symfony\Component\Console\Input\InputInterface $input
   *   A Symfony input interface object.
   * @poram Symfony\Component\Console\Input\OutputInterface $output
   *   A Symfony output interface object.
   * @poram Symfony\Component\Console\Command\Command $output
   *   The site:build Symfony console command object.
   */
  public function configure(InputInterface $input, OutputInterface $output, string $bundle) :void {
    $this->bundle = $bundle;
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Executes all the steps needed for build task.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function run(array $args) :void {
    $installedDrupalVersion = $this->validateDrupal->execute();
    if (!$installedDrupalVersion) {
      $this->print("Looks like, current project is not a Drupal project:", 'warning');
      $this->print("Converting the current project to Drupal project:", 'headline');
      $this->downloadDrupal->execute();
    }
    else {
      $this->print("Seems Drupal is already downloaded. " .
        "The downloaded Drupal core version is: $installedDrupalVersion. " .
        "Skipping downloading Drupal.", 'success'
      );
    }
    $this->print("Downloading all packages/modules/themes required by the starter-kit:", 'headline');
    $this->acquiaCmsCli->alterModulesAndThemes($this->starterKits[$this->bundle], $args['keys']);
    $this->downloadModules->execute($this->starterKits[$this->bundle]);

  }

  /**
   * Create build file in top level directory.
   */
  public function createBuild():void {
    // @todo add logic here.
  }

  /**
   * Function to print message on terminal.
   *
   * @param string $message
   *   Message to style.
   * @param string $type
   *   Type of styling the message.
   */
  protected function print(string $message, string $type) :void {
    $this->output->writeln($this->style($message, $type));
  }

}
