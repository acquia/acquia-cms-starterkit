<?php

namespace AcquiaCMS\Cli\FileSystem;

use AcquiaCMS\Cli\Exception\ErrorException;
use AcquiaCMS\Cli\Exception\ListException;
use AcquiaCMS\Cli\FileSystem\Validator\StarterKitValidator;
use AcquiaCMS\Cli\FileSystem\Validator\ValidatorInterface;
use Consolidation\Config\Loader\ConfigLoader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Load configurations and fill in property values that need to be expanded.
 */
class YamlConfigLoader extends ConfigLoader {

  /**
   * {@inheritdoc}
   *
   * @throws \AcquiaCMS\Cli\Exception\ErrorException
   */
  public function load($path) {
    $this->setSourceName($path);

    // We silently skip any nonexistent config files, so that
    // clients may simply `load` all of their candidates.
    if (!file_exists($path)) {
      $this->config = [];
      return $this;
    }
    try {
      $this->config = (array) Yaml::parse(file_get_contents($path));
      $this->validate($path, $this->config);
    }
    catch (ParseException $e) {
      throw new ErrorException(
        sprintf("The file '<fg=white;options=underscore>%s</>' is invalid and contain syntax error." . PHP_EOL . "%s",
          $path,
          $e->getMessage()
        )
          );
    }
    return $this;
  }

  /**
   * Validate the given file and data.
   *
   * @param string $path
   *   The yaml file path.
   * @param array $data
   *   An array of data.
   *
   * @throws \AcquiaCMS\Cli\Exception\ListException
   */
  public function validate(string $path, array $data): bool {
    $validator = new StarterKitValidator();
    $validator->validate($data);
    if ($validator->getErrors()) {
      $message = [];
      foreach ($validator->getErrors() as $key => $error) {
        $message[$key] = $this->parseError($error);
      }
      throw new ListException(
        sprintf(
          "Please fix all errors in the file: '<fg=white;options=underscore>%s</>'",
          $path
        ),
        $message
      );
    }
    return TRUE;
  }

  /**
   * Parse the given error.
   *
   * @param array $errors
   *   An array of errors to parse.
   */
  protected function parseError(array $errors): array {
    $message = [];
    foreach ($errors as $key => $error) {
      switch ($error['type']) {
        case ValidatorInterface::KEY_NOT_PRESENT_ERROR;
          $message[] = sprintf(
            "Key '%s' not present.",
            $key,
          );
          break;

        case ValidatorInterface::INVALID_DATA_TYPE_ERROR;
          $message[] = sprintf(
            "The value for key '%s' should be '%s', but '%s' was provided.",
            $key,
            $error['expected'],
            $error['actual'],
          );
          break;
      }
    }
    return $message;
  }

}
