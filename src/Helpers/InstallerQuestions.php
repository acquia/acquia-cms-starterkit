<?php

namespace AcquiaCMS\Cli\Helpers;

use AcquiaCMS\Cli\Helpers\Parsers\PHPParser;

/**
 * A class for adding any utility functions.
 */
class InstallerQuestions {

  /**
   * Gets All questions based on user selected user-case.
   *
   * @param array $questions
   *   An array of all questions.
   * @param string $bundle
   *   A name of the user selected use-case.
   *
   * @return array
   *   Returns the questions for the user selected use-case.
   */
  public function getQuestions(array $questions, string $bundle) :array {
    $allQuestion = [];
    foreach ($questions as $key => $question) {
      if ($this->filter($question, $bundle)) {
        $allQuestion[$key] = $question;
      }
    }
    return $allQuestion;
  }

  /**
   * Filter the questions based on user selected use-case.
   *
   * @param array $question
   *   A Question array.
   * @param string $bundle
   *   A name of the user selected use-case.
   *
   * @return bool
   *   Returns true|false, if question needs to ask.
   */
  public function filter(array $question, string $bundle) :bool {
    $isValid = TRUE;
    if (isset($question['dependencies']['starter_kits'])) {
      if (!in_array($bundle, $question['dependencies']['starter_kits'])) {
        $isValid = FALSE;
      }
    }
    return $isValid;
  }

  /**
   * Process all the questions.
   *
   * @param array $questions
   *   An array of filtered questions.
   *
   * @return array
   *   Returns an array of default values for questions and questions to ask.
   */
  public function process(array $questions) :array {
    $defaultValues = $questionToAsk = [];
    foreach ($questions as $key => $question) {
      $defaultValue = $question['default_value'] ?? getenv($key);
      $defaultValue = trim(PHPParser::parseEnvVars($defaultValue));
      if (!$defaultValue) {
        $questionToAsk[$key] = $question;
      }
      else {
        $defaultValues[$key] = $defaultValue;
      }
    }
    return [
      'default' => $defaultValues,
      'questionToAsk' => $questionToAsk,
    ];
  }

}
