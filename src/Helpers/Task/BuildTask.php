<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Executes the task needed to run site:build command.
 */
class BuildTask {

  use StatusMessageTrait;

  /**
   * Holds the Acquia CMS cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

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
   * User selected bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * User selected bundle.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * User selected bundle.
   *
   * @var string
   */
  protected $projectDir;

  /**
   * An array of Starter Kit.
   *
   * @var array
   */
  protected $starterKits;

  /**
   * Constructs an object.
   *
   * @param string $root_dir
   *   Root directory path.
   * @param \AcquiaCMS\Cli\Cli $cli
   *   An Acquia CMS cli class object.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   */
  public function __construct(string $root_dir, Cli $cli, ContainerInterface $container) {
    $this->projectDir = $root_dir;
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $container->get(ValidateDrupal::class);
    $this->downloadDrupal = $container->get(DownloadDrupal::class);
    $this->downloadModules = $container->get(DownloadModules::class);
    $this->filesystem = $container->get(Filesystem::class);
  }

  /**
   * Configures the BuildTask class object.
   *
   * @poram Symfony\Component\Console\Input\InputInterface $input
   *   A Symfony input interface object.
   * @poram Symfony\Component\Console\Input\OutputInterface $output
   *   A Symfony output interface object.
   * @poram Symfony\Component\Console\Command\Command $output
   *   The site:build Symfony console command object.
   */
  public function configure(InputInterface $input, OutputInterface $output, string $bundle) :void {
    $this->bundle = $bundle;
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Executes all the steps needed for build task.
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
    $this->buildModulesAndThemes($args);
    $this->downloadModules->execute($this->starterKits[$this->bundle]);
  }

  /**
   * Alter modules and themes based on starter-kit.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  protected function buildModulesAndThemes(array $args): void {
    $this->acquiaCmsCli->alterModulesAndThemes($this->starterKits[$this->bundle], $args['keys']);
  }

  /**
   * Create build file in top level directory.
   *
   * @param array $args
   *   An array of params argument to pass.
   * @param string $site
   *   The site uri.
   */
  public function createBuild(array $args, string $site) :void {
    $build_path = $this->projectDir . '/acms';
    $this->buildModulesAndThemes($args);
    $installed_modules = $this->starterKits[$this->bundle]['modules']['install'];
    $installed_themes = $this->starterKits[$this->bundle]['themes'];
    // Build array structure for build.yml.
    $build_content = [
      'sites' => [
        $site => [
          'modules' => $installed_modules,
          'starter_kit' => $this->bundle,
          'themes' => [
            'admin' => $installed_themes['admin'],
            'default' => $installed_themes['default'],
          ],
        ],
      ],
    ];

    // Create directory if not already there.
    if (!$this->filesystem->exists($build_path)) {
      $this->filesystem->mkdir($build_path);
    }
    if ($this->filesystem->exists($build_path)) {
      $file_name = $build_path . '/build.yml';
      if (!$this->filesystem->exists($file_name)) {
        $yaml_build_content = Yaml::dump($build_content, 4, 2);
        $this->filesystem->dumpFile($file_name, $yaml_build_content);
      }
      // Write data to the file.
      if ($this->filesystem->exists($file_name)) {
        $value = Yaml::parseFile($file_name);
        $updated_value['sites'] = array_merge($value['sites'], $build_content['sites']);
        $yaml_updated_value = Yaml::dump($updated_value, 4, 2);
        $this->filesystem->dumpFile($file_name, $yaml_updated_value);
      }
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
