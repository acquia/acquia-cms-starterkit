<?php

namespace AcquiaCMS\Cli\Question;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion as SymfonyChoiceQuestion;

/**
 * Class for choice type of question.
 */
class ChoiceQuestion extends QuestionBase {

  /**
   * Decides if question should accept multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * Holds an array of allowed values for given question.
   *
   * @var array
   */
  protected $allowedValues;

  /**
   * {@inheritdoc}
   */
  public function __construct(string $id, array $data) {
    $this->defaultAnswer = isset($data['default_answer']) ? array_map('trim', explode(',', $data['default_answer'])) : [];
    $this->allowedValues = $data['allowed_values'] ?? [];
    $this->multiple = $data['multiple'] ?? FALSE;
    parent::__construct($id, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $data): bool {
    parent::validate($data);
    if (!isset($data['allowed_values'])) {
      throw new \Exception(
        sprintf("The '%s' of '%s' type question must have a key 'allowed_values' of type 'array'.",
          $this->getId(), $this->getType(),
        )
      );
    }
    $defaultValue = $data['default_answer'] ?? "";
    $allowedValues = $data['allowed_values'];
    if ($defaultValue && gettype($defaultValue) != "string") {
      throw new \Exception(
        sprintf("The '%s' of '%s' type question must have a string value, instead '%s' was provided.",
          $this->getId(), $this->getType(), $defaultValue,
        )
      );
    }
    if (gettype($allowedValues) != "array") {
      throw new \Exception(
        sprintf("The '%s' of '%s' type question must have an array values, instead '%s' was provided.",
          $this->getId(), $this->getType(), $allowedValues,
        )
      );
    }
    if ($allowedValues && $defaultValue) {
      $defaultValues = array_map('trim', explode(',', $defaultValue));
      $diff = array_diff($defaultValues, $allowedValues);
      if ($diff) {
        throw new \Exception(
          sprintf("Invalid default value '%s' for '%s' type question '%s'. It must be from the allowed_values.",
            implode(", ", $diff), $this->getType(), $this->getId(),
          )
        );
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function initialize(array $data): void {
    $this->question = new SymfonyChoiceQuestion($data['question'], $this->allowedValues, $this->defaultAnswer);
    $this->question->setMultiselect($this->multiple);
  }

  /**
   * {@inheritdoc}
   */
  public function askQuestion(InputInterface $input, OutputInterface $output): void {
    // @todo Implement askQuestion() method.
  }

}
