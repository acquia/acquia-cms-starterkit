<?php

namespace AcquiaCMS\Cli\Validation;

use JsonSchema\Validator;
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
   * Validates starter-kit.
   */
  public function validateStarterKit(array $schema, array $starterkit): void {
    $validator = new Validator();
    $validator->validate($starterkit, $schema);
    if (!$validator->isValid()) {
      $errors = [];
      foreach ($validator->getErrors() as $key => $error) {
        $errors[] = "Property " . $error['property'] . " " . $error['constraint'] . " " . $error['message'];
        $this->output->writeln("<error>" . $errors[$key] . "</error>");
      }
      exit;
    }
  }

}
