<?php

namespace AcquiaCMS\Cli\Annotation;

/**
 * Annotation class to add task.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Task {

  /**
   * Holds tht task id.
   *
   * @var string
   *
   * @Required
   */
  public string $id;

  /**
   * Holds the weight for the task.
   *
   * @var int
   *
   * @Required
   */
  public int $weight;

  /**
   * Holds the class name for the task.
   *
   * @var string
   */
  public string $class;

  /**
   * Decides if task needs to be included or not. Default is TRUE.
   *
   * @var bool
   */
  public bool $status = TRUE;

  /**
   * Returns the task id.
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Returns the weight for the task.
   */
  public function getWeight(): int {
    return $this->weight;
  }

  /**
   * Returns the status of the task.
   */
  public function getStatus(): bool {
    return $this->status;
  }

}
