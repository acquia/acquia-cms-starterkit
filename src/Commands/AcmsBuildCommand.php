<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Task\BuildTask;
use AcquiaCMS\Cli\Helpers\Task\Steps\AskQuestions;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Provides the Acquia CMS site:build command.
 *
 * @code
 * ./vendor/bin/acms acms:build
 * @endcode
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
   * The AskQuestions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\AskQuestions
   */
  protected $askQuestions;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Helpers\Task\BuildTask $buildTask
   *   Provides the Acquia CMS Build task object.
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   * @param \AcquiaCMS\Cli\Helpers\InstallerQuestions $installerQuestions
   *   Provides the AcquiaCMS InstallerQuestions class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\AskQuestions $askQuestions
   *   Provides the AcquiaCMS AskQuestions class object.
   */
  public function __construct(
    BuildTask $buildTask,
    Cli $cli,
    InstallerQuestions $installerQuestions,
    AskQuestions $askQuestions) {
    $this->acquiaCmsCli = $cli;
    $this->buildTask = $buildTask;
    $this->installerQuestions = $installerQuestions;
    $this->askQuestions = $askQuestions;
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
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
        new InputOption('generate', 'ge', InputOption::VALUE_NONE, "Create build.yml file without running composer install/require."),
      ])
      ->setHelp("The <info>acms:build</info> command to build composer dependencies & downloads it based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    try {
      $name = $input->getArgument('name');
      $generate = $input->getOption('generate');
      $site_uri = $input->getOption('uri');
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
      $helper = $this->getHelper('question');
      if ($helper instanceof QuestionHelper) {
        $args['keys'] = $this->askQuestions->askKeysQuestions($input, $output, $name, 'build', $helper);
      }
      $this->buildTask->configure($input, $output, $name);
      if (!$generate) {
        $this->buildTask->run($args);
      }
      $this->buildTask->createBuild($args, $site_uri);
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
  protected function validationOptions(string $name): bool {
    $starterKits = array_keys($this->acquiaCmsCli->getStarterKits());
    if (!in_array($name, $starterKits)) {
      throw new InvalidArgumentException("Invalid starter kit. It should be from one of the following: " . implode(", ", $starterKits) . ".");
    }
    return TRUE;
  }

  /**
   * Providing input to user, asking to select the starter-kit.
   */
  protected function askBundleQuestion(InputInterface $input, OutputInterface $output): string {
    /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
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
    if ($helper instanceof QuestionHelper) {
      return $helper->ask($input, $output, $question);
    }
  }

  /**
   * Renders the table showing list of all starter kits.
   */
  protected function renderStarterKits(OutputInterface $output): void {
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    $starter_kits = $this->acquiaCmsCli->getStarterKits();
    $total = count($starter_kits);
    $key = 0;
    foreach ($starter_kits as $id => $starter_kit) {
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
  protected function postBuild(string $bundle, OutputInterface $output): void {
    $output->writeln("");
    /** @var \Symfony\Component\Console\Helper\FormatterHelper $formatter */
    $formatter = $this->getHelper('formatter');
    $infoMessage = "[OK] Thank you for choosing Acquia CMS. We've successfully built composer dependencies using the bundle: `$bundle`.";
    if ($formatter instanceof FormatterHelper) {
      $formattedInfoBlock = $formatter->formatBlock($infoMessage, 'fg=black;bg=green', TRUE);
      $output->writeln($formattedInfoBlock);
    }
    $output->writeln("");
  }

}
