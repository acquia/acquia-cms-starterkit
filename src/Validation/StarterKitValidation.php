<?php

namespace AcquiaCMS\Cli\Validation;

use AcquiaCMS\Cli\Exception\AcmsCliException;
use JsonSchema\Validator;

/**
 * Validation service to validate each key in starter kit.
 */
class StarterKitValidation {

  /**
   * Loop each starter-kit for validation.
   *
   * @param array $schema
   *   Schema json to validate starter-kit.
   * @param array $starterkits
   *   List of starter-kits.
   */
  public function validateStarterKits(array $schema, array &$starterkits): void {
    $errorMessage = '';
    foreach ($starterkits as $name => $starterkit) {
      $validator = new Validator();
      $validator->validate($starterkit, $schema);
      if (!$validator->isValid()) {
        $errorMessage .= $this->prepareErrorMessage($validator->getErrors(), $name);
      }
    }
    if ($errorMessage) {
      throw new AcmsCliException($errorMessage);
    }
  }

  /**
   * Prepare error message for starter-kit.
   *
   * @param array $validator
   *   Error message array.
   * @param string $name
   *   Starter-kit name.
   *
   * @return string
   *   Error message format.
   */
  public function prepareErrorMessage(array $validator, string $name): string {
    $errors = '';
    foreach ($validator as $error) {
      $errors .= " * " . $error['message'] . " in '" . $error['property'] . "' property type." . "\n";
    }
    return "- " . $name . ':' . "\n" . $errors . "\n";
  }

}
