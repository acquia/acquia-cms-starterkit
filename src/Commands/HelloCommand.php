<?php
namespace AcquiaCMS\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class HelloCommand extends Command {

	/**
	 * Configuration
	 *
	 * @return void
	 */
	protected function configure() {
		$this->setName("hello")
			->setDescription("This command prints 'Hello World!'")
			->setDefinition(array(
				new InputOption('name', '', InputOption::VALUE_OPTIONAL, 'Name of the user'),
				new InputOption('users', '',InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Space-separated user_names'),
			))
			->setHelp("The <info>hello</info> command just prints 'Hello World!'");
	}

	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
        $name = "";
	    if ($input->getOption("name")) {
	      $name = $input->getOption("name");
        }

		$output->writeln("Hello World $name!");
	}
}