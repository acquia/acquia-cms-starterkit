<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Helpers\Process\ProcessFactory;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class AcmsInstallCommand extends Command {

    protected $projectDirectory;
    public function __construct(string $project_dir, ProcessFactory $processFactory, SymfonyStyle $output, AcquiaMinimalClient $minimalClient) {
        $this->projectDirectory = $project_dir;
        $this->processFactory = $processFactory->getInstance();
        parent::__construct();
    }

    /**
	 * Configuration
	 *
	 * @return void
	 */
	protected function configure() {
		$this->setName("acms:install")
			->setDescription("Use this command to setup & install site.")
			->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
	}

	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
	    $acmsData = Yaml::parseFile($this->projectDirectory . '/acms/acms.yml');
	    $output->writeln("<info>" . file_get_contents($this->projectDirectory . "/assets/acquia_cms.icon.ascii") . "</info>");
	    $output->writeln("<fg=cyan;options=bold,underscore>Welcome to Acquia CMS starterkit installer</>");
        $output->writeln("");
//        $outputStyle = new OutputFormatterStyle('red', '#ff0', ['bold', 'blink']);
//        $output->getFormatter()->setStyle('"Welcome to Acquia CMS starterkit installer."', $outputStyle);
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Description']);
        $useCases = [];
	    foreach($acmsData["starter_kits"] as $starter_kit) {
            $useCases[$starter_kit['id']] = $starter_kit;
            $table->addRow([$starter_kit['id'], $starter_kit['name'], $starter_kit['description']]);
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
        array_walk($useCases[$bundle]["modules"], function(&$item) { $item = "drupal/$item"; });
        $output->writeln(
            'Will only be printed in verbose mode or higher',
            OutputInterface::VERBOSITY_VERBOSE
        );
//        $this->processFactory->add(['composer', 'config', "minimum-stability", "dev"], "Setting composer minimum stability to `dev`");
//        $this->processFactory->add(['composer', 'configura', "prefer-stable", "true"]);
//        $this->processFactory->add(['composer', 'require', "drupal/core:~9.3", "-W"], "Adding latest Drupal core.");
        $this->processFactory->add(['composer', 'require'] + $useCases[$bundle]["modules"], "Downloading all modules required by the starter: `$bundle`.");
        $this->processFactory->run();
        //print_r($useCases[$bundle]["modules"]);
//        $process = new Process(['composer', 'config', "minimum-stability", "dev"]);
//        $process->start();
//        $process->wait(function ($type, $buffer) {
//            print $buffer;
//        });
//        $process = new Process(['composer', 'config', "prefer-stable", "true"]);
//        $process->start();
//        $process->wait(function ($type, $buffer) {
//            print $buffer;
//        });
//        $process = new Process(['composer', 'require', "drupal/core:~9.3", "-W"]);
//        $process->setTty(true);
//        $process->setTimeout(3600);
//        $process->start();
//        $process->wait(function ($type, $buffer) {
//            print $buffer;
//        });
//        $process = new Process(['composer', 'require'] + $useCases[$bundle]["modules"]);
//        $process->setTty(true);
//        $process->setTimeout(3600);
//        $process->start();
//        $process->wait(function ($type, $buffer) {
//            print $buffer;
//        });
	}
}
