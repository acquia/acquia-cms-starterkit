<?php

namespace AcquiaCMS\Cli\Question;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Base class for Question.
 */
abstract class QuestionBase {

  /**
   * Holds symfony question object.
   *
   * @var \Symfony\Component\Console\Question\Question
   */
  protected $question;

  /**
   * Defines the type of question.
   *
   * @var string
   */
  protected $type;

  /**
   * Holds the question type. Acceptable values are 'build' or 'install'.
   *
   * @var string
   */
  protected $questionType;

  /**
   * Holds the default answer for the given question.
   *
   * @var mixed
   */
  protected $defaultAnswer;

  /**
   * Decides if the question is required or not.
   *
   * @var bool
   */
  protected $required;

  /**
   * Holds the condition for given question.
   *
   * @var string
   */
  protected $condition;

  /**
   * Holds the answer for the given question.
   *
   * @var string
   */
  protected $answer;

  /**
   * Unique id for given question.
   *
   * @var string
   */
  protected $id;

  /**
   * Defines the question status.
   *
   * @var bool
   */
  protected $status;

  /**
   * Holds the class with method to alter question.
   *
   * @var string
   */
  protected $class;

  /**
   * Defines the type of question.
   */
  const DEFAULT_TYPE = "normal";

  /**
   * An array of valid types of question.
   */
  const VALID_TYPES = [
    'normal',
    'choice',
    'confirmation',
  ];

  /**
   * Construct the question object.
   */
  public function __construct(string $id, array $data) {
    $this->defaultAnswer = $data['default_answer'] ?? "";
    $this->required = $data['required'] ?? FALSE;
    $this->condition = $data['if'] ?? "";
    $this->id = $id;
    $this->status = TRUE;
    $this->questionType = $data['question-type'];
    $this->answer = $data['answer'] ?? "";
    $this->class = $data['class'] ?? "";
    if ($this->validate($data)) {
      $this->initialize($data);
    }
  }

  /**
   * Function to validate the data.
   *
   * @param array $data
   *   An array of data to validate.
   *
   * @throws \Exception
   */
  public function validate(array $data): bool {
    if (!in_array($data['type'], self::VALID_TYPES)) {
      throw new \Exception("Invalid type. Allowed types: " . implode(', ', self::VALID_TYPES));
    }
    $this->type = $data['type'];
    return TRUE;
  }

  /**
   * Returns the unique id for given question.
   */
  public function getId(): string {
    return $this->id;
  }

  /**
   * Returns the type of question. Ex: normal, choice or confirmation.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Returns the question type. Ex: build or install.
   */
  public function getQuestionType(): string {
    return $this->questionType;
  }

  /**
   * Returns the symfony question object.
   */
  public function getQuestion(): Question {
    return $this->question;
  }

  /**
   * Returns the answer for given question.
   */
  public function getAnswer(): string {
    return $this->answer;
  }

  /**
   * Set the answer for question.
   */
  public function setAnswer(string $answer): void {
    $this->answer = $answer;
  }

  /**
   * Sets the question status. Ex: true or false.
   */
  public function setStatus(bool $status): void {
    $this->status = $status;
  }

  /**
   * Returns the question status.
   */
  public function getStatus(): bool {
    return $this->status;
  }

  /**
   * Returns the condition of given question.
   */
  public function getCondition(): string {
    return $this->condition;
  }

  /**
   * Returns the answer for the question.
   */
  public function __toString(): string {
    return $this->answer;
  }

  /**
   * Returns the alter class for given question.
   */
  public function getClass(): ?string {
    return $this->class;
  }

  /**
   * Perform actions right after question object is created.
   */
  abstract public function initialize(array $data): void;

  /**
   * Ask question to the user.
   */
  abstract public function askQuestion(InputInterface $input, OutputInterface $output): void;

}
