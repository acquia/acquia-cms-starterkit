<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableThemes;
use AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall;
use AcquiaCMS\Cli\Helpers\Task\Steps\SiteStudioPackageImport;
use AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes the task needed to run site:install command.
 */
class InstallTask {

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
   * Holds the Drupal site install step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall
   */
  protected $siteInstall;

  /**
   * Holds the enable drupal modules step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules
   */
  protected $enableModules;

  /**
   * Holds the enable drupal modules step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\EnableThemes
   */
  protected $enableThemes;

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
   * The site studio package import step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\SiteStudioPackageImport
   */
  protected $siteStudioPackageImport;

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
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal $validateDrupal
   *   A Validate Drupal class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal $downloadDrupal
   *   Download Drupal class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules $downloadModules
   *   Download Modules & themes class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall $siteInstall
   *   A Drupal Site Install class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules $enableModules
   *   Enable Drupal modules class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\EnableThemes $enableThemes
   *   Enable Drupal modules class object.
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\SiteStudioPackageImport $siteStudioPackageImport
   *   The site studio package import step object.
   */
  public function __construct(Cli $cli, ValidateDrupal $validateDrupal, DownloadDrupal $downloadDrupal, DownloadModules $downloadModules, SiteInstall $siteInstall, EnableModules $enableModules, EnableThemes $enableThemes, SiteStudioPackageImport $siteStudioPackageImport) {
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $validateDrupal;
    $this->downloadDrupal = $downloadDrupal;
    $this->enableModules = $enableModules;
    $this->enableThemes = $enableThemes;
    $this->siteInstall = $siteInstall;
    $this->downloadModules = $downloadModules;
    $this->siteStudioPackageImport = $siteStudioPackageImport;
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
  public function configure(InputInterface $input, OutputInterface $output, string $bundle) :void {
    $this->bundle = $bundle;
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Executes all the steps needed for install task.
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

    // @todo Think if we can configure to download/install modules/themes using yaml configuration.
    $this->alterModulesAndThemes($this->starterKits[$this->bundle], $args['keys']);

    $this->downloadModules->execute($this->starterKits[$this->bundle]);
    $this->print("Installing Site:", 'headline');
    $this->siteInstall->execute([
      'no-interaction' => $this->input->getOption('no-interaction'),
    ]);
    $this->print("Enabling modules for the starter-kit:", 'headline');
    $this->enableModules->execute([
      'modules' => $this->starterKits[$this->bundle]['modules'],
      'keys' => $args['keys'],
    ]);
    $this->print("Enabling themes for the starter-kit:", 'headline');
    $this->enableThemes->execute([
      'themes' => $this->starterKits[$this->bundle]['themes'],
      'starter_kit' => $this->bundle,
    ]);
    // Trigger site studio import process if starter or
    // page module is there in active bundle.
    $modules_ss_import = [
      'acquia_cms_page',
      'acquia_cms_starter',
      'acquia_cms_site_studio',
    ];
    $bundle_modules = $this->starterKits[$this->bundle]['modules']['install'] ?? [];
    $modules_list = JsonParser::installPackages($bundle_modules);
    if (array_intersect($modules_ss_import, $modules_list)) {
      $this->print("Running site studio package import for starter-kit:", 'headline');
      $this->siteStudioPackageImport->execute([
        'no-interaction' => $this->input->getOption('no-interaction'),
      ]);
    }
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

  /**
   * Function to alter modules & themes based on user response.
   *
   * @param array $starterKit
   *   An array of starter-kit.
   * @param array $userInputValues
   *   A user input values for questions.
   *
   * @return array
   *   Returns an array of altered starter-kit.
   */
  public function alterModulesAndThemes(array &$starterKit, array $userInputValues) :array {
    $isContentModel = $userInputValues['content_model'] ?? '';
    $isDemoContent = $userInputValues['demo_content'] ?? '';
    $isSiteStudio = $userInputValues['site_studio'] ?? '';

    // Set default theme as olivero (if not defined)
    $starterKit['themes']['default'] = $starterKit['themes']['default'] ?? "olivero";

    if ($isContentModel == "yes") {
      $starterKit['modules']['install'] = array_merge(
        $starterKit['modules']['install'], [
          'acquia_cms_article:^1.3.4',
          'acquia_cms_event:^1.3.4',
        ],
      );
    }
    if ($isDemoContent == "yes") {
      $starterKit['modules']['install'] = array_merge(
        $starterKit['modules']['install'], [
          'acquia_cms_starter:^1.3.0',
        ],
      );
    }
    if ($isSiteStudio == "yes") {
      $starterKit['modules']['install'] = array_merge(
        $starterKit['modules']['install'], [
          'acquia_cms_site_studio:^1.3.5',
        ],
      );
      $starterKit['themes']['default'] = "cohesion_theme";
    }
    $starterKit['modules']['install'] = array_unique($starterKit['modules']['install']);
    return $starterKit;
  }

}
