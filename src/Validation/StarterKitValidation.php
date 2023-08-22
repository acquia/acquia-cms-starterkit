<?php

namespace AcquiaCMS\Cli\Validation;

use AcquiaCMS\Cli\Exception\AcmsCliException;
use JsonSchema\Validator;

/**
 * Validation service to validate each key in starter kit.
 */
class StarterKitValidation {

  /**
   * Loop each starter-kit and send it for validation.
   */
  public function validateStarterKits(array $schema, array &$starterkits): void {
    $errorMessage = '';
    foreach ($starterkits as $name => $starterkit) {
      $errorMessage .= $this->validateStarterKit($schema, $starterkit, $name);
    }
    if ($errorMessage) {
      throw new AcmsCliException($errorMessage);
    }
  }

  /**
   * Validates starter-kit.
   */
  public function validateStarterKit(array $schema, array $starterkit, string $name): string {
    $validator = new Validator();
    $validator->validate($starterkit, $schema);
    if (!$validator->isValid()) {
      $errors = '';
      foreach ($validator->getErrors() as $error) {
        $errors .= " * " . $error['message'] . " in '" . $error['property'] . "' property type." . "\n";
      }
      return "- " . $name . ':' . "\n" . $errors . "\n";
    }
    return '';
  }

}
