<?php

namespace AcquiaCMS\Cli\FileSystem;

/**
 * The folder internface.
 */
interface FolderInterface {

  /**
   * Add the given file to folder.
   *
   * @param \AcquiaCMS\Cli\FileSystem\FileInterface $file
   *   The file object.
   */
  public function add(FileInterface $file): void;

  /**
   * Remove the given file from folder.
   *
   * @param \AcquiaCMS\Cli\FileSystem\FileInterface $file
   *   The file object.
   */
  public function remove(FileInterface $file): void;

  /**
   * Return an array of files.
   */
  public function getFiles(): array;

}
