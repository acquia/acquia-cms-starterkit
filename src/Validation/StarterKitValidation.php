<?php

namespace AcquiaCMS\Cli\Validation;

use AcquiaCMS\Cli\Exception\AcmsCliException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Validation service to validate each key in starter kit.
 */
class StarterKitValidation {

  /**
   * Holds the symfony console output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * Constructs an object.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Returns an absolute root path to project.
   */
  public function __construct(
    OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * Validates each key value for starter-kit.
   */
  public function validateStarterKit(array &$starterkits): array {
    foreach ($starterkits as $key => $starterkit) {
      // Validate starterkit name.
      if (empty($starterkit['name']) || !is_string($starterkit['name'])) {
        throw new AcmsCliException('Name field is mandatory and should be in string format, in ' . $key . ' starterkit.');
      }

      // Validate starterkit description.
      if (empty($starterkit['description']) || !is_string($starterkit['description'])) {
        throw new AcmsCliException('Description field is mandatory and should be in string format, in ' . $key . ' starterkit.');
      }

      // Validate modules section.
      if (array_key_exists('modules', $starterkit) && !isset($starterkit['modules'])) {
        throw new AcmsCliException('Modules field/section is not in array format, in ' . $key . ' starterkit.');
      }

      // Validate modules require section.
      if (isset($starterkit['modules']) &&
      (array_key_exists('require', $starterkit['modules']) &&
      (!isset($starterkit['modules']['require']) || !is_array($starterkit['modules']['require'])))) {
        throw new AcmsCliException('Modules require field/section is not in array format, in ' . $key . ' starterkit.');
      }

      // Validate modules install section.
      if (isset($starterkit['modules']) &&
      (array_key_exists('install', $starterkit['modules']) &&
      (!isset($starterkit['modules']['install']) || !is_array($starterkit['modules']['install'])))) {
        throw new AcmsCliException('Modules install field/section is not in array format, in ' . $key . ' starterkit.');
      }

      // Validate themes section.
      if (array_key_exists('themes', $starterkit) && !isset($starterkit['themes'])) {
        throw new AcmsCliException('Themes field/section is not in array format, in ' . $key . ' starterkit.');
      }

      // Validate themes require section.
      if (isset($starterkit['themes']) &&
      (array_key_exists('require', $starterkit['themes']) &&
      (!isset($starterkit['themes']['require']) || !is_array($starterkit['themes']['require'])))) {
        throw new AcmsCliException('Themes require field/section is not in array format, in ' . $key . ' starterkit.');
      }

      // Validate themes install section.
      if (isset($starterkit['themes']) &&
      (array_key_exists('install', $starterkit['themes']) &&
      (!isset($starterkit['themes']['install']) || !is_array($starterkit['themes']['install'])))) {
        throw new AcmsCliException('Themes install field/section is not in array format, in ' . $key . ' starterkit.');
      }

      // Validate themes admin section.
      if (isset($starterkit['themes']) &&
      (array_key_exists('admin', $starterkit['themes']) && !is_string($starterkit['themes']['admin']))) {
        throw new AcmsCliException('Themes admin field/section is not set or doesnt contain string value, in ' . $key . ' starterkit.');
      }

      // Validate themes default section.
      if (!is_string($starterkit['themes']['default'])) {
        throw new AcmsCliException('Themes default field/section is not set or doesnt contain string value, in ' . $key . ' starterkit.');
      }
    }

    return $starterkits;
  }

}
