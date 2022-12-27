<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Task\BuildTask;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Provides the Acquia CMS site:build command.
 *
 * @code ./vendor/bin/acms acms:build
 */
class AcmsBuildCommand extends Command {

  use StatusMessageTrait;
  use UserInputTrait;
  /**
   * The AcquiaCMS BuildTask object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\BuildTask
   */
  protected $buildTask;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * The AcquiaCMS installer questions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallerQuestions
   */
  protected $installerQuestions;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Helpers\Task\BuildTask $buildTask
   *   Provides the Acquia CMS Build task object.
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   * @param \AcquiaCMS\Cli\Helpers\InstallerQuestions $installerQuestions
   *   Provides the AcquiaCMS InstallerQuestions class object.
   */
  public function __construct(BuildTask $buildTask, Cli $cli, InstallerQuestions $installerQuestions) {
    $this->acquiaCmsCli = $cli;
    $this->buildTask = $buildTask;
    $this->installerQuestions = $installerQuestions;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("acms:build")
      ->setDescription("Use this command to build composer dependencies.")
      ->setDefinition([
        new InputArgument('name', NULL, "Name of the starter kit"),
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site."),
        new InputOption('generate', 'ge', InputOption::VALUE_OPTIONAL, "Create build.yml file without running composer install/require."),
      ])
      ->setHelp("The <info>acms:build</info> command to build composer dependencies & downloads it based on user selected use case.");
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
      }
      $args['keys'] = $this->askKeysQuestions($input, $output, $name);
      $this->buildTask->configure($input, $output, $name);
      $this->buildTask->run($args);
      $this->buildTask->createBuild();
      $this->postBuild($name, $output);
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
    $starterKit = "acquia_cms_enterprise_low_code";
    $question = new Question($this->styleQuestion("Please choose bundle from one of the above use case", $starterKit), $starterKit);
    $question->setAutocompleterValues($bundles);
    $question->setValidator(function ($answer) use ($bundles) {
      if (!is_string($answer) || !in_array($answer, $bundles)) {
        throw new \RuntimeException(
          "Please choose from one of the use case defined above. Ex: acquia_cms_enterprise_low_code."
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
    // Get all questions for user selected use-case defined in acms.yml file.
    $questions = $this->installerQuestions->getQuestions($this->acquiaCmsCli->getInstallerQuestions(), $bundle);
    $processedQuestions = $this->installerQuestions->process($questions);

    // Initialize the value with default answer for question, so that
    // if any question is dependent on other question which is skipped,
    // we can use the value for that question to make sure the cli
    // doesn't throw following RunTime exception:"Not able to resolve variable".
    // @see AcquiaCMS\Cli\Helpers::shouldAskQuestion().
    $userInputValues = $processedQuestions['default'];
    foreach ($questions as $key => $question) {
      $envVar = $this->installerQuestions->getEnvValue($question, $key);
      if (empty($envVar)) {
        if ($this->installerQuestions->shouldAskQuestion($question, $userInputValues)) {
          $userInputValues[$key] = $this->askQuestion($question, $key, $input, $output);
        }
      }
      else {
        $userInputValues[$key] = $envVar;
      }
    }

    return array_merge($processedQuestions['default'], $userInputValues);
  }

  /**
   * Renders the table showing list of all starter kits.
   */
  protected function renderStarterKits(OutputInterface $output) :void {
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    $starter_kits = $this->acquiaCmsCli->getStarterKits();
    $total = count($starter_kits);
    $key = 0;
    foreach ($starter_kits as $id => $starter_kit) {
      $useCases[$id] = $starter_kit;
      $table->addRow([$id, $starter_kit['name'], $starter_kit['description']]);
      if ($key + 1 != $total) {
        $table->addRow(["", "", ""]);
      }
      $key++;
    }
    $table->setColumnMaxWidth(2, 81);
    $table->setStyle('box');
    $table->render();
  }

  /**
   * Show successful message post build command.
   *
   * @param string $bundle
   *   User selected starter-kit.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   A Symfony console output object.
   */
  protected function postBuild(string $bundle, OutputInterface $output) :void {
    $output->writeln("");
    $formatter = $this->getHelper('formatter');
    $infoMessage = "[OK] Thank you for choosing Acquia CMS. We've successfully built composer dependencies using the bundle: `$bundle`.";
    $formattedInfoBlock = $formatter->formatBlock($infoMessage, 'fg=black;bg=green', TRUE);
    $output->writeln($formattedInfoBlock);
    $output->writeln("");
  }

  /**
   * Function to ask question to user.
   *
   * @param array $question
   *   An array of question.
   * @param string $key
   *   A unique key for question.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   A Console input interface object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   A Console output interface object.
   */
  public function askQuestion(array $question, string $key, InputInterface $input, OutputInterface $output) : string {
    $helper = $this->getHelper('question');
    $isRequired = $question['required'] ?? FALSE;
    $defaultValue = $this->installerQuestions->getDefaultValue($question, $key);
    $skipOnValue = $question['skip_on_value'] ?? TRUE;
    if ($skipOnValue && $defaultValue) {
      return $defaultValue;
    }
    $askQuestion = new Question($this->styleQuestion($question['question'], $defaultValue, $isRequired, TRUE));
    $askQuestion->setValidator(function ($answer) use ($question, $key, $isRequired, $output, $defaultValue) {
      if (!is_string($answer) && !$defaultValue) {
        if ($isRequired) {
          throw new \RuntimeException(
            "The `" . $key . "` cannot be left empty."
          );
        }
        else {
          if (isset($question['warning'])) {
            $warning = str_replace(PHP_EOL, PHP_EOL . " ", $question['warning']);
            $output->writeln($this->style(" " . $warning, 'warning', FALSE));
          }
        }
      }
      if ($answer && isset($question['allowed_values']['options']) && !in_array($answer, $question['allowed_values']['options'])) {
        throw new \RuntimeException(
          "Invalid value. It should be from one of the following: " . implode(", ", $question['allowed_values']['options'])
        );
      }
      return $answer ?: $defaultValue;
    });
    $askQuestion->setMaxAttempts(3);
    if (isset($question['allowed_values']['options'])) {
      $askQuestion->setAutocompleterValues($question['allowed_values']['options']);
    }
    $response = $helper->ask($input, $output, $askQuestion);
    return ($response === NULL) ? $defaultValue : $response;
  }

}
