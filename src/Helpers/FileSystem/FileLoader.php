<?php

namespace AcquiaCMS\Cli\Helpers\FileSystem;

use Symfony\Component\Yaml\Yaml;

/**
 * The PHP Parser class to parse different php string/array etc.
 */
class FileLoader {

  /**
   * Holds the acms build file path.
   *
   * @var string
   */
  protected $buildFilePath;

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
   * Constructs an object.
   *
   * @param string $project_dir
   *   The project directory path.
   */
  public function __construct(string $project_dir) {
    $this->projectDirectory = $project_dir;
    $this->buildFilePath = $project_dir . "/acms/acms.yml";
  }

  /**
   * Gets the Acquia CMS file contents.
   *
   * @param string|null $path
   *   Load the given file.
   *
   * @return array
   *   Returns an array of file content.
   *
   * @throws \Exception
   */
  public function load(string $path = NULL) :array {
    $path = $path ?? $this->buildFilePath;
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

  /**
   * Returns an array of starter-kits defined in acms.yml file.
   *
   * @throws \Exception
   */
  public function getStarterKits() :array {
    $fileContent = $this->load();
    return $fileContent['starter_kits'];
  }

  /**
   * Returns an array of modules for given starter-kit.
   *
   * @param string $starter_kit
   *   User selected starter_kit.
   */
  public function &getModules(string $starter_kit): array {
    $starter_kits = $this->getStarterKits();
    return $starter_kits[$starter_kit]['modules'];
  }

  /**
   * Returns an array of themes for given starter-kit.
   *
   * @param string $starter_kit
   *   User selected starter_kit.
   */
  public function &getThemes(string $starter_kit): array {
    $starter_kits = $this->getStarterKits();
    return $starter_kits[$starter_kit]['themes'];
  }

  /**
   * Returns an array of loaded composer file.
   *
   * @throws \Exception
   */
  public function getRootComposer(): array {
    return $this->load($this->projectDirectory . "/composer.json");
  }

  /**
   * Returns an array of questions for setting keys defined in acms.yml file.
   *
   * @throws \Exception
   */
  public function getInstallerQuestions() :array {
    $fileContent = $this->load();
    return $fileContent['questions'] ?? [];
  }

  /**
   * Returns the path to Acquia CMS logo.
   */
  public function getLogo() :string {
    return $this->projectDirectory . "/assets/acquia_cms.icon.ascii";
  }

  /**
   * Function to alter modules & themes based on user response.
   *
   * @param array $answers
   *   An array of user response to questions.
   */
  public function alterModulesAndThemes(array $answers) : void {
    $starter_kits = $this->getStarterKits();
    $starter_kit = &$starter_kits[$answers['starter_kit']];
    $isContentModel = $answers['content_model'] ?? '';
    $isDemoContent = $answers['demo_content'] ?? '';
    $isDamIntegration = $answers['dam_integration'] ?? '';
    $contentModelModules = [
      'acquia_cms_article',
      'acquia_cms_page',
      'acquia_cms_event',
    ];

    // Set default theme as olivero (if not defined)
    $starter_kit['themes']['default'] = $starter_kit['themes']['default'] ?? "olivero";

    if ($isContentModel == "yes") {
      $starter_kit['modules']['require'] = array_merge($starter_kit['modules']['require'], $contentModelModules);
      $starter_kit['modules']['install'] = array_merge($starter_kit['modules']['install'], $contentModelModules);
    }
    if ($isDemoContent == "yes") {
      $demoContentModules = array_merge($contentModelModules, ['acquia_cms_starter']);
      $starter_kit['modules']['require'] = array_merge($starter_kit['modules']['require'], $demoContentModules);
      $starter_kit['modules']['install'] = array_merge($starter_kit['modules']['install'], $demoContentModules);
    }
    if ($isDamIntegration == "yes") {
      $starter_kit['modules']['require'] = array_merge($starter_kit['modules']['require'], ['acquia_cms_dam']);
      $starter_kit['modules']['install'] = array_merge($starter_kit['modules']['install'], ['acquia_cms_dam']);
    }
    $starter_kit['modules']['require'] = array_unique($starter_kit['modules']['require']);
    $starter_kit['modules']['install'] = array_unique($starter_kit['modules']['install']);
    $this->loaded[$this->buildFilePath]['starter_kits'] = $starter_kits;
  }

}
