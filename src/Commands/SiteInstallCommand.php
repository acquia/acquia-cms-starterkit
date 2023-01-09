<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code ./vendor/bin/acms site:install
 */
class SiteInstallCommand extends Command {

  use StatusMessageTrait;
  use UserInputTrait;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * Holds Install task.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\InstallTask
   */
  protected $installTask;

  /**
   * The AcquiaCMS installer questions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallerQuestions
   */
  protected $installerQuestions;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\InstallTask $installTask
   *   Provides the Acquia CMS Install task object.
   */
  public function __construct(
    Cli $cli,
    InstallTask $installTask,
    InstallerQuestions $installerQuestions) {
    $this->acquiaCmsCli = $cli;
    $this->installTask = $installTask;
    $this->installerQuestions = $installerQuestions;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("site:install")
      ->setDescription("A wrapper command for drush site:install command.")
      ->setDefinition([
        new InputArgument('profile', InputArgument::IS_ARRAY,
          "An install profile name. Defaults to <info>minimal</info> unless an install profile is marked as a distribution. " . PHP_EOL .
        "Additional info for the install profile may also be provided with additional arguments. The key is in the form [form name].[parameter name]"),
        new InputOption('db-url', '', InputOption::VALUE_OPTIONAL, "A Drupal 6 style database URL. Required for initial install, not re-install. If omitted and required, Drush prompts for this item."),
        new InputOption('db-prefix', '', InputOption::VALUE_OPTIONAL, "An optional table prefix to use for initial install."),
        new InputOption('db-su', '', InputOption::VALUE_OPTIONAL, "Account to use when creating a new database. Must have Grant permission (mysql only). Optional."),
        new InputOption('db-su-pw', '', InputOption::VALUE_OPTIONAL, "Password for the <info>db-su</info> account. Optional."),
        new InputOption('account-name', '', InputOption::VALUE_OPTIONAL, "uid1 name.", 'admin'),
        new InputOption('account-mail', '', InputOption::VALUE_OPTIONAL, "uid1 email.", 'no-reply@example.com'),
        new InputOption('site-mail', '', InputOption::VALUE_OPTIONAL, "<info>From</info>: for system mailings.", 'no-reply@example.com'),
        new InputOption('account-pass', '', InputOption::VALUE_OPTIONAL, "uid1 pass. Defaults to a randomly generated password."),
        new InputOption('locale', '', InputOption::VALUE_OPTIONAL, "A short language code. Sets the default site language. Language files must already be present.", 'en'),
        new InputOption('site-name', '', InputOption::VALUE_OPTIONAL, "Name of the Drupal site.", 'Acquia CMS'),
        new InputOption('site-pass', '', InputOption::VALUE_OPTIONAL),
        new InputOption('sites-subdir', '', InputOption::VALUE_OPTIONAL, "Name of directory under <info>sites</info> which should be created."),
        new InputOption('existing-config ', '', InputOption::VALUE_NONE, "Configuration from <info>sync</info> directory should be imported during installation."),
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
        new InputOption('yes', 'y', InputOption::VALUE_NONE, "Equivalent to --no-interaction."),
        new InputOption('no', '', InputOption::VALUE_NONE, "Cancels at any confirmation prompt."),
        new InputOption('hide-command', 'hide', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
        new InputOption('display-command', 'd', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
      ])
      ->setAliases(['site-install', 'si'])
      ->setHelp("The <info>site:install</info> command install Drupal along with modules/themes/configuration/profile.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    try {
      $args = [];
      $this->acquiaCmsCli->printLogo();
      $this->acquiaCmsCli->printHeadline();
      $site_uri = $input->getOption('uri');
      // Get starterkit name from build file.
      [$starterkit_machine_name, $starterkit_name] = $this->installTask->getStarterKitName($site_uri);
      $args['keys'] = $this->askKeysQuestions($input, $output, $starterkit_machine_name, 'install');
      $this->installTask->configure($input, $output, $starterkit_machine_name, $site_uri);
      $this->installTask->run($args);
      $this->postSiteInstall($starterkit_name, $output);
    }
    catch (AcmsCliException $e) {
      $output->writeln("<error>" . $e->getMessage() . "</error>");
      return StatusCodes::ERROR;
    }
    return StatusCodes::OK;
  }

  /**
   * Providing input to user, asking to provide key.
   */
  protected function askKeysQuestions(InputInterface $input, OutputInterface $output, string $bundle, string $question_type) :array {
    // Get all questions for user selected use-case defined in acms.yml file.
    $questions = $this->installerQuestions->getQuestions($this->acquiaCmsCli->getInstallerQuestions($question_type), $bundle);
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
