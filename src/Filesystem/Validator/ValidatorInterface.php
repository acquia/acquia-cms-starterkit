<?php

namespace AcquiaCMS\Cli\FileSystem\Validator;

/**
 * Interface for validator class.
 */
interface ValidatorInterface {

  const KEY_NOT_PRESENT_ERROR = 1;
  const INVALID_DATA_TYPE_ERROR = 2;

  /**
   * Function to validate given data.
   *
   * @param array $data
   *   An array of input data.
   */
  public function validate(array $data): bool;

  /**
   * Function to validate schema for the input data.
   *
   * @param array $data
   *   An array of input data.
   * @param array $schema
   *   An array of schema.
   */
  public function validateSchema(array $data, array $schema): bool;

  /**
   * Function which returns the schema.
   */
  public function getSchema(): array;

  /**
   * Returns an array of errors.
   */
  public function getErrors(): array;

  /**
   * Returns an array of errors for given error type.
   *
   * @param string $type
   *   Given error type.
   */
  public function getError(string $type): array;

  /**
   * Sets an array of errors.
   *
   * @param array $errors
   *   An array of errors.
   */
  public function setErrors(array $errors): void;

}
