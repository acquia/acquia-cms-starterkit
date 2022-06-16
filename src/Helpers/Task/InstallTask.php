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
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   */
  public function __construct(Cli $cli, ContainerInterface $container) {
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $container->get(ValidateDrupal::class);
    $this->downloadDrupal = $container->get(DownloadDrupal::class);
    $this->enableModules = $container->get(EnableModules::class);
    $this->enableThemes = $container->get(EnableThemes::class);
    $this->siteInstall = $container->get(SiteInstall::class);
    $this->downloadModules = $container->get(DownloadModules::class);
    $this->siteStudioPackageImport = $container->get(SiteStudioPackageImport::class);
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
    $this->acquiaCmsCli->alterModulesAndThemes($this->starterKits[$this->bundle], $args['keys']);

    $this->downloadModules->execute($this->starterKits[$this->bundle]);
    $this->print("Installing Site:", 'headline');
    $this->siteInstall->execute([
      'no-interaction' => $this->input->getOption('no-interaction'),
    ]);
    $bundle_modules = $this->starterKits[$this->bundle]['modules']['install'] ?? [];
    $modules_list = JsonParser::installPackages($bundle_modules);
    $this->print("Enabling modules for the starter-kit:", 'headline');
    $isDemoContent = $args['keys']['demo_content'] ?? '';
    if ($isDemoContent == "yes" && ($key = array_search('acquia_cms_starter', $modules_list)) !== FALSE) {
      // Remove acquia_cms_starter module in the list of modules to install.
      // Because we install this module separately in the last.
      unset($modules_list[$key]);
    }
    $this->enableModules->execute([
      'modules' => $modules_list,
      'keys' => $args['keys'],
    ]);
    $this->print("Enabling themes for the starter-kit:", 'headline');
    $this->enableThemes->execute([
      'themes' => $this->starterKits[$this->bundle]['themes'],
      'starter_kit' => $this->bundle,
    ]);

    $siteStudioApiKey = $args['keys']['SITESTUDIO_API_KEY'] ?? '';
    $siteStudioOrgKey = $args['keys']['SITESTUDIO_ORG_KEY'] ?? '';
    // Trigger Site Studio Package import, if acquia_cms_site_studio module
    // is there in active bundle.
    if (in_array('acquia_cms_site_studio', $modules_list)) {
      if ($siteStudioApiKey && $siteStudioOrgKey) {
        $this->print("Running Site Studio package import:", 'headline');
        $this->siteStudioPackageImport->execute([
          'no-interaction' => $this->input->getOption('no-interaction'),
        ]);
      }
      else {
        $this->print("Skipped importing Site Studio Packages." .
          PHP_EOL .
          "You can set the key later from: /admin/cohesion/configuration/account-settings & import Site Studio packages.",
          "warning",
              );
      }
    }

    // Enable: acquia_cms_site_studio_content (instead acquia_cms_starter), if
    // acquia_cms_starter module is in the list of module installation and any
    // content model module is not available in the list of module installation
    // like acquia_cms_article, acquia_cms_page etc.
    if ($isDemoContent == "yes") {
      $starter_module = (
        !in_array('acquia_cms_article', $modules_list) &&
        in_array('acquia_cms_site_studio', $modules_list) &&
        $siteStudioApiKey && $siteStudioOrgKey
      ) ? 'acquia_cms_site_studio_content' : 'acquia_cms_starter';
      $this->print("Enabling Starter module for the starter-kit:", 'headline');
      $this->enableModules->execute([
        'modules' => [$starter_module],
        'keys' => $args['keys'],
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

}
