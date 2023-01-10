<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Helper function to get your inputs to setup starter kit.
 */
class AskQuestions {

  use StatusMessageTrait;
  use UserInputTrait;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  /**
   * The AcquiaCMS installer questions object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallerQuestions
   */
  protected $installerQuestions;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   * @param \AcquiaCMS\Cli\Helpers\InstallerQuestions $installerQuestions
   *   Provides the AcquiaCMS InstallerQuestions class object.
   */
  public function __construct(
    Cli $cli,
    InstallerQuestions $installerQuestions) {
    $this->acquiaCmsCli = $cli;
    $this->installerQuestions = $installerQuestions;
  }

  /**
   * Providing input to user, asking to provide key.
   */
  public function askKeysQuestions(
    InputInterface $input,
    OutputInterface $output,
    string $bundle,
    string $question_type,
    QuestionHelper $helper) :array {
    // Get all questions for user selected use-case defined in acms.yml file.
    $questions = $this->installerQuestions->getQuestions($this->acquiaCmsCli->getInstallerQuestions($question_type), $bundle);
    $processedQuestions = $this->installerQuestions->process($questions);

    // Initialize the value with default answer for question, so that
    // if any question is dependent on other question which is skipped,
    // we can use the value for that question to make sure the cli
    // doesn't throw following RunTime exception:"Not able to resolve variable".
    // @see AcquiaCMS\Cli\Helpers::shouldAskQuestion().
    $userInputValues = $processedQuestions['default'];
    foreach ($questions as $key => $question) {
      $envVar = $this->installerQuestions->getEnvValue($question, $key);
      if (empty($envVar)) {
        if ($this->installerQuestions->shouldAskQuestion($question, $userInputValues)) {

          $userInputValues[$key] = $this->askQuestion($question, $key, $input, $output, $helper);
        }
      }
      else {
        $userInputValues[$key] = $envVar;
      }
    }

    return array_merge($processedQuestions['default'], $userInputValues);
  }

  /**
   * Function to ask question to user.
   *
   * @param array $question
   *   An array of question.
   * @param string $key
   *   A unique key for question.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   A Console input interface object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   A Console output interface object.
   * @param \Symfony\Component\Console\Helper\QuestionHelper $helper
   *   A Symfony helper object.
   */
  public function askQuestion(
    array $question,
    string $key,
    InputInterface $input,
    OutputInterface $output,
    QuestionHelper $helper) : string {
    $isRequired = $question['required'] ?? FALSE;
    $defaultValue = $this->installerQuestions->getDefaultValue($question, $key);
    $skipOnValue = $question['skip_on_value'] ?? TRUE;
    if ($skipOnValue && $defaultValue) {
      return $defaultValue;
    }
    $askQuestion = new Question($this->styleQuestion($question['question'], $defaultValue, $isRequired, TRUE));
    $askQuestion->setValidator(function ($answer) use ($question, $key, $isRequired, $output, $defaultValue) {
      if (!is_string($answer) && !$defaultValue) {
        if ($isRequired) {
          throw new \RuntimeException(
            "The `" . $key . "` cannot be left empty."
          );
        }
        else {
          if (isset($question['warning'])) {
            $warning = str_replace(PHP_EOL, PHP_EOL . " ", $question['warning']);
            $output->writeln($this->style(" " . $warning, 'warning', FALSE));
          }
        }
      }
      if ($answer && isset($question['allowed_values']['options']) && !in_array($answer, $question['allowed_values']['options'])) {
        throw new \RuntimeException(
          "Invalid value. It should be from one of the following: " . implode(", ", $question['allowed_values']['options'])
        );
      }
      return $answer ?: $defaultValue;
    });
    $askQuestion->setMaxAttempts(3);
    if (isset($question['allowed_values']['options'])) {
      $askQuestion->setAutocompleterValues($question['allowed_values']['options']);
    }
    $response = $helper->ask($input, $output, $askQuestion);
    return ($response === NULL) ? $defaultValue : $response;
  }

}
