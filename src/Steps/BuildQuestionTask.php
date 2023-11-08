<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class to ask build questions to user and capture response.
 *
 * @Task(
 *   id = "build_question_task",
 *   weight = 15,
 * )
 */
class BuildQuestionTask extends BaseTask {

  use UserInputTrait;

  /**
   * Holds the starter_kit_manager service object.
   *
   * @var \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface
   */
  protected $starterKitManager;

  /**
   * Holds the install_question helper object.
   *
   * @var \Symfony\Component\Console\Helper\QuestionHelper
   */
  protected $questionHelper;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\FileSystem\StarterKitManagerInterface $starter_kit_manager
   *   The starter_kit_manager service object.
   * @param \Symfony\Component\Console\Helper\QuestionHelper $question_helper
   *   The question helper object.
   */
  public function __construct(StarterKitManagerInterface $starter_kit_manager, QuestionHelper $question_helper) {
    $this->starterKitManager = $starter_kit_manager;
    $this->questionHelper = $question_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('starter_kit_manager'),
      $command->getHelper('question')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int {
    $starter_kit = $input->getArgument("name");
    $question = $this->starterKitManager->getQuestion("starter-kit");
    if ($starter_kit) {
      $question->setAnswer($starter_kit);
    }
    $starterKits = array_keys($this->starterKitManager->getStarterKits());
    $question->getQuestion()->setAutocompleterValues($starterKits);
    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $options = $input->getOptions();
    /** @var \AcquiaCMS\Cli\Question\Question $question */
    foreach ($this->starterKitManager->getQuestions("build") as $question) {
      if (!$question->getAnswer()) {
        if ($question->getCondition()) {
          $expression = new ExpressionLanguage();
          $status = $expression->evaluate($question->getCondition(), [
            "questions" => $this->starterKitManager->getQuestions("build"),
          ]);
          $question->setStatus($status);
        }
        if ($question->getStatus()) {
          $question->askQuestion($input, $output);
        }
      }
      else {
        if (isset($options[$question->getId()])) {
          $question->setAnswer($options[$question->getId()]);
        }
      }
      if ($question->getClass()) {
        if (is_callable($question->getClass())) {
          call_user_func_array($question->getClass(), [
            $this->starterKitManager->selectedStarterKit(),
            $question,
          ]);
        }
        else {
          throw new \Exception(sprintf("The class and method `%s` do not exist.", $question->getClass()));
        }
      }
    }
    return StatusCode::OK;
  }

}
