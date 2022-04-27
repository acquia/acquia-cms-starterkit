<?php

namespace AcquiaCMS\Cli\Helpers;

/**
 * A class for adding any utility functions.
 */
class InstallerQuestions {

  /**
   * Validate all input options/arguments.
   *
   * @param array $bundleQuestions
   *   The questions defined in acms.yml file.
   * @param string $bundle
   *   A name of the user selected use-case.
   *
   * @return array
   *   Returns the questions of the user selected use-case.
   */
  public function getQuestionForBundle(array $bundleQuestions, string $bundle) :array {
    $allQuestion = [];
    foreach ($bundleQuestions as $id => $starter_kit_key) {
      if (isset($starter_kit_key['dependencies']['starter_kits'])) {
        if (in_array($bundle, $starter_kit_key['dependencies']['starter_kits'])) {
          $allQuestion[$id] = [
            'question' => $starter_kit_key['question'],
            'required' => $starter_kit_key['required'],
          ];
        }
      }
      else {
        $allQuestion[$id] = [
          'question' => $starter_kit_key['question'],
          'required' => $starter_kit_key['required'],
        ];
      }
    }
    // Return all question from acms.yml file for requested starter kit.
    return $allQuestion;
  }

  /**
   * Validate all input options/arguments.
   *
   * @param array $questions
   *   Questions of the user selected use-case.
   *
   * @return array
   *   Returns the filteres questions of the user selected use-case.
   */
  public function filterQuestionForBundle(array $questions) :array {
    $filteredQuestions = [];
    foreach ($questions as $apiKey => $question) {
      if (empty(getenv($apiKey))) {
        $filteredQuestions[$apiKey] = $question;
      }
    }
    return $filteredQuestions;
  }

  /**
   * Validate all input options/arguments.
   *
   * @param array $questions
   *   Questions of the user selected use-case.
   *
   * @return array
   *   Returns the filteres questions of the user selected use-case.
   */
  public function styleQuestionForBundle(array $questions) :array {
    foreach ($questions as $apiKey => $question) {
      if ($question['required']) {
        $questions[$apiKey]['question'] = $questions[$apiKey]['question'] . '<error>*</error>';
      }
    }
    return $questions;
  }

  /**
   * Validate all input options/arguments.
   *
   * @param array $bundleQuestions
   *   The os questions defined in acms.yml file.
   * @param string $bundle
   *   A name of the user selected use-case.
   * @param array $keys
   *   API/Token keys for the user selected use-case.
   *
   * @return array
   *   Returns the keys/tokens for the user selected use-case.
   */
  public function getKeyPair(array $bundleQuestions, string $bundle, array $keys) :array {
    $setKeys = [];
    // Return Set of API/Token array.
    foreach ($bundleQuestions as $id => $starter_kit_key) {
      if (isset($starter_kit_key['dependencies']['starter_kits'])) {
        if (in_array($bundle, $starter_kit_key['dependencies']['starter_kits'])) {
          if (getenv($id)) {
            $setKeys[$id] = getenv($id);
          }
        }
      }
      else {
        if (getenv($id)) {
          $setKeys[$id] = getenv($id);
        }
      }
    }
    return array_merge($setKeys, $keys);
  }

}
