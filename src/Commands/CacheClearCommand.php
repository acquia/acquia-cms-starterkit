<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Enum\StatusCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the placeholder command to clear caches.
 *
 * @code ./vendor/bin/acms cc
 */
class CacheClearCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("cache:clear")
      ->setDescription("Clears all the caches.")
      ->setAliases(['cc'])
      ->setHelp("The <info>cache:clear</info> command clears all the caches.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    // DOES NOTHING.
    // This is just the placeholder function to provide the cache:clear
    // command in command list. As cache clear command is handled before
    // application gets bootstrapped. Because there might be instances
    // when cache gets corrupted and if implemented using console command,
    // it might not allow us to clear cache, when it's actually needed.
    // @see {project_root}/bin/acms.php
    return StatusCodes::OK;
  }

}
