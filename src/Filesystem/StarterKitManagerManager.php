<?php

namespace AcquiaCMS\Cli\FileSystem;

use AcquiaCMS\Cli\Exception\ErrorException;
use AcquiaCMS\Cli\Helpers\StarterKit;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use AcquiaCMS\Cli\Question\ChoiceQuestion;
use AcquiaCMS\Cli\Question\ConfirmationQuestion;
use AcquiaCMS\Cli\Question\Question;

/**
 * Class for starter_kit_manager object.
 */
class StarterKitManagerManager implements StarterKitManagerInterface {

  use UserInputTrait;

  /**
   * An array of StarterKits.
   *
   * @var array
   */
  protected array $starterKits;

  /**
   * An array of questions.
   *
   * @var array
   */
  protected array $questions;

  /**
   * An array of user response to questions.
   *
   * @var array
   */
  protected array $answers;

  /**
   * Constructs an object.
   */
  public function __construct() {
    $this->starterKits = [];
    $this->questions = [];
    $this->answers = [
      "build" => [],
      "install" => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function loadFromArray(array $data): StarterKitManagerInterface {
    $starterKits = $data['starter_kits'] ?? [];
    $buildQuestions = $data['questions']['build'] ?? [];
    $installQuestions = $data['questions']['install'] ?? [];
    $manager = new self();
    foreach ($starterKits as $machine_name => $starterKit) {
      $manager->addStarterKit($machine_name, $starterKit);
    }
    foreach ($buildQuestions as $machine_name => $question) {
      $manager->addQuestion($machine_name, $question, "build");
    }
    foreach ($installQuestions as $machine_name => $question) {
      $manager->addQuestion($machine_name, $question, "install");
    }
    return $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function addStarterKit(string $machine_name, array $starter_kit): StarterKitManagerInterface {
    $starterKit = new StarterKit($machine_name, $starter_kit);
    if (isset($starter_kit['modules']['require'])) {
      $starterKit->addModules("require", $starter_kit['modules']['require']);
    }
    if (isset($starter_kit['modules']['install'])) {
      $starterKit->addModules("install", $starter_kit['modules']['install']);
    }

    if (isset($starter_kit['themes']['require'])) {
      $starterKit->addThemes("require", $starter_kit['themes']['require']);
    }
    if (isset($starter_kit['themes']['install'])) {
      $starterKit->addThemes("install", $starter_kit['themes']['install']);
    }

    $this->starterKits[$machine_name] = $starterKit;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addQuestion(string $machine_name, array $question, string $questionType): StarterKitManagerInterface {
    if (!isset($question['type'])) {
      $question['type'] = Question::DEFAULT_TYPE;
    }
    $question['question-type'] = $questionType;

    switch ($question['type']) {
      case "choice":
        /** @var \AcquiaCMS\Cli\Question\Question $questionObj */
        $questionObj = new ChoiceQuestion($machine_name, $question);
        break;

      case "confirmation":
        /** @var \AcquiaCMS\Cli\Question\Question $questionObj */
        $questionObj = new ConfirmationQuestion($machine_name, $question);
        break;

      default:
        /** @var \AcquiaCMS\Cli\Question\Question $questionObj */
        $questionObj = new Question($machine_name, $question);
        break;
    }
    $questionObj->getQuestion()->setMaxAttempts(3);
    $this->questions[$questionType][$machine_name] = $questionObj;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStarterKits(): array {
    return $this->starterKits;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestions(string $questionType = "build"): array {
    return $this->questions[$questionType];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \AcquiaCMS\Cli\Exception\ErrorException
   */
  public function getQuestion(string $key, string $questionType = "build"): Question {
    $question = $this->questions[$questionType][$key] ?? NULL;
    if (!$question) {
      throw new ErrorException(
        sprintf("The question with id `%s` doesn't exist for `%s` type.", $key, $questionType)
      );
    }
    return $question;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswers(string $questionType = "build"): array {
    if ($this->answers[$questionType]) {
      return $this->answers[$questionType];
    }
    foreach ($this->getQuestions($questionType) as $question) {
      $answer = $question->getAnswer();
      if ($question->getStatus() && $answer) {
        $this->answers[$questionType][$question->getId()] = $answer;
      }
    }
    return $this->answers[$questionType];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \AcquiaCMS\Cli\Exception\ErrorException
   */
  public function selectedStarterKit(): StarterKit {
    $answers = $this->getAnswers();
    $selectedStarterKit = $answers['starter-kit'] ?? "";
    $starterKits = $this->getStarterKits();
    if (!$selectedStarterKit || !isset($starterKits[$selectedStarterKit])) {
      throw new ErrorException("No starter-kit selected.");
    }
    return $starterKits[$selectedStarterKit];
  }

}
