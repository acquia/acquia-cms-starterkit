<?php

namespace AcquiaCMS\Cli\Tasks;

use AcquiaCMS\Cli\Enum\StatusCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
  private $discovery;

  /**
   * Holds an input object.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  protected $input;

  /**
   * Holds an output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

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
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Tasks\TaskDiscovery $discovery
   *   The task discovery object.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container object.
   */
  public function __construct(TaskDiscovery $discovery, ContainerInterface $container) {
    $this->container = $container;
    $this->discovery = $discovery;
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
   * Initialize an object from symfony command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   An input object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   An output object.
   * @param \Symfony\Component\Console\Command\Command $command
   *   A symfony command object.
   */
  public function configure(InputInterface $input, OutputInterface $output, Command $command): void {
    $this->input = $input;
    $this->output = $output;
    $this->command = $command;
  }

  /**
   * Execute the tasks.
   *
   * @throws \Exception
   */
  public function executeTasks(): void {
    if (!class_exists($this->input::class) || !class_exists($this->input::class) || !class_exists($this->input::class)) {
      throw new \Exception("You must first call method: \$taskManagerObject->configure(\$input, \$output, \$command)");
    }
    $tasks = $this->getTasks();
    foreach ($tasks as $task) {
      $class = $task->class;
      $taskObject = $class::create($this->command, $this->container);
      $statusPre = $taskObject->preExecute($this->input, $this->output);
      if (StatusCode::OK == $statusPre) {
        $statusExe = $taskObject->execute($this->input, $this->output);
        if (StatusCode::OK == $statusExe) {
          $taskObject->postExecute($this->input, $this->output);
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
