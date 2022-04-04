<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Composer\ComposerFacade;
use AcquiaCMS\Cli\Helpers\Process\ProcessFactory;
use AcquiaCMS\Cli\Helpers\Process\ProcessManager;
use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaMinimalClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class AcmsInstallCommand extends Command {

    protected $projectDirectory;
    protected $acquiaCmsCli;
    protected $starterKits;
    protected $acquiaMinimalClient;
    public function __construct(Cli $cli, InstallTask $installTask) {
        $this->acquiaCmsCli = $cli;
        $this->starterKits = $this->acquiaCmsCli->getStarterKits();
        $this->installTask = $installTask;
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
    $this->installTask->configure($input, $output, $this);
    $this->installTask->run();
	}

}
