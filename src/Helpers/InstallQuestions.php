<?php

namespace AcquiaCMS\Cli\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A class for adding any utility functions.
 */
class InstallQuestions {

  /**
   * Holds user response for questions.
   *
   * @var array
   */
  protected $answers = [];

  /**
   * Holds an array of questions.
   *
   * @var array
   */
  protected $questions = [];

  /**
   * Holds the expression_language service.
   *
   * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage
   */
  protected $expressionLanguage;

  /**
   * Constructs an object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A symfony container object.
   *
   * @throws \Exception
   */
  public function __construct(ContainerInterface $container) {
    $this->questions = $container->get('file_loader')->getInstallerQuestions();
    $this->expressionLanguage = $container->get('expression_language');
  }

  /**
   * Sets the default values for questions.
   */
  protected function setDefaultValues(): void {
    foreach ($this->getQuestions() as $key => $question) {
      $envValue = getenv($key);
      if ($envValue) {
        unset($this->questions[$key]);
      }
      $this->answers[$key] = $envValue ?: ($question['default_value'] ?? "");
    }
  }

  /**
   * Returns an array of user response to questions.
   */
  public function &getAnswers(): array {
    if (!$this->answers) {
      $this->setDefaultValues();
    }
    return $this->answers;
  }

  /**
   * Returns an array of questions to ask.
   */
  public function &getQuestions(): array {
    return $this->questions;
  }

  /**
   * Returns user-response for given question.
   *
   * @param string $key
   *   A question key.
   */
  public function getAnswer(string $key): string {
    return $this->answers[$key] ?? "";
  }

  /**
   * Returns question array.
   *
   * @param string $key
   *   A question key.
   */
  public function getQuestion(string $key): array {
    return $this->questions[$key] ?? [];
  }

  /**
   * Decides if question should be asked or not.
   *
   * @param string $key
   *   A question key.
   */
  public function shouldAskQuestion(string $key): bool {
    $questions = (object) $this->getAnswers();
    $question = $this->getQuestion($key);
    $condition = $question['if'] ?? "";
    return !getenv($key) && (!$condition || $this->expressionLanguage->evaluate($condition, ['questions' => $questions]));
  }

}
