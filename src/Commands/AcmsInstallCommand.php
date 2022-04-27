<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code ./vendor/bin/acms acms:install
 */
class AcmsInstallCommand extends Command {

  use StatusMessageTrait;

  /**
   * The AcquiaCMS InstallTask object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\InstallTask
   */
  protected $installTask;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Holds the symfony console output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * The AcquiaCMS installer questions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallerQuestions
   */
  protected $installerQuestions;

  /**
   * Constructs an instance.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Output contains the string to be displayed.
   * @param \AcquiaCMS\Cli\Helpers\Task\InstallTask $installTask
   *   Provides the Acquia CMS Install task object.
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   * @param \AcquiaCMS\Cli\Helpers\InstallerQuestions $installerQuestions
   *   Provides the AcquiaCMS InstallerQuestions class object.
   */
  public function __construct(
    OutputInterface $output,
    InstallTask $installTask,
    Cli $cli,
    InstallerQuestions $installerQuestions) {
    $this->acquiaCmsCli = $cli;
    $this->installTask = $installTask;
    $this->output = $output;
    $this->installerQuestions = $installerQuestions;
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
      ])
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    try {
      $name = $input->getArgument('name');
      $args = [];
      if ($name) {
        $this->validationOptions($name);
        $this->acquiaCmsCli->printLogo();
        $this->acquiaCmsCli->printHeadline();
      }
      else {
        $this->acquiaCmsCli->printLogo();
        $this->acquiaCmsCli->printHeadline();
        $name = $this->askBundleQuestion($input, $output);
        $args['keys'] = $this->askKeysQuestions($input, $output, $name);
      }
      $this->installTask->configure($input, $output, $name);
      $this->installTask->run($args);
      $this->postSiteInstall($name, $output);
    }
    catch (AcmsCliException $e) {
      $output->writeln("<error>" . $e->getMessage() . "</error>");
      return StatusCodes::ERROR;
    }
    return StatusCodes::OK;
  }

  /**
   * Validate all input options/arguments.
   *
   * @param string $name
   *   A name of the user selected use-case.
   */
  protected function validationOptions(string $name) :bool {
    $starterKits = array_keys($this->acquiaCmsCli->getStarterKits());
    if (!in_array($name, $starterKits)) {
      throw new InvalidArgumentException("Invalid starter kit. It should be from one of the following: " . implode(", ", $starterKits) . ".");
    }
    return TRUE;
  }

  /**
   * Providing input to user, asking to select the starter-kit.
   */
  protected function askBundleQuestion(InputInterface $input, OutputInterface $output) :string {
    $helper = $this->getHelper('question');
    $bundles = array_keys($this->acquiaCmsCli->getStarterKits());
    $this->renderStarterKits($output);
    $starterKit = "acquia_cms_minimal";
    $question = new Question("Please choose bundle from one of the above use case: <comment>[$starterKit]</comment>: ", $starterKit);
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
    return $helper->ask($input, $output, $question);
  }

  /**
   * Providing input to user, asking to provide key.
   */
  protected function askKeysQuestions(InputInterface $input, OutputInterface $output, string $bundle) :array {
    $askKeys = [];
    $helper = $this->getHelper('question');
    // The questions defined in acms.yml file.
    $bundleQuestions = $this->acquiaCmsCli->getInstallerQuestions();
    // Get all questions for user selected use-case.
    $allQuestions = $this->installerQuestions->getQuestionForBundle($bundleQuestions, $bundle);
    // Filter questions, only ask question if variable key is not available.
    $filteredQuestions = $this->installerQuestions->filterQuestionForBundle($allQuestions);
    // Style questions.
    $questionsToAsk = $this->installerQuestions->styleQuestionForBundle($filteredQuestions);
    if ($questionsToAsk) {
      $this->output->writeln($this->style("Please provide the required API/Token keys for installation: ", 'headline'));
      $this->output->writeln($this->style("Required Keys are denoted by a (*) ", 'warning'));
      foreach ($questionsToAsk as $key => $question) {
        $askQuestion = new Question($question['question'] . ' : ');
        $askQuestion->setValidator(function ($answer) use ($question) {
          if (!is_string($answer) && $question['required']) {
            throw new \RuntimeException(
              "Key cannot be empty."
            );
          }
          return $answer;
        });
        // Max attempts for getting keys.
        $askQuestion->setMaxAttempts(3);
        $askKeys[$key] = $helper->ask($input, $output, $askQuestion);
      }
    }
    // Return variable key-value pair.
    return $this->installerQuestions->getKeyPair($bundleQuestions, $bundle, $askKeys);
  }

  /**
   * Renders the table showing list of all starter kits.
   */
  protected function renderStarterKits(OutputInterface $output) :void {
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    foreach ($this->acquiaCmsCli->getStarterKits() as $id => $starter_kit) {
      $useCases[$id] = $starter_kit;
      $table->addRow([$id, $starter_kit['name'], $starter_kit['description']]);
    }
    $table->setStyle('box');
    $table->render();
  }

  /**
   * Show successful message post site installation.
   *
   * @param string $bundle
   *   User selected starter-kit.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   A Symfony console output object.
   */
  protected function postSiteInstall(string $bundle, OutputInterface $output) :void {
    $output->writeln("");
    $formatter = $this->getHelper('formatter');
    $infoMessage = "[OK] Thank you for choosing Acquia CMS. We've successfully setup your project using bundle: `$bundle`.";
    $formattedInfoBlock = $formatter->formatBlock($infoMessage, 'fg=black;bg=green', TRUE);
    $output->writeln($formattedInfoBlock);
    $output->writeln("");
  }

}
