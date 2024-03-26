<?php

namespace AcquiaCMS\Cli\Helpers;

/**
 * A class for adding any utility functions.
 */
class Utility {

  /**
   * Function to normalize any given file/directory path.
   *
   * @param string $path
   *   Path to normalize.
   *
   * @return string
   *   Returns the normalized path.
   */
  public static function normalizePath(string $path): string {
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = [];
    foreach ($parts as $part) {
      if ('.' == $part) {
        continue;
      }
      if ('..' == $part) {
        array_pop($absolutes);
      }
      else {
        $absolutes[] = $part;
      }
    }
    return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
  }

  /**
   * Generates the password based upon certain required conditions.
   *
   * @param int $length
   *   Parameter that accepts the length of the password.
   *
   * @return string
   *   Returns the shuffled string password.
   */
  public static function generateRandomPassword(int $length = 12): string {
    // Define the character libraries.
    $sets = [];
    $sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    $sets[] = '0123456789';
    $sets[] = '~!@#$%^&*(){}[],./?';
    $password = '';

    // Append a character from each set - gets first 4 characters.
    foreach ($sets as $set) {
      $password .= $set[array_rand(str_split($set))];
    }

    // Use all characters to fill up to $length.
    while (strlen($password) < $length) {
      // Get a random set.
      $randomSet = $sets[array_rand($sets)];
      // Add a random char from the random set.
      $password .= $randomSet[array_rand(str_split($randomSet))];
    }

    // Shuffle the password string before returning.
    return str_shuffle($password);
  }

  /**
   * Replace an array value by key.
   *
   * @param array $input
   *   An input array.
   * @param string $key
   *   The key from where to replace.
   * @param string $search
   *   Key to search.
   * @param string $replace
   *   Value to replace.
   */
  public static function replaceValueByKey(array $input, string $key, string $search, string $replace): array {
    // Split the key into an array of nested keys.
    $keys = explode('.', $key);

    // Initialize a reference to the starting point of array.
    $current = &$input;
    // Traverse the array using each nested key.
    foreach ($keys as $nestedKey) {
      // Check if the current nested key exists in the array.
      if (isset($current[$nestedKey])) {
        // If it's the last key, perform the replacement.
        if ($nestedKey === end($keys)) {
          if (is_array($current[$nestedKey])) {
            $current[$nestedKey] = array_map(function ($value) use ($search, $replace) {
              return ($value === $search) ? $replace : $value;
            }, $current[$nestedKey]);
          }
          else {
            $current[$nestedKey] = $replace;
          }
        }
        else {
          // If not the last key, continue traversing.
          $current = &$current[$nestedKey];
        }
      }
    }
    return $input;
  }

  /**
   * Remove the value by key.
   *
   * @param array $input
   *   An input array.
   * @param string $key
   *   The key from where to remove value.
   * @param string $search
   *   The key to search.
   */
  public static function removeValueByKey(array $input, string $key, string $search): array {
    // Split the key into an array of nested keys.
    $keys = explode('.', $key);

    // Initialize a reference to the starting point of array.
    $current = &$input;

    // Traverse the array using each nested key.
    foreach ($keys as $nestedKey) {
      // Check if the current nested key exists in the array.
      if (isset($current[$nestedKey])) {
        // If it's the last key, perform the removal.
        if ($nestedKey === end($keys)) {
          if (is_array($current[$nestedKey])) {
            // Remove the value from the array.
            $index = array_search($search, $current[$nestedKey], TRUE);
            if ($index !== FALSE) {
              // Remove the element without reindexing.
              array_splice($current[$nestedKey], $index, 1);
            }
            $current[$nestedKey] = array_filter($current[$nestedKey], function ($value) use ($search) {
              return $value !== $search;
            });
          }
        }
        else {
          // If not the last key, continue traversing.
          $current = &$current[$nestedKey];
        }
      }
    }
    return $input;
  }

}
