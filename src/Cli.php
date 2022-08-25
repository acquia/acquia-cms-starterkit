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
  public $headline = "Welcome to Acquia CMS Starter Kit installer";

  /**
   * Holds the symfony console output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * An absolute directory path to project.
   *
   * @var string
   */
  protected $projectDirectory;

  /**
   * An absolute root directory path of the project.
   *
   * @var string
   */
  protected $rootDirectory;

  /**
   * Constructs an object.
   *
   * @param string $project_dir
   *   Returns an absolute path to project.
   * @param string $root_dir
   *   Returns an absolute root path to project.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Holds the symfony console output object.
   */
  public function __construct(string $project_dir, string $root_dir, OutputInterface $output) {
    $this->projectDirectory = $project_dir;
    $this->rootDirectory = $root_dir;
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

  /**
   * Returns an array of questions for setting keys defined in acms.yml file.
   */
  public function getInstallerQuestions() :array {
    $fileContent = $this->getAcquiaCmsFile();
    return $fileContent['questions'] ?? [];
  }

  /**
   * Returns the composer.json string.
   *
   * @return string
   *   Returns the contents of composer.json file exist at root directory.
   */
  public function getRootComposer() :string {
    $rootComposerJson = $this->rootDirectory . "/composer.json";
    if (!file_exists($rootComposerJson)) {
      return "";
    }
    return file_get_contents($rootComposerJson);
  }

  /**
   * Function to alter modules & themes based on user response.
   *
   * @param array $starterKit
   *   An array of starter-kit.
   * @param array $userInputValues
   *   A user input values for questions.
   *
   * @return array
   *   Returns an array of altered starter-kit.
   */
  public function alterModulesAndThemes(array &$starterKit, array $userInputValues) :array {
    $isContentModel = $userInputValues['content_model'] ?? '';
    $isDemoContent = $userInputValues['demo_content'] ?? '';
    $contentModelModules = [
      'acquia_cms_article',
      'acquia_cms_page',
      'acquia_cms_event',
    ];

    // Set default theme as olivero (if not defined)
    $starterKit['themes']['default'] = $starterKit['themes']['default'] ?? "olivero";

    if ($isContentModel == "yes") {
      $starterKit['modules']['require'] = array_merge($starterKit['modules']['require'], $contentModelModules);
      $starterKit['modules']['install'] = array_merge($starterKit['modules']['install'], $contentModelModules);
    }
    if ($isDemoContent == "yes") {
      $demoContentModules = array_merge($contentModelModules, ['acquia_cms_starter']);
      $starterKit['modules']['require'] = array_merge($starterKit['modules']['require'], $demoContentModules);
      $starterKit['modules']['install'] = array_merge($starterKit['modules']['install'], $demoContentModules);
    }
    $starterKit['modules']['require'] = array_unique($starterKit['modules']['require']);
    $starterKit['modules']['install'] = array_unique($starterKit['modules']['install']);
    return $starterKit;
  }

}
