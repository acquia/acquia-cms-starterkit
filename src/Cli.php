<?php

namespace AcquiaCMS\Cli;

use AcquiaCMS\Cli\Validation\StarterKitValidation;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
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
   * User selected bundle.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * Starter-kit validator.
   *
   * @var \AcquiaCMS\Cli\Validation\StarterKitValidation
   */
  protected $starterKitValidation;

  /**
   * Constructs an object.
   *
   * @param string $project_dir
   *   Returns an absolute path to project.
   * @param string $root_dir
   *   Returns an absolute root path to project.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Holds the symfony console output object.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   * @param \AcquiaCMS\Cli\Validation\StarterKitValidation $starter_kit_validation
   *   A service to validate starter-kit.
   */
  public function __construct(
    string $project_dir,
    string $root_dir,
    OutputInterface $output,
    ContainerInterface $container,
    StarterKitValidation $starter_kit_validation) {
    $this->projectDirectory = $project_dir;
    $this->rootDirectory = $root_dir;
    $this->output = $output;
    $this->filesystem = $container->get(Filesystem::class);
    $this->starterKitValidation = $starter_kit_validation;
  }

  /**
   * Prints the Acquia CMS logo in terminal.
   */
  public function printLogo(): void {
    $this->output->writeln("<info>" . file_get_contents($this->getLogo()) . "</info>");
  }

  /**
   * Returns the path to Acquia CMS logo.
   */
  public function getLogo(): string {
    return $this->projectDirectory . "/assets/acquia_cms.icon.ascii";
  }

  /**
   * Prints the Acquia CMS welcome headline.
   */
  public function printHeadline(): void {
    $this->output->writeln("<fg=cyan;options=bold,underscore> " . $this->headline . "</>");
    $this->output->writeln("");
  }

  /**
   * Returns an array of information defined in provided file.
   *
   * @param string $file_path
   *   File name to to collect information.
   *
   * @return array
   *   Retuen the file content.
   */
  public function getAcquiaCmsFile(string $file_path): array {
    $fileContents = [];
    try {
      $fileContents = Yaml::parseFile($file_path);
    }
    catch (\Exception $e) {
      $this->output->writeln("<error>" . $e->getMessage() . "</error>");
    }

    // Return filecontent, if file is blank then return empty array.
    return $fileContents ?? [];
  }

  /**
   * Returns an array of starter-kits defined in acms.yml file.
   *
   * @param string $type
   *   The param to identify which data to return.
   */
  public function getStarterKitsAndQuestions(string $type = ''): array {
    $defaultStarterkits = $this->getAcquiaCmsFile($this->projectDirectory . '/acms/acms.yml');
    $starterkits = $defaultStarterkits['starter_kits'];
    $questions = $defaultStarterkits['questions'];
    // Check if user defined starterkit file exist in root directory.
    if ($this->filesystem->exists($this->rootDirectory . '/acms/acms.yml')) {
      $userDefinedStarterkitsAndQuestions = $this->getAcquiaCmsFile($this->rootDirectory . '/acms/acms.yml');
      // Check if starter_kits existis else assign empty array.
      $userDefinedStarterkits = $userDefinedStarterkitsAndQuestions['starter_kits'] ?? [];
      // Check if starter_kits existis else assign empty array.
      $userDefinedQuestions = $userDefinedStarterkitsAndQuestions['questions'] ?? [];
      // Merge default and user defined starterkits.
      $starterkits = array_merge($starterkits, $userDefinedStarterkits);
      // Merge default and user defined questions.
      $questions = array_merge($questions, $userDefinedQuestions);
    }
    if ($type == 'starterkits') {
      return $starterkits;
    }
    elseif ($type == 'questions') {
      return $questions;
    }

    // Send each starterkit for validation.
    $schema = $this->getAcquiaCmsFile($this->projectDirectory . '/acms/schema.json');
    $this->starterKitValidation->validateStarterKits($schema, $starterkits);

    // Return starterkit list.
    return [
      'starter_kits' => $starterkits,
      'questions' => $questions,
    ];
  }

  /**
   * Returns an array of starter-kits defined in acms.yml file.
   */
  public function getStarterKits(): array {
    // Return starterkit list.
    return $this->getStarterKitsAndQuestions('starterkits');
  }

  /**
   * Returns an array of starter-kits information from build.yml file.
   *
   * @param string $site_uri
   *   The site uri.
   */
  public function getBuildInformtaion(string $site_uri): array {
    $default_file_path = $this->projectDirectory . '/acms/build.yml';
    $fileContents = [];
    // Read build.yml file from root directory.
    if ($this->filesystem->exists($this->rootDirectory . '/acms/build.yml')) {
      $fileContents = $this->getAcquiaCmsFile($this->rootDirectory . '/acms/build.yml');
      $fileContents = $fileContents['sites'][$site_uri] ?? $this->getAcquiaCmsFile($default_file_path)['sites']['default'];
    }
    else {
      $fileContents = $this->getAcquiaCmsFile($default_file_path)['sites']['default'];
    }
    return $fileContents ?? [];
  }

  /**
   * Returns an array of questions for setting keys defined in acms.yml file.
   */
  public function getInstallerQuestions(string $question_type): array {
    $fileContent = $this->getStarterKitsAndQuestions('questions');
    return $fileContent[$question_type] ?? [];
  }

  /**
   * Returns the composer.json string.
   *
   * @return string
   *   Returns the contents of composer.json file exist at root directory.
   */
  public function getRootComposer(): string {
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
  public function alterModulesAndThemes(array &$starterKit, array $userInputValues): array {
    $isContentModel = $userInputValues['content-model'] ?? '';
    $isDemoContent = $userInputValues['demo-content'] ?? '';
    $isDamIntegration = $userInputValues['dam-integration'] ?? '';
    $isGdprIntegration = $userInputValues['gdpr-integration'] ?? '';
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
    if ($isDamIntegration == "yes") {
      $starterKit['modules']['require'] = array_merge($starterKit['modules']['require'], ['acquia_cms_dam']);
      $starterKit['modules']['install'] = array_merge($starterKit['modules']['install'], ['acquia_cms_dam']);
    }
    if ($isGdprIntegration == "yes") {
      $gdprModules = ['gdpr', 'eu_cookie_compliance', 'gdpr_fields'];
      $starterKit['modules']['require'] = array_merge($starterKit['modules']['require'], $gdprModules);
      $starterKit['modules']['install'] = array_merge($starterKit['modules']['install'], $gdprModules);
    }
    $starterKit['modules']['require'] = array_unique($starterKit['modules']['require']);
    $starterKit['modules']['install'] = array_values(array_unique($starterKit['modules']['install']));
    return $starterKit;
  }

  /**
   * Function to get options for acms starterkit questions.
   *
   * @param string $command_type
   *   Must be build/install/both.
   *
   * @return array
   *   List of options.
   */
  public function getOptions(string $command_type = ''): array {
    $questions = $this->getStarterKitsAndQuestions("questions");
    $options = $output = [];
    // $questions = $starterkitsQuestions['questions'];
    if ($command_type === 'build' || $command_type === 'install') {
      $options = $questions[$command_type];
    }
    else {
      $options = array_merge($questions['build'], $questions['install']);
    }
    foreach ($options as $key => $value) {
      $output[$key] = [
        'description' => $value['question'],
        'default_value' => $value['default_value'] ?? '',
        'startekit_name' => $value['dependencies']['starter_kits'],
      ];
    }

    return $output;
  }

  /**
   * Get question options based on starterkit.
   *
   * @param string $command_type
   *   Command type: install|build.
   * @param array $args
   *   List of input options.
   * @param string|null $starterkit
   *   Starterkit name.
   *
   * @return array
   *   List of filtered options.
   */
  public function filterOptionsByStarterKit(string $command_type, array $args, ?string $starterkit = ''): array {
    $getQuestions = $this->getInstallerQuestions($command_type);
    $output = [];
    foreach ($getQuestions as $key => $value) {
      $dependencyStarterkit = $value['dependencies']['starter_kits'];
      if (!empty($starterkit) &&
      ($dependencyStarterkit == $starterkit ||
      strpos($dependencyStarterkit, substr($starterkit, 11)))) {

        if (isset($value['default_value'])) {
          if (strripos($starterkit, 'headless') && $args["enable-nextjs-app"] === "no") {
            unset($args['nextjs-app-site-url']);
            unset($args['nextjs-app-site-name']);
            unset($args['nextjs-app-env-file']);
          }
          else {
            $arg = 'enable-' . $key;
            if (isset($args[$arg]) && $args[$arg] !== 'no') {
              $output[$arg] = $args[$arg];
            }
            else {
              $output[$key] = $args[$key];
            }
          }
        }
        else {
          if (in_array($key, array_keys($args))) {
            $output[$key] = $args[$key];
          }
        }
      }
    }

    return $output;
  }

}
