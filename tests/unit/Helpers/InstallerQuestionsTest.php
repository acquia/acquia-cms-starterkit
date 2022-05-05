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
    $this->assertEquals($questions, $this->installerQuestions->getQuestions($this->acmsQuestions, $bundle));
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
        'acquia_cms_demo',
        array_merge(
          CliTest::getConnectorId(),
          CliTest::getGmapsKey(),
          CliTest::getSearchUuid(),
          CliTest::getSiteStudioApiKey(),
          CliTest::getSiteStudioOrgKey(),
        ),
      ],
      [
        'acquia_cms_low_code',
        array_merge(
          CliTest::getSearchUUID(),
          CliTest::getSiteStudioApiKey(),
          CliTest::getSiteStudioOrgKey()
        ),
      ],
      [
        'acquia_cms_standard_site_studio',
        CliTest::getSearchUUID(),
        CliTest::getSiteStudioApiKey(),
        CliTest::getSiteStudioOrgKey()
      ],
      [
        'acquia_cms_standard',
        CliTest::getSearchUUID(),
      ],
      [
        'acquia_cms_minimal',
        CliTest::getSearchUUID(),
      ],
      [
        'acquia_cms_headless',
        [],
      ],
    ];
  }

}
