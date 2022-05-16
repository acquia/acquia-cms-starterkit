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

  /**
   * Function used to parse a question expression.
   *
   * @param string $input
   *   An input string to parse.
   *
   * @return array
   *   Returns an array of parsed matches.
   */
  public static function parseQuestionExpression(string $input): array {
    $input = trim($input);
    $pattern = '/\${?(\w+)}?(\s)(==|>|>=|<|<=|!=)(\s)(".*"|(\d+\.?\d+))/i';
    preg_match($pattern, $input, $matches);
    if (!isset($matches[0]) || (isset($matches[0]) && $input != $matches[0])) {
      $errorMessages = [
        "Invalid Question expression: $input",
        '',
        "It should exactly match from one of the following pattern: ",
        ' ${some_question_key} == "<some_string_value>". Ex: ${some_question_key} == "yes"',
        ' ${some_question_key} != "<some_string_value>". Ex: ${some_question_key} != "yes"',
        ' ${some_question_key} > <some_numeric_value>. Ex: ${some_question_key} > 10',
        ' ${some_question_key} >= <some_numeric_value>. Ex: ${some_question_key} >= 10',
        ' ${some_question_key} < <some_numeric_value>. Ex: ${some_question_key} < 10',
        ' ${some_question_key} <= <some_numeric_value>. Ex: ${some_question_key} <= 10',
      ];
      throw new \RuntimeException(implode(PHP_EOL, $errorMessages));
    }
    return $matches;
  }

}
