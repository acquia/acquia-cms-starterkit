<?php

namespace AcquiaCMS\Cli;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides the object for an Acquia CMS starter-kit cli.
 */
class Cli {

  /**
   * A message that gets displayed when running any starter-kit cli command.
   *
   * @var string
   */
  public $headline = "Welcome to Acquia CMS starterkit installer";

  /**
   * Holds the symfony console output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $projectDirectory;

  /**
   * Constructs an object.
   *
   * @param string $project_dir
   *   Returns an absolute path to project.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Holds the symfony console output object.
   */
  public function __construct(string $project_dir, OutputInterface $output) {
    $this->projectDirectory = $project_dir;
    $this->output = $output;
  }

  /**
   * Prints the Acquia CMS logo in terminal.
   */
  public function printLogo() :void {
    $this->output->writeln("<info>" . file_get_contents($this->getLogo()) . "</info>");
  }

  /**
   * Returns the path to Acquia CMS logo.
   */
  public function getLogo() :string {
    return $this->projectDirectory . "/assets/acquia_cms.icon.ascii";
  }

  /**
   * Prints the Acquia CMS welcome headline.
   */
  public function printHeadline() :void {
    $this->output->writeln("<fg=cyan;options=bold,underscore> " . $this->headline . "</>");
    $this->output->writeln("");
  }

  /**
   * Gets the Acquia CMS file contents.
   */
  public function getAcquiaCmsFile() :array {
    $fileContents = [];
    try {
      $fileContents = Yaml::parseFile($this->projectDirectory . '/acms/acms.yml');
    }
    catch (\Exception $e) {
      $this->output->writeln("<error>" . $e->getMessage() . "</error>");
    }
    return $fileContents;
  }

  /**
   * Returns an array of starter-kits defined in acms.yml file.
   */
  public function getStarterKits() :array {
    $fileContent = $this->getAcquiaCmsFile();
    return $fileContent['starter_kits'] ?? [];
  }

}
