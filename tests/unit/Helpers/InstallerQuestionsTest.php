<?php

namespace tests\Helpers;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;
use tests\CliTest;

/**
 * Class to tests InstallerQuestions class.
 */
class InstallerQuestionsTest extends TestCase {
  use ProphecyTrait;

  /**
   * Holds the symfony console output object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $output;

  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $projectDirectory;


  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $rootDirectory;

  /**
   * An acquia minimal client object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCli;

  /**
   * An acquia minimal client object.
   *
   * @var \AcquiaCMS\Cli\Helpers\InstallerQuestions
   */
  protected $installerQuestions;

  /**
   * An array of questions defined in acms.yml file.
   *
   * @var array
   */
  protected $acmsQuestions;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->output = $this->prophesize(OutputInterface::class);
    $output = $this->output->reveal();
    $this->projectDirectory = getcwd();
    $this->rootDirectory = $this->projectDirectory;
    $this->acquiaCli = new Cli($this->projectDirectory, $this->rootDirectory, $output);
    $this->installerQuestions = new InstallerQuestions();
    $this->acmsQuestions = $this->acquiaCli->getInstallerQuestions();
  }

  /**
   * Tests that the keys are correct.
   *
   * @param string $bundle
   *   The user selected use-case id.
   * @param array $questions
   *   An array of questions.
   *
   * @dataProvider providerBundle
   */
  public function testGetQuestionsForBundle(string $bundle, array $questions) :void {
    $this->assertEquals($questions, $this->installerQuestions->getQuestions($this->acmsQuestions, $bundle)['questionMustAsk']);
  }

  /**
   * Function to test method: getDefaultValue().
   *
   * @param array $actual
   *   An array of actual values.
   * @param string $expected
   *   An expected string value.
   * @param string $key
   *   A unique question key.
   * @param array $envVariables
   *   An array of environment variables & values.
   *
   * @dataProvider providerDefaultValue
   */
  public function testQuestionDefaultValue(array $actual, string $expected, string $key = '', array $envVariables = []) :void {
    if ($envVariables) {
      foreach ($envVariables as $envVariable => $value) {
        putenv("$envVariable=$value");
      }
    }
    $this->assertEquals($expected, $this->installerQuestions->getDefaultValue($actual, $key));
  }

  /**
   * Function to test method: shouldAskQuestion().
   *
   * @param array $question
   *   An array of question.
   * @param array $userInputValues
   *   An array of user input values.
   * @param bool $expected
   *   Return true|false based on question should be asked.
   * @param string $exception
   *   An expected exception string.
   *
   * @dataProvider dataShouldAskQuestion
   */
  public function testShouldAskQuestion(array $question, array $userInputValues, bool $expected, string $exception = '') : void {
    if ($exception) {
      $this->expectExceptionMessageMatches($exception);
    }
    $this->assertEquals($expected, $this->installerQuestions->shouldAskQuestion($question, $userInputValues));
  }

  /**
   * Returns an array of dummy question.
   *
   * @return array
   *   Returns an array of dummy question.
   */
  protected function dummyQuestion(): array {
    return [
      'dependencies' => [
        'starter_kits' => 'acquia_cms_enterprise_low_code',
        'questions' => [
          '${demo_content} == "ALL"',
        ],
      ],
      'question' => "Please provide the Site Studio Organization Key",
      'warning' => "The Site Studio Organization key is not set. The Site Studio packages won't get imported.\nYou can set the key later from: /admin/cohesion/configuration/account-settings to import Site Studio packages.",
    ];
  }

  /**
   * Returns an array of dataProvider for method: shouldAskQuestion().
   *
   * @return array[]
   *   Returns an array of dataProvider.
   */
  public function dataShouldAskQuestion(): array {
    $dummyQuestion = $this->dummyQuestion();
    return [
      [
        $dummyQuestion,
        [
          'demo_content' => 'ALL',
        ],
        TRUE,
      ],
    ];
  }

  /**
   * Function to return an array of actual|expected values.
   *
   * @return array[]
   *   Returns an array of values.
   */
  public function providerDefaultValue(): array {
    return [
      [
        CliTest::getContentModel()['content_model'],
        'no',
      ],
      [
        CliTest::getSiteStudioApiKey()['SITESTUDIO_API_KEY'],
        'some_value',
        'SITESTUDIO_API_KEY',
        [
          'SITESTUDIO_API_KEY' => 'some_value',
        ],
      ],
      [
        CliTest::getGmapsKey()['GMAPS_KEY'],
        '',
        'GMAPS_KEYS',
        [
          'SOME_RANDOM_VALUE' => 'some_value',
        ],
      ],
    ];
  }

  /**
   * Data provider for ::testGetQuestionsForBundle().
   *
   * @return array[]
   *   Sets of arguments to pass to the test method.
   */
  public function providerBundle() :array {
    return [
      [
        'acquia_cms_enterprise_low_code',
        array_merge(
          CliTest::getDemoContent(),
        ),
      ],
      [
        'acquia_cms_community',
        array_merge(
          CliTest::getDemoContent(),
        ),
      ],
      [
        'acquia_cms_headless',
        array_merge(
          CliTest::getDemoContent(),
          CliTest::getNextjsApp(),
        ),
      ],
    ];
  }

}
