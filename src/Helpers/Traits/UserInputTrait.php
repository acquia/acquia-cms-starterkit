<?php

namespace AcquiaCMS\Cli\Helpers\Traits;

/**
 * Provides the trait for user input questions.
 */
trait UserInputTrait {

  /**
   * Style the question to print on cli.
   */
  public function styleQuestion(string $question, string $default_value = '', bool $required = FALSE, bool $new_line = FALSE) :string {
    $message = " <info>$question</info>";
    if (!$default_value && $required) {
      $message .= "<fg=red;options=bold> *</>";
    }
    if ($default_value) {
      $message .= " <comment>[$default_value]</comment>";
    }
    $message .= ":" . PHP_EOL . " > ";
    return ($new_line) ? PHP_EOL . $message : $message;
  }

  /**
   * Get user input options.
   *
   * @param array $options
   *   List of user and default inputs.
   * @param string $command_type
   *   Command type whether its build or install.
   *
   * @return array
   *   Filter input options.
   */
  public function getInputOptions(array $options, string $command_type): array {
    $output = [];
    if ($command_type === 'install') {
      $inputOptions = array_filter($options);
    }
    else {
      $inputOptions = array_filter($options, function ($option) {
        return $option === 'yes';
      });
    }

    foreach ($inputOptions as $key => $value) {
      $arg = str_replace('enable-', '', $key);
      $output[$arg] = $value;
    }

    return $output;
  }

}
