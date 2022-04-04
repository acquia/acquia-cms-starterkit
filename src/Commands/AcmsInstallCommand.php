<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Helper\ComposerHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Acquia cms install command class.
 */
class AcmsInstallCommand extends Command {

  /**
   * The default command name.
   *
   * @var string
   */
  protected static $defaultName = 'acms:install';

  /**
   * The default command description.
   *
   * @var string
   */
  protected static $defaultDescription = 'The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.';

  /**
   * The project directory path.
   *
   * @var string
   */
  protected $projectDirectory;

  /**
   * The composer helper class.
   *
   * @var \AcquiaCMS\Cli\Helper\ComposerHelper
   */
  protected $composerHelper;

  /**
   * Class constructor.
   *
   * @param string $project_dir
   *   The directory path.
   * @param \AcquiaCMS\Cli\Helper\ComposerHelper $composer_helper
   *   The composer helper class.
   */
  public function __construct(string $project_dir, ComposerHelper $composer_helper) {
    $this->projectDirectory = $project_dir;
    $this->composerHelper = $composer_helper;
    parent::__construct();
  }

  /**
   * Configuration.
   */
  protected function configure() {
    $this->setDefinition([new InputOption('bundle', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Please select bundle for setting up acquia cms?')]);
    $this->setHelp("This command allows user to setup Acquia CMS by selecting predefine starter kit, Ex: 'Acquia CMS Demo', 'Acquia CMS Low Code' etc.");
  }

  /**
   * Executes the command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input interface.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   *
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $bundle = $input->getOption('bundle');
    if (!empty($bundle)) {
      $bundle_name = $bundle['name'];
      $output->writeln('Please wait, while we are setting up site with bundle [' . $bundle_name . ']');
      $requiredModules = $bundle['modules'];
      $requiredThemes = $bundle['themes'];

      // Run composer require modules/themes for selected package.
      try {
        $requiredModulesThemes = $this->composerHelper->getRequiredModulesThemes($requiredModules, $requiredThemes);
        $composerRequireProcess = Process::fromShellCommandline('composer require ' . $requiredModulesThemes);
        $this->setCommonProcessTask($composerRequireProcess, $output);

        // Run site install using minimal profile.
        if ($composerRequireProcess->isSuccessful()) {
          $siteInstallProcess = Process::fromShellCommandline('./vendor/bin/drush site-install -y minimal');
          $this->setCommonProcessTask($siteInstallProcess, $output);

          // Enables required themes and modules.
          if ($siteInstallProcess->isSuccessful()) {
            $modules = $this->composerHelper->getModuleList($requiredModules);
            $modulesInstallProcess = Process::fromShellCommandline('./vendor/bin/drush pm:enable -y ' . $modules);
            $this->setCommonProcessTask($modulesInstallProcess, $output);

            $themes = $this->composerHelper->getThemeList($requiredThemes);
            $themesInstallProcess = Process::fromShellCommandline('./vendor/bin/drush theme:enable -y ' . $themes);
            $this->setCommonProcessTask($themesInstallProcess, $output);

          }
        }
      }
      catch (\Exception $e) {
        throw new \Exception($e);
      }
    }
  }

  /**
   * Interact with user.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input interface.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $acmsData = Yaml::parseFile($this->projectDirectory . '/acms/acms.yml');
    $output->writeln("<info>" . file_get_contents($this->projectDirectory . "/assets/acquia_cms.icon.ascii") . "</info>");
    $output->writeln("<fg=cyan;options=bold,underscore>Welcome to Acquia CMS starterkit installer</>");
    $output->writeln("");

    $table = new Table($output);
    $bundlesData = [];
    $table->setHeaders(['ID', 'Name', 'Description']);
    foreach ($acmsData["starter_kits"] as $starter_kit) {
      $table->addRow([
        $starter_kit['id'],
        $starter_kit['name'],
        $starter_kit['description'],
      ]);
      $bundlesData[$starter_kit['id']] = $starter_kit;
    }
    $table->setStyle('box');
    $table->render();

    $helper = $this->getHelper('question');
    $bundles = array_column($acmsData['starter_kits'], 'id');
    $question = new Question("Please choose from one of the above use case: <comment>[acquia_cms_demo]</comment>: ", 'acquia_cms_demo');
    $question->setAutocompleterValues($bundles);
    $question->setValidator(function ($answer) use ($bundles) {
      if (!is_string($answer) || !in_array($answer, $bundles)) {
        throw new \RuntimeException(
          " Please choose from one of the use case defined above. Ex: acquia_cms_demo."
        );
      }
      return $answer;
    });
    $question->setMaxAttempts(3);
    $bundle = $helper->ask($input, $output, $question);
    if (isset($bundlesData[$bundle])) {
      $input->setOption('bundle', $bundlesData[$bundle]);
    }
  }

  /**
   * Helper function to set common task for the process and output.
   *
   * @param \Symfony\Component\Process\Process $process
   *   The project class object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   */
  private function setCommonProcessTask(Process $process, OutputInterface $output) {
    $process->setWorkingDirectory($this->composerHelper->getProjectRootPath());
    $process->setTimeout(NULL);
    $process->setTty(TRUE);
    $process->start();
    $process->wait(function ($type, $buffer) {
      print $buffer;
    });

    if (!$process->isSuccessful() && $output->isVerbose()) {
      throw new ProcessFailedException($process);
    }
  }

}
