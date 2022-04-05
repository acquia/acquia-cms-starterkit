<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Exception\AcmsCliException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the Sample hello command.
 *
 * @code ./vendor/bin/acms hello
 */
class HelloCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("hello")
      ->setDescription("This command prints 'Hello World!'")
      ->setDefinition([
        new InputOption('name', '', InputOption::VALUE_OPTIONAL, 'Name of the user'),
        new InputOption('users', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Space-separated user_names'),
      ])
      ->setHelp("The <info>hello</info> command just prints 'Hello World!'");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    try {
      $name = "";
      if ($input->getOption("name")) {
        $name = $input->getOption("name");
      }

      $output->writeln("Hello World $name!");
    }
    catch (AcmsCliException $e) {
      return FALSE;
    }
    return TRUE;
  }

}
