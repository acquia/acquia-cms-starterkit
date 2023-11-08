<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to run commands before starting to execute task.
 *
 * @Task(
 *   id = "pre_site_install_task",
 *   weight = 5,
 * )
 */
class PreSiteInstallTask extends BaseTask {

  const HEADLINE = "Welcome to Acquia CMS Starter Kit installer";

  /**
   * Holds the The starter_kit project directory.
   *
   * @var string
   */
  protected $starterKitDirectory;

  /**
   * Creates the task object.
   *
   * @param string $starter_kit_dir
   *   The starter_kit project directory.
   */
  public function __construct(string $starter_kit_dir) {
    $this->starterKitDirectory = $starter_kit_dir;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->getParameter("kernel.project_dir"),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln("<info>" . file_get_contents($this->starterKitDirectory . "/assets/acquia_cms.icon.ascii") . "</info>");
    $output->writeln("<fg=cyan;options=bold,underscore> " . self::HEADLINE . "</>");
    $output->writeln("");
    return StatusCode::OK;
  }

}
