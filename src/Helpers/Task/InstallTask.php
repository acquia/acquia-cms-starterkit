<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall;
use AcquiaCMS\Cli\Helpers\Task\Steps\StatusMessage;
use AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Executes the task needed to run site:install command.
 */
class InstallTask {

  /**
   * Holds the Acquia CMS cli object.
   *
   * @var AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Holds an array of defined starter kits.
   *
   * @var array
   */
  protected $starterKits;

  /**
   * Holds the Validate Drupal step object.
   *
   * @var AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal
   */
  protected $validateDrupal;

  /**
   * Holds the validate Drupal step object.
   *
   * @var AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal
   */
  protected $downloadDrupal;

  /**
   * Holds the Drupal site install step object.
   *
   * @var AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall
   */
  protected $siteInstall;

  /**
   * Holds the enable drupal modules step object.
   *
   * @var AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules
   */
  protected $enableModules;

  /**
   * Holds the status message object.
   *
   * @var AcquiaCMS\Cli\Helpers\Task\Steps\StatusMessage
   */
  protected $statusMessage;

  /**
   * Holds the symfony console command object.
   *
   * @var Symfony\Component\Console\Command\Command
   */
  protected $command;

  /**
   * Holds the symfony console input object.
   *
   * @var Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Holds the symfony console output object.
   *
   * @var Symfony\Component\Console\Input\OutputInterface
   */
  protected $output;

  /**
   * Constructs an object.
   *
   * @param AcquiaCMS\Cli\Cli $cli
   *   An Acquia CMS cli class object.
   * @param AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal $validateDrupal
   *   A Validate Drupal class object.
   * @param AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal $downloadDrupal
   *   Download Drupal class object.
   * @param AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall $siteInstall
   *   A Drupal Site Install class object.
   * @param AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules $enableModules
   *   Enable Drupal modules class object.
   * @param AcquiaCMS\Cli\Helpers\Task\Steps\StatusMessage $statusMessage
   *   Status Message class object.
   */
  public function __construct(Cli $cli, ValidateDrupal $validateDrupal, DownloadDrupal $downloadDrupal, SiteInstall $siteInstall, EnableModules $enableModules, StatusMessage $statusMessage) {
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $validateDrupal;
    $this->downloadDrupal = $downloadDrupal;
    $this->statusMessage = $statusMessage;
    $this->enableModules = $enableModules;
    $this->siteInstall = $siteInstall;
  }

  /**
   * Configures the InstallTask class object.
   *
   * @poram Symfony\Component\Console\Input\InputInterface $input
   *   A Symfony input interface object.
   * @poram Symfony\Component\Console\Input\OutputInterface $output
   *   A Symfony output interface object.
   * @poram Symfony\Component\Console\Command\Command $output
   *   The site:install Symfony console command object.
   */
  public function configure(InputInterface $input, OutputInterface $output, Command $command) {
    $this->command = $command;
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Executes all the steps needed for install task.
   */
  public function run() {
    $this->acquiaCmsCli->printLogo();
    $this->acquiaCmsCli->printHeadline();
    $this->renderStarterKits();
    $bundle = $this->askBundleQuestion();
    if (!$this->validateDrupal->execute()) {
      $this->statusMessage->print("Looks like, current project is not a Drupal project.", StatusMessage::TYPE_WARNING);
      $this->statusMessage->print("Converting the current project to Drupal project.", StatusMessage::TYPE_HEADLINE);
      $this->downloadDrupal->execute();
    }
    else {
      $this->statusMessage->print("Seems Drupal is already downloaded. Skipping downloading Drupal.", StatusMessage::TYPE_SUCCESS);
    }
    $this->statusMessage->print("Installing Site", StatusMessage::TYPE_HEADLINE);
    $this->siteInstall->execute();
    $this->statusMessage->print("Enabling modules for the bundle: `$bundle`.", StatusMessage::TYPE_HEADLINE);
    $this->enableModules->execute($this->starterKits[$bundle]);
  }

  /**
   * Renders the table showing list of all starter kits.
   */
  protected function renderStarterKits() {
    $table = new Table($this->output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    foreach ($this->starterKits as $id => $starter_kit) {
      $useCases[$id] = $starter_kit;
      $table->addRow([$id, $starter_kit['name'], $starter_kit['description']]);
    }
    $table->setStyle('box');
    $table->render();
  }

  /**
   * Providing input to user, asking to select the starter-kit.
   */
  protected function askBundleQuestion() {
    $helper = $this->command->getHelper('question');
    $bundles = array_keys($this->starterKits);
    $question = new Question("Please choose bundle from one of the above use case: <comment>[$bundles[0]]</comment>: ", $bundles[0]);
    $question->setAutocompleterValues($bundles);
    $question->setValidator(function ($answer) use ($bundles) {
      if (!is_string($answer) || !in_array($answer, $bundles)) {
        throw new \RuntimeException(
          "Please choose from one of the use case defined above. Ex: acquia_cms_demo."
        );
      }
      return $answer;
    });
    $question->setMaxAttempts(3);
    return $helper->ask($this->input, $this->output, $question);
  }

}
