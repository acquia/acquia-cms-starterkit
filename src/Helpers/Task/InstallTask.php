<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules;
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
   * @param \AcquiaCMS\Cli\Helpers\Task\Steps\SiteStudioPackageImport $siteStudioPackageImport
   *   The site studio package import step object.
   */
  public function __construct(Cli $cli, ValidateDrupal $validateDrupal, DownloadDrupal $downloadDrupal, DownloadModules $downloadModules, SiteInstall $siteInstall, EnableModules $enableModules, SiteStudioPackageImport $siteStudioPackageImport) {
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $validateDrupal;
    $this->downloadDrupal = $downloadDrupal;
    $this->enableModules = $enableModules;
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
    if (!$this->validateDrupal->execute()) {
      $this->print("Looks like, current project is not a Drupal project:", 'warning');
      $this->print("Converting the current project to Drupal project:", 'headline');
      $this->downloadDrupal->execute();
    }
    else {
      $this->print("Seems Drupal is already downloaded. Skipping downloading Drupal.", 'success');
    }
    $this->print("Downloading all packages/modules/themes required by the starter-kit:", 'headline');
    $this->downloadModules->execute($this->starterKits[$this->bundle]);
    $this->print("Installing Site:", 'headline');
    $this->siteInstall->execute([
      'no-interaction' => $this->input->getOption('no-interaction'),
    ]);
    $this->print("Enabling modules for the starter-kit:", 'headline');
    $this->enableModules->execute([
      'type' => 'modules',
      'packages' => $this->starterKits[$this->bundle]['modules'],
      'keys' => $args['keys'],
    ]);
    $this->print("Enabling themes for the starter-kit:", 'headline');
    $this->enableModules->execute([
      'type' => 'themes',
      'packages' => $this->starterKits[$this->bundle]['themes'],
      'keys' => $args['keys'],
    ]);
    // Trigger site studio import process if starter or
    // page module is there in active bundle.
    $modules_ss_import = [
      'acquia_cms_page',
      'acquia_cms_starter',
      'acquia_cms_site_studio',
    ];
    $bundle_modules = $this->starterKits[$this->bundle]['modules']['install'] ?? [];
    $modules_list = $this->getModulesWithoutVersionString($bundle_modules);
    if (array_intersect($modules_ss_import, $modules_list)) {
      $this->print("Running site studio package import for starter-kit:", 'headline');
      $this->siteStudioPackageImport->execute([
        'no-interaction' => $this->input->getOption('no-interaction'),
      ]);
    }
  }

  /**
   * Get lists of modules name without version string.
   *
   * Ex: acquia_cms_page:1.3.x-dev give acquia_cms_page.
   *
   * @param array $modules
   *   The modules array.
   *
   * @return array
   *   The list of module without version string.
   */
  protected function getModulesWithoutVersionString(array $modules): array {
    $modulesList = [];
    foreach ($modules as $module) {
      $modulesList[] = explode(':', $module)[0];
    }
    return $modulesList;
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
