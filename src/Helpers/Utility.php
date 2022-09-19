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

}
