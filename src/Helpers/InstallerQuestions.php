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
    $questionToAsk = [];
    foreach ($questions as $key => $question) {
      if ($this->filterByStarterKit($question, $bundle)) {
        $questionToAsk[$key] = $question;
      }
    }

    return $questionToAsk;
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
  public function filterByStarterKit(array $question, string $bundle) :bool {
    $isValid = TRUE;
    if (isset($question['dependencies']['starter_kits'])) {
      $starterKits = array_map('trim', explode('||', $question['dependencies']['starter_kits']));
      if (!in_array($bundle, $starterKits)) {
        $isValid = FALSE;
      }
    }
    return $isValid;
  }

  /**
   * Filter the questions based on other dependent question.
   *
   * @param array $question
   *   An Array of question.
   * @param string $bundle
   *   A name of the user selected use-case.
   *
   * @return bool
   *   Returns true|false, if question needs to ask.
   */
  public function filterByQuestion(array $question, string $bundle) :bool {
    $isValid = FALSE;
    // Here, we are just filtering to check if we should ask question or not.
    // At this point, we don't know what answer user would give.
    // Based on user answer, we'll decide, if we should ask question.
    // @see shouldAskQuestion().
    $starterKit = $this->filterByStarterKit($question, $bundle);
    if (isset($question['dependencies']['questions']) && $starterKit) {
      $isValid = TRUE;
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
      $defaultValue = $this->getDefaultValue($question, $key);
      $isSkip = $question['skip_on_value'] ?? TRUE;
      if ((!$defaultValue && !$isSkip) || !$isSkip) {
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

  /**
   * Returns the default value for the question.
   *
   * @param array $question
   *   An array of question.
   * @param string $key
   *   A unique question key.
   *
   * @return string
   *   Returns the default value for question.
   */
  public function getDefaultValue(array $question, string $key = ""): string {
    $envVarValue = $this->getEnvValue($question, $key);
    return $envVarValue ?: trim(PHPParser::parseEnvVars($question['default_value'] ?? ''));
  }

  /**
   * Returns the value from environment variable for the question.
   *
   * @param array $question
   *   An array of question.
   * @param string $key
   *   A unique question key.
   *
   * @return string
   *   Returns the default value for question.
   */
  public function getEnvValue(array $question, string $key = ""): string {
    return trim(PHPParser::parseEnvVars(!empty(getenv($key)) ? getenv($key) : ''));
  }

  /**
   * Determines if question should be asked.
   *
   * @param array $question
   *   An array of question.
   * @param array $userInputValues
   *   An array of user answer for question.
   *
   * @return bool
   *   Returns true|false, if question should be asked.
   */
  public function shouldAskQuestion(array $question, array $userInputValues): bool {
    $questionsExpressionArray = $question['dependencies']['questions'] ?? [];
    if (!$questionsExpressionArray) {
      return TRUE;
    }
    $isValid = FALSE;
    foreach ($questionsExpressionArray as $questionsExpression) {
      $questionsExpression = array_map('trim', explode('||', $questionsExpression));
      $isValid = FALSE;
      foreach ($questionsExpression as $questionExpression) {
        $questionMatches = PHPParser::parseQuestionExpression($questionExpression);
        $conditionKey = $questionMatches[1] ?? '';
        if ($conditionKey && isset($userInputValues[$conditionKey])) {
          $questionValue = trim($questionMatches[5], '"');
          if ($questionValue == 'ALL') {
            return TRUE;
          }
          switch ($questionMatches[3]) {
            case "==":
              $isValid = $userInputValues[$conditionKey] == $questionValue;
              break;

            case "!=":
              $isValid = $userInputValues[$conditionKey] != $questionValue;
              break;

            case ">":
              $isValid = $userInputValues[$conditionKey] > $questionValue;
              break;

            case ">=":
              $isValid = $userInputValues[$conditionKey] >= $questionValue;
              break;

            case "<":
              $isValid = $userInputValues[$conditionKey] < $questionValue;
              break;

            case "<=":
              $isValid = $userInputValues[$conditionKey] <= $questionValue;
              break;

            default:
              throw new \RuntimeException("Invalid condition or condition not defined: " . $questionMatches[3]);
          }
        }
        else {
          throw new \RuntimeException('Not able to resolve variable: ${' . $conditionKey . '} for expression: ' . $questionExpression);
        }
        if ($isValid == TRUE) {
          return $isValid;
        }
      }
    }
    return $isValid;
  }

}
