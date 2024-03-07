<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use AcquiaCMS\Cli\Helpers\Task\Steps\AskQuestions;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code
 *   ./vendor/bin/acms site:install
 * @endcode
 */
class SiteInstallCommand extends Command {

  use StatusMessageTrait, UserInputTrait;

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
   * The AskQuestions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\AskQuestions
   */
  protected $askQuestions;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\InstallTask $installTask
   *   Provides the Acquia CMS Install task object.
   * @param \AcquiaCMS\Cli\Helpers\InstallerQuestions $installerQuestions
   *   Provides the AcquiaCMS InstallerQuestions class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\AskQuestions $askQuestions
   *   Provides the AcquiaCMS AskQuestions class object.
   */
  public function __construct(
    Cli $cli,
    InstallTask $installTask,
    InstallerQuestions $installerQuestions,
    AskQuestions $askQuestions) {
    $this->acquiaCmsCli = $cli;
    $this->installTask = $installTask;
    $this->installerQuestions = $installerQuestions;
    $this->askQuestions = $askQuestions;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    // Command Arguments.
    $definitions = [
      new InputArgument('profile', InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
      "An install profile name. Defaults to <info>minimal</info> unless an install profile is marked as a distribution. " . PHP_EOL .
      "Additional info for the install profile may also be provided with additional arguments. The key is in the form [form name].[parameter name]"),
    ];
    // Options of drush and acms install.
    $options = array_merge($this->getDrushOptions(), $this->acquiaCmsCli->getOptions('install'));
    $this->setName("site:install")
      ->setDescription("A wrapper command for drush site:install command.")
      // Prepare command options.
      ->setDefinition(array_merge($definitions, $this->configureOptions($options)))
      ->setAliases(['site-install', 'si'])
      ->setHelp("The <info>site:install</info> command install Drupal along with modules/themes/configuration/profile.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    try {
      $args = [];
      if (!$input->getOption('without-product-info')) {
        $this->acquiaCmsCli->printLogo();
        $this->acquiaCmsCli->printHeadline();
      }
      $siteUri = $input->getOption('uri');
      // Get starterkit name from build file.
      [$starterkitMachineName, $starterkitName] = $this->installTask->getStarterKitName($siteUri);
      // Get user input options for install process.
      $options = array_filter($input->getOptions());
      $envOptions = $this->acquiaCmsCli->envOptions($options, 'install');
      $options = !empty($envOptions) ?
      array_merge($options, $envOptions) : $options;
      $installOptions = $this->getInputOptions($options, 'install');

      $helper = $this->getHelper('question');
      if ($helper instanceof QuestionHelper) {
        $args['keys'] = $this->askQuestions->askKeysQuestions($installOptions, $input, $output, $starterkitMachineName, 'install', $helper);
      }
      $this->installTask->configure($input, $output, $starterkitMachineName, $siteUri);
      $this->installTask->run($args);
      $this->postSiteInstall($starterkitName, $output);
    }
    catch (AcmsCliException $e) {
      $output->writeln("<error>" . $e->getMessage() . "</error>");
      return StatusCodes::ERROR;
    }
    return StatusCodes::OK;
  }

  /**
   * Show successful message post site installation.
   *
   * @param string $bundle
   *   User selected starter-kit.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   A Symfony console output object.
   */
  protected function postSiteInstall(string $bundle, OutputInterface $output): void {
    $output->writeln("");
    $formatter = $this->getHelper('formatter');
    $infoMessage = "[OK] Thank you for choosing Acquia CMS. We've successfully setup your project using bundle: `$bundle`.";
    if ($formatter instanceof FormatterHelper) {
      $formattedInfoBlock = $formatter->formatBlock($infoMessage, 'fg=black;bg=green', TRUE);
      $output->writeln($formattedInfoBlock);
    }
    $output->writeln("");
  }

}
