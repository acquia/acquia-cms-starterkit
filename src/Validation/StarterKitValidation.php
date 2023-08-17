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
   * Loop each starter-kit and send it for validation.
   */
  public function validateStarterKits(array $schema, array &$starterkits): void {
    foreach ($starterkits as $starterkit) {
      $this->validateStarterKit($schema, $starterkit);
    }
  }

  /**
   * Validates array for starter-kit.
   */
  public function validateArray($schema, $starterkit, $key): void {
    if ($schema['is_required'] ?
      ((!isset($starterkit) || empty($starterkit))  && !is_array($starterkit)) :
      (empty($starterkit) || !is_array($starterkit))) {
      $required = $schema['is_required'] ? 'is mandatory and ' : '';
      throw new AcmsCliException($this->arrayfield . ' => ' . $key . ' field ' . $required . 'should be in array format, in ' . $this->starterKitName . ' starterkit.');
    }
  }

  /**
   * Validates string for starter-kit.
   */
  public function validateString($schema, $starterkit, $key): void {
    if ($schema['is_required'] ?
      ((!isset($starterkit) || empty($starterkit))  && !is_string($starterkit)) :
      (empty($starterkit) || !is_string($starterkit))) {
      $required = $schema['is_required'] ? 'is mandatory and ' : '';
      throw new AcmsCliException($key . ' field ' . $required . 'should be in string format, in starterkit.');
    }
  }

  /**
   * Validates each key value for starter-kit.
   */
  public function validateStarterKit(array $schema, $starterkit): void {
    foreach ($schema as $key => $value) {
      if ($schema[$key]['type'] == 'arrays' && isset($schema[$key]['next_level_validation'])) {
        unset($schema[$key]['type']);
        unset($schema[$key]['is_required']);
        unset($schema[$key]['next_level_validation']);
        $this->validateArray($schema[$key], $starterkit[$key], $key);
        $this->validateStarterKit($schema[$key], $starterkit[$key]);
      }
      else {
        switch ($schema[$key]['type']) {
          case 'arrays':
            $this->validateArray($schema[$key], $starterkit[$key], $key);
            break;

          case 'strings':
            $this->arrayfield = '';
            $this->validateString($schema[$key], $starterkit[$key], $key);
            break;

          case 'default':
            throw new AcmsCliException('starterkit is not defined properly kindly fix your acms.yml file.');
        }
      }
    }
  }

}
