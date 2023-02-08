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

}
