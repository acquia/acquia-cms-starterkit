<?php

namespace AcquiaCMS\Cli\FileSystem\Validator;

/**
 * Class to validate data for given schema data.
 */
abstract class Validator implements ValidatorInterface {

  /**
   * Holds an array of errors.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * {@inheritdoc}
   */
  public function validateSchema(array $data, array $schema, string $previousKey = ""): bool {
    $isValid = TRUE;
    foreach ($schema as $key => $dataType) {
      $index = $previousKey ? "$previousKey.$key" : $key;
      if (!array_key_exists($key, $data)) {
        $this->errors[$index] = [
          "type" => self::KEY_NOT_PRESENT_ERROR,
        ];
        // The key is not present in the data, skip validation.
        continue;
      }
      $value = $data[$key];
      if (is_array($dataType)) {
        if (!$this->validateSchema($value, $dataType, $key)) {
          $isValid = FALSE;
        }
      }
      else {
        if (!$this->validateDataType($value, $dataType)) {
          $this->errors[$index] = [
            "type" => self::INVALID_DATA_TYPE_ERROR,
            "actual" => gettype($value),
            "expected" => $dataType,
          ];
          $isValid = FALSE;
        }
      }
    }

    return $isValid;
  }

  /**
   * {@inheritdoc}
   */
  protected function validateDataType(mixed $value, string $dataType): bool {
    switch ($dataType) {
      case 'string':
        return is_string($value);

      case 'integer':
        return is_int($value);

      case 'float':
        return is_float($value);

      case 'boolean':
        return is_bool($value);

      case 'array':
        return is_array($value);

      default:
        return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $data): bool {
    return $this->validateSchema($data, $this->getSchema());
  }

  /**
   * {@inheritdoc}
   */
  public function getError(string $type): array {
    return array_key_exists($type, $this->errors) ? $this->errors[$type] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * {@inheritdoc}
   */
  public function setErrors(array $errors): void {
    $this->errors = $errors;
  }

}
