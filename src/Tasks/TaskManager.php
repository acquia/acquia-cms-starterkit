<?php

namespace AcquiaCMS\Cli\Tasks;

use AcquiaCMS\Cli\Enum\StatusCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to manage tasks.
 */
class TaskManager {

  /**
   * Holds task_discovery object.
   *
   * @var \AcquiaCMS\Cli\Tasks\TaskDiscovery
   */
  protected $discovery;

  /**
   * Holds the symfony container object.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Holds the symfony command object.
   *
   * @var \Symfony\Component\Console\Command\Command
   */
  protected $command;

  /**
   * Holds the io service object.
   *
   * @var \AcquiaCMS\Cli\ConsoleIO
   */
  protected $io;

  /**
   * Constructs an object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container object.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->discovery = $container->get("task_discovery");
    $this->io = $container->get('io');
  }

  /**
   * Configures the task_manager service object.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   Given command object.
   */
  public function configure(Command $command): void {
    $this->command = $command;
  }

  /**
   * Returns a list of available workers.
   *
   * @return array
   *   Returns an array of sorted tasks.
   */
  public function getTasks(): array {
    $commandDir = preg_replace('/[^A-Za-z0-9]/', ' ', $this->command->getName());
    $commandDir = ucwords($commandDir);
    $commandDir = $this->container->getParameter("kernel.project_dir") . "/src/Steps/" . str_replace(" ", "", $commandDir);
    if (is_dir($commandDir)) {
      $this->discovery->addDirectory($commandDir);
    }
    $tasks = $this->discovery->getTasks();
    $this->filterTasks($tasks);
    return $this->sortTasks($tasks);
  }

  /**
   * Returns one worker by name.
   *
   * @param string $name
   *   The task id.
   *
   * @throws \Exception
   */
  public function getTask(string $name): array {
    $workers = $this->discovery->getTasks();
    if (isset($workers[$name])) {
      return $workers[$name];
    }

    throw new \Exception('Task not found.');
  }

  /**
   * Execute the tasks.
   *
   * @throws \Exception
   */
  public function executeTasks(): void {
    $tasks = $this->getTasks();
    $input = $this->io->getInput();
    $output = $this->io->getOutput();
    foreach ($tasks as $task) {
      $class = $task->class;
      $taskObject = $class::create($this->command, $this->container);
      $statusPre = $taskObject->preExecute($input, $output);
      if (StatusCode::OK == $statusPre) {
        $statusExe = $taskObject->execute($input, $output);
        if (StatusCode::OK == $statusExe) {
          $taskObject->postExecute($input, $output);
        }
      }
    }
  }

  /**
   * Sorts an array of tasks.
   *
   * @param array $tasks
   *   An array of tasks.
   */
  public function sortTasks(array $tasks): array {
    uasort($tasks, function ($a, $b) {
      if ($a->weight == $b->weight) {
        return strcasecmp($a->id, $b->id);
      }
      return $a->weight - $b->weight;
    });
    return $tasks;
  }

  /**
   * Filters the task based on status.
   *
   * @param array $tasks
   *   An array of annotation tasks.
   */
  public function filterTasks(array &$tasks): array {
    $tasks = array_filter($tasks, function ($task) {
      return $task->status;
    });
    return $tasks;
  }

}
