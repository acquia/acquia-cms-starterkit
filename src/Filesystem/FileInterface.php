<?php

namespace AcquiaCMS\Cli\FileSystem;

use Symfony\Component\Finder\SplFileInfo;

/**
 * The file interface.
 */
interface FileInterface {

  /**
   * Function to validate file.
   */
  public function validate(): bool;

  /**
   * Holds an array file content.
   */
  public function getContent(): array;

  /**
   * Return the file info object.
   */
  public function getFile(): SplFileInfo;

  /**
   * Return the type of file.
   */
  public function getType(): string;

}
