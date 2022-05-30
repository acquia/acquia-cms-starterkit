<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;

/**
 * Run the drush command to enable Drupal modules.
 */
class EnableModules {

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  protected $drushCommand;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush
   *   Holds the drush command class object.
   */
  public function __construct(Drush $drush) {
    $this->drushCommand = $drush;
  }

  /**
   * Run the drush commands to enable Drupal modules.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []) :int {
    $modules = JsonParser::installPackages($args['modules']['install']);

    // Enable: acquia_cms_site_studio_content (instead acquia_cms_starter), if
    // acquia_cms_starter module is in the list of module installation and any
    // content model module is not available in the list of module installation
    // like acquia_cms_article, acquia_cms_page etc.
    if (in_array('acquia_cms_starter', $modules) && !in_array('acquia_cms_article', $modules)) {
      $key = array_search('acquia_cms_starter', $modules);
      $modules[$key] = 'acquia_cms_site_studio_content';
    }
    $command = array_merge(["en", "--yes"], $modules);
    $args['keys']['UNSET_COHESION_SYNC'] = 1;
    $this->drushCommand->prepare($command)->run($args['keys']);
    return StatusCodes::OK;
  }

}
