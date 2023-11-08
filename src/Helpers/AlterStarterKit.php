<?php

namespace AcquiaCMS\Cli\Helpers;

use AcquiaCMS\Cli\Question\Question;

/**
 * Class to alter modules/themes etc. for user selected StarterKit.
 */
class AlterStarterKit {

  /**
   * Update modules list, if user chooses yes for demo-content question.
   *
   * @param \AcquiaCMS\Cli\Helpers\StarterKit $selectedStarterKit
   *   The user selected StarterKit object.
   * @param \AcquiaCMS\Cli\Question\Question $question
   *   The question object for which user answered question.
   */
  public static function postDemoContent(StarterKit $selectedStarterKit, Question $question): void {
    if ($question->getAnswer() == "yes") {
      $selectedStarterKit->addModules("require", [
        "acquia_cms_starter",
      ]);
      $selectedStarterKit->addModules("install", [
        "acquia_cms_starter",
      ]);
    }
  }

  /**
   * Update modules list, if user chooses yes for content-model question.
   *
   * @param \AcquiaCMS\Cli\Helpers\StarterKit $selectedStarterKit
   *   The user selected StarterKit object.
   * @param \AcquiaCMS\Cli\Question\Question $question
   *   The question object for which user answered question.
   */
  public static function postContentModel(StarterKit $selectedStarterKit, Question $question): void {
    if ($question->getAnswer() == "yes") {
      $selectedStarterKit->addModules("require", [
        'acquia_cms_article',
        'acquia_cms_page',
        'acquia_cms_event',
      ]);
      $selectedStarterKit->addModules("install", [
        'acquia_cms_article',
        'acquia_cms_page',
        'acquia_cms_event',
      ]);
    }
  }

  /**
   * Update modules list, if user chooses yes for dam-integration question.
   *
   * @param \AcquiaCMS\Cli\Helpers\StarterKit $selectedStarterKit
   *   The user selected StarterKit object.
   * @param \AcquiaCMS\Cli\Question\Question $question
   *   The question object for which user answered question.
   */
  public static function postDamQuestion(StarterKit $selectedStarterKit, Question $question): void {
    if ($question->getAnswer() == "yes") {
      $selectedStarterKit->addModules("require", [
        'acquia_cms_dam',
      ]);
      $selectedStarterKit->addModules("install", [
        'acquia_cms_dam',
      ]);
    }
  }

  /**
   * Update modules list, if user chooses yes for gdpr-integration question.
   *
   * @param \AcquiaCMS\Cli\Helpers\StarterKit $selectedStarterKit
   *   The user selected StarterKit object.
   * @param \AcquiaCMS\Cli\Question\Question $question
   *   The question object for which user answered question.
   */
  public static function postGdprQuestion(StarterKit $selectedStarterKit, Question $question): void {
    if ($question->getAnswer() == "yes") {
      $selectedStarterKit->addModules("require", [
        'gdpr', 'eu_cookie_compliance', 'gdpr_fields',
      ]);
      $selectedStarterKit->addModules("install", [
        'gdpr', 'eu_cookie_compliance', 'gdpr_fields',
      ]);
    }
  }

}
