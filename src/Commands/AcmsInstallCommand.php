<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code ./vendor/bin/acms acms:install
 */
class AcmsInstallCommand extends Command {

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * The AcquiaCMS InstallTask object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Task\InstallTask
   */
  protected $installTask;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Helpers\Task\InstallTask $installTask
   *   Provides the Acquia CMS Install task object.
   */
  public function __construct(InstallTask $installTask) {
    $this->installTask = $installTask;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition([
        new InputArgument('name', NULL, "Name of the starter kit", 'acquia_cms_minimal'),
      ])
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    try {
      $this->installTask->configure($input, $output, $this);
      $this->installTask->run();
      $formatter = $this->getHelper('formatter');
      $name = $input->getArgument("name");
      $infoMessage = "[OK] Thank you for choosing Acquia CMS. We've successfully setup your project using bundle: `$name`.";
      $formattedInfoBlock = $formatter->formatBlock($infoMessage, 'fg=black;bg=green;options=bold', TRUE);
      $output->writeln($formattedInfoBlock);
    }
    catch (AcmsCliException $e) {
      $output->writeln("<error>" . $e->getMessage() . "</error>");
      return StatusCodes::ERROR;
    }
    return StatusCodes::OK;
  }

}
