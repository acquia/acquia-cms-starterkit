<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\FileSystem\FileLoader;
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
 *   weight = 0,
 * )
 */
class RenderStarterKitsTask extends BaseTask {

  /**
   * Holds the file-loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * Creates a task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   A file loader service object.
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
    $table = new Table($output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    $starter_kits = $this->fileLoader->getStarterKits();
    $total = count($starter_kits);
    $key = 0;
    foreach ($starter_kits as $id => $starter_kit) {
      $useCases[$id] = $starter_kit;
      $table->addRow([$id, $starter_kit['name'], $starter_kit['description']]);
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
