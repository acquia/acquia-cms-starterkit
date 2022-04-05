<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use Symfony\Component\Console\Command\Command;
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
   * @var AcquiaCMS\Cli\Helpers\Task\InstallTask
   */
  protected $installTask;

  /**
   * Constructs an instance.
   *
   * @param AcquiaCMS\Cli\Helpers\Task\InstallTask $installTask
   *   Provides the Acquia CMS Install task object.
   */
  public function __construct(InstallTask $installTask) {
    $this->installTask = $installTask;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->installTask->configure($input, $output, $this);
    $this->installTask->run();
  }

}
