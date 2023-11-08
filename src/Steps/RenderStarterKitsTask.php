<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to render starter_kit on terminal.
 *
 * @Task(
 *   id = "render_starter_kits",
 *   weight = 10,
 * )
 */
class RenderStarterKitsTask extends BaseTask {

  /**
   * Holds the file-loader service object.
   *
   * @var \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface
   */
  protected $starterKitManager;

  /**
   * Creates a task object.
   *
   * @param \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface $starter_kit_manager
   *   The starter_kit_manager service object.
   */
  public function __construct(StarterKitManagerInterface $starter_kit_manager) {
    $this->starterKitManager = $starter_kit_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get("starter_kit_manager"),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    $starter_kits = $this->starterKitManager->getStarterKits();
    $total = count($starter_kits);
    $key = 0;

    /** @var \AcquiaCMS\Cli\Helpers\StarterKit $starter_kit */
    foreach ($starter_kits as $starter_kit) {
      $useCases[$starter_kit->getId()] = $starter_kit;
      $table->addRow([
        $starter_kit->getId(),
        $starter_kit->getName(),
        $starter_kit->getDescription(),
      ]);
      if ($key + 1 != $total) {
        $table->addRow(["", "", ""]);
      }
      $key++;
    }
    $table->setColumnMaxWidth(2, 81);
    $table->setStyle('box');
    $table->render();
    return StatusCode::OK;
  }

}
