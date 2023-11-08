<?php

namespace AcquiaCMS\Cli\Helpers\Package;

/**
 * Class for base package.
 */
class BasePackage {

  /**
   * An array of required packages.
   *
   * @var array
   */
  protected $require = [];

  /**
   * An array of packages to install.
   *
   * @var array
   */
  protected $install = [];

  /**
   * Add the given package to an array of require packages.
   */
  public function addToRequire(string $componentName): void {
    if (!in_array($componentName, $this->require)) {
      $this->require[] = $componentName;
    }
  }

  /**
   * Add the given package to an array of install packages.
   */
  public function addToInstall(string $componentName): void {
    if (!in_array($componentName, $this->install)) {
      $this->install[] = $componentName;
    }
  }

  /**
   * Return an array of require and install packages.
   */
  public function toArray(): array {
    return [
      "require" => $this->require,
      "install" => $this->install,
    ];
  }

}
