<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableThemes;
use AcquiaCMS\Cli\Helpers\Task\Steps\InitNextjsApp;
use AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall;
use AcquiaCMS\Cli\Helpers\Task\Steps\ToggleModules;
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
   * The toggle modules step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\ToggleModules
   */
  protected $toggleModules;

  /**
   * The site studio package import step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\InitNextjsApp
   */
  protected $initNextjsApp;

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
    $this->toggleModules = $container->get(ToggleModules::class);
    $this->initNextjsApp = $container->get(InitNextjsApp::class);
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
    $this->acquiaCmsCli->alterModulesAndThemes($this->starterKits[$this->bundle], $args['keys']);
    $this->downloadModules->execute($this->starterKits[$this->bundle]);

    $this->print("Installing Site:", 'headline');
    $this->siteInstall->execute([
      'no-interaction' => $this->input->getOption('no-interaction'),
      'name' => $this->starterKits[$this->bundle]['name'],
    ]);

    $bundle_modules = $this->starterKits[$this->bundle]['modules']['install'] ?? [];
    $modules_list = JsonParser::installPackages($bundle_modules);
    // Get User password from shared factory.
    $password = SharedFactory::getData('password');
    $this->print("User name: admin, User password: $password", 'info');
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

    // Toggle modules based on environments.
    $this->print("Toggle modules for the starter-kit:", 'headline');
    $this->toggleModules->execute([
      'no-interaction' => $this->input->getOption('no-interaction'),
    ]);

    $siteStudioApiKey = $args['keys']['SITESTUDIO_API_KEY'] ?? '';
    $siteStudioOrgKey = $args['keys']['SITESTUDIO_ORG_KEY'] ?? '';
    // Trigger Site Studio Package import, if acquia_cms_site_studio module
    // is there in active bundle.
    if (in_array('acquia_cms_site_studio', $modules_list)) {
      if (!(($siteStudioApiKey && $siteStudioOrgKey) || (getenv('SITESTUDIO_API_KEY') && getenv('SITESTUDIO_ORG_KEY')))) {
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
      $this->print("Enabling Starter module for the starter-kit:", 'headline');
      $this->enableModules->execute([
        'modules' => ['acquia_cms_starter'],
        'keys' => $args['keys'],
      ]);
    }

    $isNextjsApp = $args['keys']['nextjs_app'] ?? '';
    // Initialize: NextJs application, create consumer, create nextjs site,
    // write/display nextjs site environment variables.
    if ($isNextjsApp == "yes") {
      $this->print("Initiating NextJs App for the starter-kit:", 'headline');
      $isNextjsAppSiteUrl = $args['keys']['nextjs_app_site_url'] ?? '';
      $isNextjsAppSiteName = $args['keys']['nextjs_app_site_name'] ?? '';
      $isNextjsAppEnvFile = $args['keys']['nextjs_app_env_file'] ?? '';
      $this->initNextjsApp->execute([
        '--site-url' => $isNextjsAppSiteUrl,
        '--site-name' => $isNextjsAppSiteName,
        '--env-file' => $isNextjsAppEnvFile,
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
