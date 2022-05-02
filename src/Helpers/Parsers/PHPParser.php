<?php

namespace AcquiaCMS\Cli\Helpers\Parsers;

/**
 * The PHP Parser class to parse different php string/array etc.
 */
class PHPParser {

  /**
   * Function used to parse string which contains environment variables.
   *
   * @param string $input
   *   An input string to parse.
   *
   * @return string
   *   Returns parsed string.
   */
  public static function parseEnvVars(string $input) :string {
    $pattern = '/\${?(\w+)}?/i';
    return preg_replace_callback($pattern, function ($matches) {
      return isset($matches[1]) ? getenv($matches[1]) : '';
    }, $input);
  }

}
