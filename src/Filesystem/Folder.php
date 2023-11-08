<?php

namespace AcquiaCMS\Cli\FileSystem;

/**
 * Class to manage folder.
 */
class Folder implements FolderInterface {

  /**
   * Holds an array of files for given folder.
   *
   * @var array
   */
  private $files;

  /**
   * Construct folder object.
   */
  public function __construct() {
    $this->files = [];
  }

  /**
   * {@inheritdoc}
   */
  public function add(FileInterface $file): void {
    $this->files[] = $file;
  }

  /**
   * {@inheritdoc}
   */
  public function remove(FileInterface $file): void {
    $index = array_search($file, $this->files, TRUE);
    if ($index !== FALSE) {
      unset($this->files[$index]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFiles(): array {
    return $this->files;
  }

}
