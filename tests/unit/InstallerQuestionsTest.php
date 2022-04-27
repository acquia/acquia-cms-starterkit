<?php

namespace tests;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

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
   *
   * @dataProvider providerBundle
   */
  public function testGetQuestionsForBundle(string $bundle) :void {
    $questions = $this->getQuestions($bundle);
    $this->assertEquals($questions, $this->installerQuestions->getQuestionForBundle($this->acmsQuestions, $bundle));
  }

  /**
   * Tests that the question are asked to user if key is not set.
   *
   * @param string $bundle
   *   The user selected use-case id.
   *
   * @dataProvider providerBundle
   */
  public function testFilterQuestionForBundle(string $bundle) :void {
    putenv("SEARCH_UUID=dummy");
    $allQuestions = [];
    switch ($bundle) {
      case 'acquia_cms_demo':
        if (!getenv("CONNECTOR_ID")) {
          $allQuestions['CONNECTOR_ID'] = [
            "question" => "Please provide the Acquia Connector ID",
            "required" => TRUE,
          ];
        }
        if (!getenv("GMAPS_KEY")) {
          $allQuestions['GMAPS_KEY'] = [
            "question" => "Please provide the Google Maps API Key",
            "required" => TRUE,
          ];
        }
      case 'acquia_cms_low_code':
        if (!getenv("SITESTUDIO_API_KEY")) {
          $allQuestions['SITESTUDIO_API_KEY'] = [
            "question" => "Please provide the Site Studio API Key",
            "required" => TRUE,
          ];
        }
        if (!getenv("SITESTUDIO_ORG_KEY")) {
          $allQuestions['SITESTUDIO_ORG_KEY'] = [
            "question" => "Please provide the Site Studio Organization Key",
            "required" => TRUE,
          ];
        }
      case 'acquia_cms_minimal':
      case 'acquia_cms_standard':
        if (!getenv("SEARCH_UUID")) {
          $allQuestions['SEARCH_UUID'] = [
            "question" => "Please provide the Acquia CMS Search UUID",
            "required" => TRUE,
          ];
        }
        break;
    }
    $questions = $this->getQuestions($bundle);
    $this->assertEquals($allQuestions, $this->installerQuestions->filterQuestionForBundle($questions));
  }

  /**
   * Tests that the keys are correct.
   *
   * @param string $bundle
   *   The user selected use-case id.
   *
   * @dataProvider providerBundle
   */
  public function teststyleQuestionForBundle(string $bundle) :void {
    $questions = $this->getQuestions($bundle);
    $this->assertEquals($this->installerQuestions->styleQuestionForBundle($questions), $this->installerQuestions->styleQuestionForBundle($this->installerQuestions->getQuestionForBundle($this->acmsQuestions, $bundle)));
  }

  /**
   * Tests that the keys are correct.
   *
   * @param string $bundle
   *   The user selected use-case id.
   *
   * @dataProvider providerBundle
   */
  public function testGetKeyPair(string $bundle) :void {
    putenv("SEARCH_UUID=dummy");
    $keysArgs = [
      'SEARCH_UUID' => getenv("SEARCH_UUID"),
    ];
    $keys = $this->getKeys($bundle);
    $this->assertEquals($keys, $this->installerQuestions->getKeyPair($this->acmsQuestions, $bundle, $keysArgs));
  }

  /**
   * Data provider for ::testGetKeyPair().
   *
   * @return array[]
   *   Sets of arguments to pass to the test method.
   */
  public function providerBundle() :array {
    return [
      ['acquia_cms_minimal'],
      ['acquia_cms_standard'],
      ['acquia_cms_low_code'],
      ['acquia_cms_demo'],
    ];
  }

  /**
   * Data provider for ::testGetKeyPair().
   *
   * @return array[]
   *   Sets of arguments to pass to the test method.
   */
  public function getQuestions(string $bundle) :array {
    $keys = [];
    switch ($bundle) {
      case 'acquia_cms_demo':
        $keys['CONNECTOR_ID'] = [
          "question" => "Please provide the Acquia Connector ID",
          "required" => TRUE,
        ];
        $keys['GMAPS_KEY'] = [
          "question" => "Please provide the Google Maps API Key",
          "required" => TRUE,
        ];
      case 'acquia_cms_low_code':
        $keys['SITESTUDIO_API_KEY'] = [
          "question" => "Please provide the Site Studio API Key",
          "required" => TRUE,
        ];
        $keys['SITESTUDIO_ORG_KEY'] = [
          "question" => "Please provide the Site Studio Organization Key",
          "required" => TRUE,
        ];
      case 'acquia_cms_minimal':
      case 'acquia_cms_standard':
        $keys['SEARCH_UUID'] = [
          "question" => "Please provide the Acquia CMS Search UUID",
          "required" => TRUE,
        ];
        break;
    }
    return $keys;
  }

  /**
   * Data provider for ::testGetKeyPair().
   *
   * @return array[]
   *   Sets of arguments to pass to the test method.
   */
  public function getKeys(string $bundle) :array {
    $keys = [];
    switch ($bundle) {
      case 'acquia_cms_demo':
        if (getenv("CONNECTOR_ID")) {
          $keys["CONNECTOR_ID"] = getenv("CONNECTOR_ID");
        }
        if (getenv("GMAPS_KEY")) {
          $keys["GMAPS_KEY"] = getenv("GMAPS_KEY");
        }
      case 'acquia_cms_low_code':
        if (getenv("SITESTUDIO_API_KEY")) {
          $keys["SITESTUDIO_API_KEY"] = getenv("SITESTUDIO_API_KEY");
        }
        if (getenv("SITESTUDIO_ORG_KEY")) {
          $keys["SITESTUDIO_ORG_KEY"] = getenv("SITESTUDIO_ORG_KEY");
        }
      case 'acquia_cms_minimal':
      case 'acquia_cms_standard':
        if (getenv("SEARCH_UUID")) {
          $keys["SEARCH_UUID"] = getenv("SEARCH_UUID");
        }
        break;
    }
    return $keys;
  }

}
