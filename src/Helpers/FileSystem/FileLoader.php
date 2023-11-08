<?php

namespace AcquiaCMS\Cli\Helpers\FileSystem;

use Symfony\Component\Yaml\Yaml;

/**
 * The PHP Parser class to parse different php string/array etc.
 */
class FileLoader {

  /**
   * Holds the project directory path.
   *
   * @var string
   */
  protected $projectDirectory;

  /**
   * Holds the yaml file contents.
   *
   * @var array
   */
  protected $loaded = [];

  /**
   * Gets the Acquia CMS file contents.
   *
   * @param string $path
   *   Load the given file.
   *
   * @return array
   *   Returns an array of file content.
   *
   * @throws \Exception
   */
  public function load(string $path): array {
    if (isset($this->loaded[$path])) {
      return $this->loaded[$path];
    }
    try {
      $this->loaded[$path] = Yaml::parseFile($path);
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
    return $this->loaded[$path];
  }

}
