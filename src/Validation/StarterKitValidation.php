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
   * Schema for starter-kit.
   */
  public function validateStarterKitSchema(array &$starterkit): array {
    $schema = [
      'name' => [
        'type' => 'string',
        'is_required' => TRUE,
      ],
      'description' => [
        'type' => 'string',
        'is_required' => TRUE,
      ],
      'modules' => [
        'require' => [
          'type' => 'array',
          'is_required' => FALSE,
        ],
        'install' => [
          'type' => 'array',
          'is_required' => FALSE,
        ],
        'type' => 'array',
        'is_required' => FALSE,
      ],
      'themes' => [
        'require' => [
          'type' => 'array',
          'is_required' => FALSE,
        ],
        'install' => [
          'type' => 'array',
          'is_required' => FALSE,
        ],
        'admin' => [
          'type' => 'string',
          'is_required' => FALSE,
        ],
        'default' => [
          'type' => 'string',
          'is_required' => FALSE,
        ],
        'type' => 'array',
        'is_required' => FALSE,
      ],
    ];
    $this->validateStarterKit($schema, $starterkit);

    return $schema;
  }

  /**
   * Validates each key value for starter-kit.
   */
  public function validateStarterKit(array $schema, $starterkit): array {
    foreach ($schema as $key => $value) {
      if (is_array($schema[$key]) && $schema[$key]['type'] === 'array') {
        $this->validateStarterKit($schema[$key], $starterkit[$key]);
      }
      else {
        switch ($schema[$key]['type']) {
          case 'array':
            if (isset($starterkit[$key]) && !is_array($starterkit[$key])) {
              throw new AcmsCliException($key . ' should be in array format.');
            }
            break;

          case 'string':
            if (isset($starterkit[$key]) && !is_string($starterkit[$key])) {
              throw new AcmsCliException($key . ' field is mandatory and should be in string format.');
            }
            break;

          case 'default':
            throw new AcmsCliException('starterkit is not defined properly kindly fix your acms.yml file.');
        }
      }
    }

    return $starterkit;
  }

}
