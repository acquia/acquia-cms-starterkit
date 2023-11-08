<?php

namespace AcquiaCMS\Cli\FileSystem\Validator;

/**
 * Class for validating StarterKit data.
 */
class StarterKitValidator extends Validator {

  /**
   * Defines the starter_kit key.
   */
  const STARTER_KIT_KEY = "starter_kits";

  /**
   * {@inheritdoc}
   */
  public function getSchema(): array {
    return [
      'name' => 'string',
      'description' => 'string',
      'modules' => [
        "require" => "array",
        "install" => "array",
      ],
      'themes' => [
        "require" => "array",
        "install" => "array",
        "default" => "string",
        "admin" => "string",
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $data): bool {
    $this->validateSchema($data, [self::STARTER_KIT_KEY => "array"]);
    $error = $this->getError(self::STARTER_KIT_KEY);
    if ($error) {
      return FALSE;
    }
    $isValid = TRUE;
    $errors = [];
    foreach ($data[self::STARTER_KIT_KEY] as $key => $value) {
      $this->setErrors([]);
      if (!$this->validateSchema($value, $this->getSchema())) {
        $errors[$key] = $this->getErrors();
        $isValid = FALSE;
      }
    }
    $this->setErrors($errors);
    return $isValid;
  }

}
