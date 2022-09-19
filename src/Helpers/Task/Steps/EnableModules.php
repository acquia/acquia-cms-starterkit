<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Enum\StatusCodes;
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
    $modules = $args['modules'];
    $options = ["en", "--yes"];
    $is_low_code = FALSE;
    if (($key = array_search("acquia_cms_site_studio", $modules)) !== FALSE) {
      $is_low_code = TRUE;
      unset($modules[$key]);
    }

    $enable_modules_command = array_merge($options, $modules);
    $args['keys']['UNSET_COHESION_SYNC'] = 1;
    $this->drushCommand->prepare($enable_modules_command)->run($args['keys']);
    if ($is_low_code) {
      $enable_site_studio_command = array_merge($options, ['acquia_cms_site_studio']);
      $this->drushCommand->prepare($enable_site_studio_command)->run($args['keys']);
    }

    return StatusCodes::OK;
  }

}
