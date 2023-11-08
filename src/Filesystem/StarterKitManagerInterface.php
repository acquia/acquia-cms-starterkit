<?php

namespace AcquiaCMS\Cli\FileSystem;

use AcquiaCMS\Cli\Helpers\StarterKit;
use AcquiaCMS\Cli\Question\Question;

/**
 * The StarterKit manager interface.
 */
interface StarterKitManagerInterface {

  /**
   * Adds the given StarterKit.
   *
   * @param string $machine_name
   *   Given unique id for the starterkit.
   * @param array $starter_kit
   *   An array of StarterKit data.
   */
  public function addStarterKit(string $machine_name, array $starter_kit): StarterKitManagerInterface;

  /**
   * Adds the given question.
   *
   * @param string $machine_name
   *   Given unique id for the question.
   * @param array $question
   *   An array of question data.
   * @param string $questionType
   *   Given question type. Acceptable values are 'build' or 'install'.
   */
  public function addQuestion(string $machine_name, array $question, string $questionType): StarterKitManagerInterface;

  /**
   * Returns an array of starter kits.
   */
  public function getStarterKits(): array;

  /**
   * Returns an array of questions for given question type.
   *
   * @param string $questionType
   *   Given question type. Acceptable values are 'build' or 'install'.
   */
  public function getQuestions(string $questionType): array;

  /**
   * Returns the question based on given id.
   *
   * @param string $key
   *   Given unique id for the question.
   */
  public function getQuestion(string $key): Question;

  /**
   * Return an array of user answer for given question phase.
   *
   * @param string $questionType
   *   Given question type. Should be either build or install.
   */
  public function getAnswers(string $questionType = "build"): array;

  /**
   * Returns the user selected StarterKit object.
   */
  public function selectedStarterKit(): StarterKit;

}
