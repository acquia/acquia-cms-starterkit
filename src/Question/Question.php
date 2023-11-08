<?php

namespace AcquiaCMS\Cli\Question;

use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as SymfonyQuestion;

/**
 * Class to manage questions which will be presented to user.
 */
class Question extends QuestionBase {

  use UserInputTrait;

  /**
   * An array of allowed values.
   *
   * @var array
   */
  protected $allowedValues;

  /**
   * The symfony question object.
   *
   * @var \Symfony\Component\Console\Question\Question
   */
  protected $question;

  /**
   * {@inheritDoc}
   */
  public function initialize(array $data): void {
    $this->allowedValues = $data['allowed_values'] ?? [];

    $questionText = $this->styleQuestion(
      $data['question'],
      $data['default_answer'] ?? "",
      $data['required'] ?? FALSE,
      TRUE,
    );

    $this->question = new SymfonyQuestion($questionText, $this->defaultAnswer);
    if ($this->allowedValues) {
      $this->question->setAutocompleterValues($this->allowedValues);
    }
    if (isset($data['hidden'])) {
      $this->question->setHidden($data['hidden']);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function askQuestion(InputInterface $input, OutputInterface $output): void {
    /** @var \Symfony\Component\Console\Question\Question $question */
    $question = $this->getQuestion();
    $questHelper = new QuestionHelper();
    $answer = $questHelper->ask($input, $output, $question);
    $this->setAnswer($answer);
  }

}
