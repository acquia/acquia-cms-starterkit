<?php

namespace AcquiaCMS\Cli\Question;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion as SymfonyConfirmationQuestion;

/**
 * Class for confirmation type of question.
 */
class ConfirmationQuestion extends QuestionBase {

  /**
   * Holds an array of allowed values for given question.
   *
   * @var array
   */
  protected $allowedValues;

  /**
   * {@inheritdoc}
   */
  public function initialize(array $data): void {
    $this->question = new SymfonyConfirmationQuestion($data['question'], $this->defaultAnswer);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $data): bool {
    parent::validate($data);
    if (isset($data['allowed_values'])) {
      throw new \Exception(sprintf("The question '%s' of type '%s' can not contain key: allow_values.", $this->getId(), $this->getType()));
    }
    $defaultValue = $data['default_answer'] ?? "";
    if ($defaultValue && gettype($defaultValue) != "boolean") {
      throw new \Exception(
        sprintf("The '%s' of '%s' type question must have a 'true' or 'false' value, instead '%s' was provided.",
          $this->getId(), $this->getType(), $defaultValue,
        )
      );
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function askQuestion(InputInterface $input, OutputInterface $output): void {
    // @todo Implement askQuestion() method.
  }

}
