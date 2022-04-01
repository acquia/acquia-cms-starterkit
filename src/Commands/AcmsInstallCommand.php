<?php

namespace AcquiaCMS\Cli\Commands;

use Symfony\Component\Yaml\Yaml;
use AcquiaCMS\Cli\Helper\ComposerHelper;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AcmsInstallCommand extends Command {

  protected static $defaultName = 'acms:install';
  protected static $defaultDescription = 'The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.';
  protected $projectDirectory;

  /**
   * Class constructor.
   *
   * @param string $project_dir
   */
  public function __construct(string $project_dir) {
      $this->projectDirectory = $project_dir;
      parent::__construct();
  }

  /**
   * Configuration
   *
   * @return void
   */
  protected function configure() {
    $this->setDefinition([new InputOption('bundle', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Please select bundle for setting up acquia cms?')]);
    $this->setHelp("This command allows user to setup Acquia CMS by selecting predefine starter kit, Ex: 'Acquia CMS Demo', 'Acquia CMS Low Code' etc.");
  }

	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
    $bundle = $input->getOption('bundle');
    if (!empty($bundle)) {
      $bundle_name = $bundle['name'];
      $output->writeln('Please wait, while we are setting up site with bundle ['.$bundle_name.']');

      $requiredModules = $bundle['modules'];
      $requiredThemes = $bundle['themes'];
      $modulesThemses = implode(" ", array_merge($requiredModules, $requiredThemes));
      array_walk($requiredThemes, function (&$theme, $key) {
        $theme = "drupal/$theme:^1.0";
      });
      array_walk($requiredModules, function (&$module, $key) {
        $module = "drupal/$module:^1.0";
      });
      $moduleThemsesRequired = implode(" ", array_merge($requiredModules, $requiredThemes));
      $composerRequireCommand = "composer require $moduleThemsesRequired";

      // Require all modules and themse for selected package.
      try {
        $process = new Process($composerRequireCommand);
        $process->setWorkingDirectory(getcwd());
        $process->run();

        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        }

        $output->writeln($process->getOutput());
        $output->writeln($process->getErrorOutput());
      } catch (\Exception $e) { throw new \Exception($e); }

      // Run site install
      try {
        $process = new Process('./vendor/bin/drush site-install -y acquia_cms');
        $process->setWorkingDirectory(getcwd());
        $process->run();

        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        }

        $output->writeln($process->getOutput());
        $output->writeln($process->getErrorOutput());
      } catch (\Exception $e) { throw new \Exception($e); }

      // Enable modules/themes
      try {
        $process = new Process('./vendor/bin/drush en '. $modulesThemses);
        $process->setWorkingDirectory(getcwd());
        $process->run();

        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        }

        $output->writeln($process->getOutput());
        $output->writeln($process->getErrorOutput());
      } catch (\Exception $e) { throw new \Exception($e); }

    }
	}
  /**
   * Interact with user.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $acmsData = Yaml::parseFile($this->projectDirectory . '/acms/acms.yml');
    $output->writeln("<info>" . file_get_contents($this->projectDirectory . "/assets/acquia_cms.icon.ascii") . "</info>");
    $output->writeln("<fg=cyan;options=bold,underscore>Welcome to Acquia CMS starterkit installer</>");
    $output->writeln("");

    $table = new Table($output);
    $bundlesData = [];
    $table->setHeaders(['ID', 'Name', 'Description']);
    foreach($acmsData["starter_kits"] as $starter_kit) {
      $table->addRow([$starter_kit['id'], $starter_kit['name'], $starter_kit['description']]);
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
}
