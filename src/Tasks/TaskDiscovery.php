<?php

namespace AcquiaCMS\Cli\Tasks;

use AcquiaCMS\Cli\Annotation\Task;
use AcquiaCMS\Cli\SimpleAnnotationReader;
use Symfony\Component\Console\Exception\RuntimeException;
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
   * An array of annotation directories.
   *
   * @var array
   */
  protected $directories = [];

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
    if (is_dir($project_dir . "/src/Steps")) {
      $this->addDirectory($project_dir . "/src/Steps");
    }
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
   * Returns an array of annotation directory path.
   */
  protected function getDirectories(): array {
    return $this->directories;
  }

  /**
   * Remove the given directory.
   *
   * @param string $dir
   *   Given directory path to remove.
   */
  public function removeDirectory(string $dir): void {
    if (isset($this->directories[$dir])) {
      unset($this->directories[$dir]);
    }
  }

  /**
   * Adds the annotation directory.
   *
   * @param string $dir
   *   Directory to look for annotation task.
   */
  public function addDirectory(string $dir): void {
    if (!is_dir($dir)) {
      throw new RuntimeException(sprintf("The directory at path '%s' doesn't exist", $dir));
    }
    if (!isset($this->directories[$dir])) {
      $this->directories[$dir] = $dir;
    }
  }

  /**
   * Discovers workers.
   *
   * @throws \Exception
   */
  private function discoverTasks(): void {
    $directories = $this->getDirectories();
    $annotations = [];
    foreach ($directories as $directory) {
      $annotations = array_merge($annotations, $this->getAnnotations($directory));
    }
    $this->tasks = $annotations;
  }

  /**
   * Returns an array of annotations.
   *
   * @param string $directory
   *   Directory to search for annotations.
   *
   * @throws \Exception
   */
  protected function getAnnotations(string $directory): array {
    $finder = new Finder();
    $finder->files()
      ->in($directory)
      ->name(['*Task.php'])
      ->depth(0);

    $annotations = [];
    foreach ($finder as $file) {
      $classNamespace = str_replace($this->projectDir . "/src/", "AcquiaCMS\Cli\\", $file->getPath());
      $classNamespace = str_replace("/", "\\", $classNamespace);
      $class = $classNamespace . "\\" . $file->getBasename('.php');
      if (class_exists($class)) {
        $classObj = new \ReflectionClass($class);
        $annotation = $this->annotationReader->getClassAnnotation($classObj, Task::class);
        if (!$annotation) {
          continue;
        }
        $annotation->class = $class;
        $annotations[$annotation->getId()] = $annotation;
      }
      else {
        throw new \Exception("Class doesn't exist: '" . $class . "'.");
      }
    }
    return $annotations;
  }

}
