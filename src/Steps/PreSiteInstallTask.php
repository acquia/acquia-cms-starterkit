<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\FileSystem\FileLoader;
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
 *   weight = -1,
 * )
 */
class PreSiteInstallTask extends BaseTask {

  const HEADLINE = "Welcome to Acquia CMS Starter Kit installer";

  /**
   * Holds the file_loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   The file_loader service object.
   */
  public function __construct(FileLoader $fileLoader) {
    $this->fileLoader = $fileLoader;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('file_loader')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln("<info>" . file_get_contents($this->fileLoader->getLogo()) . "</info>");
    $output->writeln("<fg=cyan;options=bold,underscore> " . self::HEADLINE . "</>");
    $output->writeln("");
    return StatusCode::OK;
  }

}
