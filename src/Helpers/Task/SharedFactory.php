<?php

namespace AcquiaCMS\Cli\Helpers\Task;

use AcquiaCMS\Cli\Helpers\Utility;

/**
 * Executes the user generation/collection of data.
 */
class SharedFactory {
  /**
   * Property for the generating & storing the user data.
   *
   * @var SharedFactory
   */
  private static $generatedData;

  /**
   * Handles the retrieval of the data.
   *
   * @param string $key
   *   Key for accessing user data.
   * @param string $value
   *   Value of the specified key for user data.
   *
   * @return string
   *   Returns the generated data.
   */
  public static function getData(string $key, string $value = ''): string {
    $value = self::$generatedData[$key];
    if (empty($value)) {
      $value = self::setData($key, $value);
    }
    return $value;
  }

  /**
   * Handles the storing operation of the data.
   *
   * @param string $key
   *   Key for accessing user data.
   * @param string $value
   *   Value of the specified key for user data.
   *
   * @return string
   *   Returns the array of the user data.
   */
  public static function setData($key, $value = ''): string {
    if ($key == 'password') {
      if (!isset(self::$generatedData[$key]) && empty(self::$generatedData[$key])) {
        $value = Utility::generateRandomPassword(12);
      }
    }
    return self::$generatedData[$key] = $value;
  }

}
