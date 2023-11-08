<?php

namespace AcquiaCMS\Cli\Helpers;

/**
 * Different method for ArrayHelper class.
 */
class ArrayHelper {

  /**
   * Recursively merge the data.
   *
   * @param array $default
   *   An array of default data.
   * @param array $new
   *   An array of data to merge and/or override.
   */
  public static function mergeRecursive(array $default, array $new): array {
    $result = [];
    foreach ($default as $key => $value) {
      if (is_int($key)) {
        return $new;
      }
      if (is_array($value) && isset($new[$key]) && is_array($new[$key])) {
        $result[$key] = self::mergeRecursive($value, $new[$key]);
      }
      elseif (isset($new[$key])) {
        $result[$key] = $new[$key];
      }
      elseif (is_string($key)) {
        $result[$key] = $value;
      }
      if (!isset($new[$key])) {
        $result = array_replace_recursive($result, $new);
      }
    }
    return $result;
  }

}
