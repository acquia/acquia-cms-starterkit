<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\FileSystem\StarterkitConfigLoader;
use AcquiaCMS\Cli\FileSystem\StarterKitManagerManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to run commands before starting to execute task.
 *
 * @Task(
 *   id = "validate_starter_kits",
 *   weight = 0,
 * )
 */
class ValidateStarterKitTask extends BaseTask {

  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $projectDir;

  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $rootDir;

  /**
   * The yaml config loader object.
   *
   * @var \AcquiaCMS\Cli\FileSystem\StarterkitConfigLoader
   */
  protected $configLoader;

  /**
   * Holds the container service object.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Creates the task object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container service object.
   */
  public function __construct(ContainerInterface $container) {
    $this->projectDir = $container->getParameter("kernel.project_dir");
    $this->rootDir = $container->getParameter("app.base_dir");
    $this->configLoader = new StarterkitConfigLoader($this->rootDir . "/acms/acms.yml");
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int {
    $this->configLoader->add($this->projectDir . "/acms/acms.yml");
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $this->configLoader->processConfigFiles();
    $manager = StarterKitManagerManager::loadFromArray($this->configLoader->getContent());
    $this->container->set("starter_kit_manager", $manager);
    return StatusCode::OK;
  }

}
