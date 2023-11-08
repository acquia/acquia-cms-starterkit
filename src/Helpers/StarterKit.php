<?php

namespace AcquiaCMS\Cli\Helpers;

use AcquiaCMS\Cli\Helpers\Package\Module;
use AcquiaCMS\Cli\Helpers\Package\Theme;

/**
 * Class for StarterKit.
 */
class StarterKit {

  /**
   * Given StarterKit name.
   *
   * @var string
   */
  protected $name;

  /**
   * Given StarterKit description.
   *
   * @var string
   */
  protected $description;

  /**
   * Holds the module object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Package\Module
   */
  protected $modules;

  /**
   * Holds the theme object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Package\Theme
   */
  protected $themes;

  /**
   * Holds the default theme for the StarterKit.
   *
   * @var string
   */
  protected $defaultTheme;

  /**
   * Holds the admin theme for the StarterKit.
   *
   * @var string
   */
  protected $adminTheme;

  /**
   * Holds the unique id for the StarterKit.
   *
   * @var string
   */
  protected $id;

  /**
   * Defines the default theme to set, when theme is not provided.
   */
  const DEFAULT_THEME = "olivero";

  /**
   * Defines the admin theme to set, when theme is not provided.
   */
  const DEFAULT_ADMIN_THEME = "acquia_claro";

  /**
   * Construct the StarterKit object.
   *
   * @param string $id
   *   The unique id for the StarterKit.
   * @param array $data
   *   An array of input data to create StarterKit object.
   */
  public function __construct(string $id, array $data) {
    $this->name = $data['name'] ?? "";
    $this->description = $data['description'] ?? "";
    $this->defaultTheme = $data['themes']['default'] ?? self::DEFAULT_THEME;
    $this->adminTheme = $data['themes']['admin'] ?? self::DEFAULT_ADMIN_THEME;
    $this->id = $id;
    $this->modules = new Module();
    $this->themes = new Theme();
  }

  /**
   * Return the unique id for the StarerKit.
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Return the name of the StarerKit.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Return the description for the StarerKit.
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Return the admin theme for the StarerKit.
   */
  public function getAdminTheme(): string {
    return $this->adminTheme;
  }

  /**
   * Return the default theme for the StarerKit.
   */
  public function getDefaultTheme(): string {
    return $this->defaultTheme;
  }

  /**
   * Return an array of modules for the StarerKit.
   */
  public function getModules(): array {
    return $this->modules->toArray();
  }

  /**
   * Return an array of themes for the StarerKit.
   */
  public function getThemes(): array {
    return $this->themes->toArray();
  }

  /**
   * Add the given array of modules.
   *
   * @param string $type
   *   Given type of module. Ex: require or install.
   * @param array $modules
   *   An array of modules to add in given type.
   */
  public function addModules(string $type, array $modules): void {
    switch ($type) {
      case "require":
        foreach ($modules as $module) {
          $this->modules->addToRequire($module);
        }
        break;

      case "install":
        foreach ($modules as $module) {
          $this->modules->addToInstall($module);
        }
        break;
    }

  }

  /**
   * Add the given array of themes.
   *
   * @param string $type
   *   Given type of theme. Ex: require or install.
   * @param array $themes
   *   An array of themes to add in given type.
   */
  public function addThemes(string $type, array $themes): void {
    switch ($type) {
      case "require":
        foreach ($themes as $theme) {
          $this->themes->addToRequire($theme);
        }
        break;

      case "install":
        foreach ($themes as $theme) {
          $this->themes->addToInstall($theme);
        }
        break;
    }
  }

  /**
   * Convert the StarterKit object to array.
   */
  public function toArray(): array {
    $data = [
      "name" => $this->getName(),
      "description" => $this->getDescription(),
      "modules" => $this->modules->toArray(),
      "themes" => $this->themes->toArray(),
    ];
    $data["themes"]["admin"] = $this->getAdminTheme();
    $data["themes"]["default"] = $this->getDefaultTheme();
    return $data;
  }

}
