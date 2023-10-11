<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableThemes;
use AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall;
use AcquiaCMS\Cli\Helpers\Task\Steps\ToggleModules;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes the task needed to run site:install command.
 */
class SiteInstallTask {

  /**
   * Holds the Acquia CMS cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

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
   * Holds the Site Install step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall
   */
  protected $siteInstall;

  /**
   * Holds Enable modules step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules
   */
  protected $enableModules;

  /**
   * Holds Enable Themes step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\EnableThemes
   */
  protected $enableThemes;

  /**
   * Holds Toggle Modules step object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\Steps\ToggleModules
   */
  protected $toggleModules;

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
    $this->siteInstall = $container->get(SiteInstall::class);
    $this->enableModules = $container->get(EnableModules::class);
    $this->enableThemes = $container->get(EnableThemes::class);
    $this->toggleModules = $container->get(ToggleModules::class);
  }

  /**
   * Configures the InstallTask class object.
   *
   * @poram Symfony\Component\Console\Input\InputInterface $input
   *   A Symfony input interface object.
   * @poram Symfony\Component\Console\Input\OutputInterface $output
   *   A Symfony output interface object.
   */
  public function configure(InputInterface $input, OutputInterface $output) :void {
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Executes all the steps needed for install task.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function run(array $args = []) :void {
    $this->acquiaCmsCli->printLogo();
    if (!$this->input->getOption('display-command')) {
      $this->input->setOption('hide-command', TRUE);
      $this->siteInstall->drushCommand->setInput($this->input);
    }
    $options = $this->input->getOptions();
    $profile = $this->input->getArgument('profile');
    $commands = ["site:install"];
    $commands = ($profile) ? array_merge($commands, $profile) : array_merge($commands, ['minimal']);
    foreach ($options as $option => $value) {
      if ($value && $option != "uri" && $option != "display-command" && $option != "hide-command") {
        $commands[] = (is_bool($value)) ? "--$option" : "--$option=$value";
      }
    }
    // @todo Use some alternative approach for clearing Drupal caches
    // instead of using below class.
    // Clear cache command before running site:install
    $this->siteInstall->drushCommand->prepare(['cr'])->runQuietly([], FALSE);
    $this->siteInstall->execute([
      'command' => $commands,
    ]);
    $this->enableModules->execute([
      'modules' => $this->getModulesToInstall(),
    ]);
    $this->enableThemes->execute([
      'themes' => [
        'install' => [
          'gin',
          'cohesion_theme',
        ],
        'admin' => 'gin',
        'default' => 'cohesion_theme',
      ],
      'starter_kit' => 'acquia_cms_existing_site',
    ]);
    $this->toggleModules->execute([
      'no-interaction' => $this->input->getOption('no-interaction'),
    ]);
    $this->acquiaCmsCli->printLogo();
  }

  /**
   * Returns an array of Acquia CMS modules to install.
   */
  protected function getModulesToInstall(): array {
    return [
      'acquia_cms_article',
      'acquia_cms_document',
      'acquia_cms_event',
      'acquia_cms_page',
      'acquia_cms_search',
      'acquia_cms_site_studio',
      'acquia_cms_toolbar',
      'acquia_cms_tour',
      'acquia_cms_video',
    ];
  }

}
