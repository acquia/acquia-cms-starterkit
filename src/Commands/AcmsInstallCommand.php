<?php

namespace AcquiaCMS\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class AcmsInstallCommand extends Command {

    protected $projectDirectory;
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
	    foreach($acmsData["starter_kits"] as $starter_kit) {
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
	}
}