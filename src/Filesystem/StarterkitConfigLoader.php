<?php

namespace AcquiaCMS\Cli\FileSystem;

use Consolidation\Config\Config;

/**
 * Load configuration, and fill in property values that need to be expanded.
 */
class StarterkitConfigLoader {

  /**
   * Config.
   *
   * @var \Consolidation\Config\Config
   */
  protected $config;

  /**
   * Loader.
   *
   * @var \AcquiaCMS\Cli\FileSystem\YamlConfigLoader
   */
  protected $loader;

  /**
   * Processor.
   *
   * @var \AcquiaCMS\Cli\FileSystem\YamlConfigProcessor
   */
  protected $processor;

  /**
   * Constructs StarterkitConfigLoader object.
   *
   * @param string $default_path
   *   Given default_path for config.
   *
   * @throws \AcquiaCMS\Cli\Exception\ErrorException
   */
  public function __construct(string $default_path) {
    $this->config = new Config();
    $this->loader = new YamlConfigLoader();
    $expander = new YamlConfigExpander();
    $this->processor = new YamlConfigProcessor($expander);
    $this->add($default_path);
  }

  /**
   * Adds the given yaml file to extend.
   *
   * @param string $path
   *   Add the given StarterKit file.
   *
   * @throws \AcquiaCMS\Cli\Exception\ErrorException
   */
  public function add(string $path): StarterkitConfigLoader {
    $this->processor->add($this->config->export());
    $this->processor->extend($this->loader->load($path));
    return $this;
  }

  /**
   * Process config.
   */
  public function processConfigFiles(): StarterkitConfigLoader {
    $this->config->replace($this->processor->export());
    return $this;
  }

  /**
   * Return an array of content.
   */
  public function getContent(): array {
    return $this->config->export();
  }

}
