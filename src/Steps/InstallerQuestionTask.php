<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\FileSystem\FileLoader;
use AcquiaCMS\Cli\Helpers\InstallQuestions;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to ask questions to user and capture response.
 *
 * @Task(
 *   id = "installer_question_task",
 *   weight = 1,
 * )
 */
class InstallerQuestionTask extends BaseTask {

  use UserInputTrait;

  /**
   * Holds the file_loader service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader
   */
  protected $fileLoader;

  /**
   * Holds the install_questions service object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallQuestions
   */
  protected $questions;

  /**
   * Holds the install_question helper object.
   *
   * @var \Symfony\Component\Console\Helper\QuestionHelper
   */
  protected $questionHelper;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\FileSystem\FileLoader $fileLoader
   *   The file_loader service object.
   * @param \AcquiaCMS\Cli\Helpers\InstallQuestions $questions
   *   The install_questions service object.
   * @param \Symfony\Component\Console\Helper\QuestionHelper $question_helper
   *   The question helper object.
   */
  public function __construct(FileLoader $fileLoader, InstallQuestions $questions, QuestionHelper $question_helper) {
    $this->questions = $questions;
    $this->fileLoader = $fileLoader;
    $this->questionHelper = $question_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('file_loader'),
      $container->get('install_questions'),
      $command->getHelper('question')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int {
    $starter_kits = array_keys($this->fileLoader->getStarterKits());
    $name = $input->getArgument("name");
    if ($name) {
      if (!in_array($name, $starter_kits)) {
        throw new \RuntimeException(
          "Invalid response. Response should be from one of the following: " . implode(", ", $starter_kits) . "."
        );
      }
      $questions = &$this->questions->getQuestions();
      $answers = &$this->questions->getAnswers();
      unset($questions["starter_kit"]);
      $answers["starter_kit"] = $name;
    }

    return parent::preExecute($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $answers = &$this->questions->getAnswers();
    foreach ($this->questions->getQuestions() as $key => $question) {
      if ($this->questions->shouldAskQuestion($key)) {
        $answers[$key] = $this->askQuestion($key, $input, $output);
      }
      else {
        unset($answers[$key]);
      }
    }
    return StatusCode::OK;
  }

  /**
   * {@inheritdoc}
   */
  public function postExecute(InputInterface $input, OutputInterface $output): int {
    $answers = $this->questions->getAnswers();
    $this->fileLoader->alterModulesAndThemes($answers);
    return parent::postExecute($input, $output);
  }

  /**
   * Function responsible for asking questions to users.
   *
   * @param string $key
   *   Question id.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   An input object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   An output object.
   *
   * @return string
   *   Returns the user response for question.
   *
   * @throws \Exception
   */
  protected function askQuestion(string $key, InputInterface $input, OutputInterface $output): string {
    $question = $this->questions->getQuestion($key);
    $isRequired = $question['required'] ?? FALSE;
    $isNewLine = TRUE;
    $autoCompleteValues = $question['allowed_values']['options'] ?? "";
    if ($key == "starter_kit") {
      $autoCompleteValues = array_keys($this->fileLoader->getStarterKits());
      $isNewLine = FALSE;
    }
    $questionResponse = $this->questions->getAnswer($key);
    $questionObj = new Question($this->styleQuestion($question['question'], $questionResponse, $isRequired, $isNewLine), $questionResponse);
    if ($autoCompleteValues) {
      $questionObj->setAutocompleterValues($autoCompleteValues);
      $questionObj->setValidator(function ($answer) use ($autoCompleteValues) {
        if ($answer && !in_array($answer, $autoCompleteValues)) {
          throw new \RuntimeException(
            "Invalid response. Response should be from one of the following: " . implode(", ", $autoCompleteValues) . "."
          );
        }
        return $answer;
      });
    }
    $questionObj->setMaxAttempts(3);
    $answer = $this->questionHelper->ask($input, $output, $questionObj);
    if (!$answer && isset($question['warning'])) {
      $warning = str_replace(PHP_EOL, PHP_EOL . " ", $question['warning']);
      $output->writeln($this->style(" " . $warning, 'warning', FALSE));
    }
    return $answer;
  }

}
