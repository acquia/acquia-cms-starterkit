<?php

namespace AcquiaCMS\Cli\Validation;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
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
  public function validateStarterKits(array $schema, array &$starterkits): int {
    $errorMessage = '';
    try {
      foreach ($starterkits as $name => $starterkit) {
        $errorMessage .= $this->validateStarterKit($schema, $starterkit, $name);
      }
      if ($errorMessage) {
        throw new AcmsCliException("\n" . $errorMessage . "\n");
      }
    }
    catch (AcmsCliException $e) {
      $this->output->writeln("<error>" . $e->getMessage() . "</error>");
      exit;
    }

    return StatusCodes::OK;
  }

  /**
   * Validates starter-kit.
   */
  public function validateStarterKit(array $schema, array $starterkit, string $name): ?string {
    $validator = new Validator();
    $validator->validate($starterkit, $schema);
    if (!$validator->isValid()) {
      $errors = '';
      foreach ($validator->getErrors() as $error) {
        $errors .= $error['message'] . " in '" . $error['property'] . "' property type." . "\n";
      }
      return $name . ':' . "\n" . $errors;
    }

    return '';
  }

}
