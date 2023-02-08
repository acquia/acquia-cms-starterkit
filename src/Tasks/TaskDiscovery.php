<?php

namespace AcquiaCMS\Cli\Tasks;

use AcquiaCMS\Cli\Annotation\Task;
use AcquiaCMS\Cli\SimpleAnnotationReader;
use Symfony\Component\Finder\Finder;

/**
 * Class for task discovery.
 */
class TaskDiscovery {

  /**
   * Holds an annotation reader object.
   *
   * @var \AcquiaCMS\Cli\SimpleAnnotationReader
   */
  private $annotationReader;

  /**
   * The Kernel root directory.
   *
   * @var string
   */
  private $projectDir;

  /**
   * Holds an array of tasks.
   *
   * @var array
   */
  private $tasks = [];

  /**
   * WorkerDiscovery constructor.
   *
   * @param string $project_dir
   *   The project directory path.
   */
  public function __construct(string $project_dir) {
    $this->annotationReader = new SimpleAnnotationReader();
    $this->annotationReader->addNamespace("AcquiaCMS\Cli\Annotation");
    $this->projectDir = $project_dir;
  }

  /**
   * Returns all the workers.
   */
  public function getTasks(): array {
    if (!$this->tasks) {
      $this->discoverTasks();
    }

    return $this->tasks;
  }

  /**
   * Returns an annotation directory path.
   */
  protected function getAnnotationsDirectory(): string {
    return $this->projectDir . "/src/Steps";
  }

  /**
   * Discovers workers.
   */
  private function discoverTasks(): void {
    $finder = new Finder();
    $finder->files()
      ->in($this->getAnnotationsDirectory())
      ->name(['*Task.php'])
      ->depth(0);

    $annotations = [];
    foreach ($finder as $file) {
      $classNamespace = str_replace($this->projectDir . "/src/", "AcquiaCMS\Cli\\", $file->getPath());
      $class = $classNamespace . "\\" . $file->getBasename('.php');
      $classObj = new \ReflectionClass($class);
      $annotation = $this->annotationReader->getClassAnnotation($classObj, Task::class);
      if (!$annotation) {
        continue;
      }
      $annotation->class = $class;
      $annotations[$annotation->getId()] = $annotation;
    }
    $this->tasks = $annotations;
  }

}
