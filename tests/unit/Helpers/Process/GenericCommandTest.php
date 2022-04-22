<?php

namespace tests\Helpers\Process;

use AcquiaCMS\Cli\Helpers\Process\Commands\Generic;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

class GenericCommandTest extends TestCase {
  use ProphecyTrait;

  /**
   * Holds the symfony console output object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $output;

  /**
   * A process manager object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Generic
   */
  protected $genericCommand;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $output = $this->prophesize(OutputInterface::class);
    $this->output = $output->reveal();
    $this->genericCommand = new Generic(getcwd(), $this->output);
  }

  /**
   * Test exception for generic command.
   */
  public function testExceptionCommand() :void {
    $this->expectExceptionMessage("Command can not be empty. Provide command name. Ex: drush, php etc.");
    $this->genericCommand->prepare()->run();
  }

  public function testExecuteCommand() :void {
    $this->genericCommand->setCommand('echo');
    $status = $this->genericCommand->prepare([""])->run();
    $this->assertIsInt($status);
    $this->assertEquals(0, $status);
  }

  public function testExecuteCommandQuietly() :void {
    $this->genericCommand->setCommand('echo');
    $output = $this->genericCommand->prepare(["Command Runs"])->runQuietly();
    $this->assertIsString($output);
    $this->assertEquals("Command Runs" . PHP_EOL, $output);
  }

  /**
   * Execute the php having environment variables.
   *
   * @param array $actual
   *   An actual array of command to execute.
   * @param string $expected
   *   An expected string output command.
   *
   * @dataProvider dataProviderWithEnvVariable
   */
  public function testExecuteCommandWithEnvironmentVariables(array $actual, string $expected) :void {
    $this->genericCommand->setCommand('php');
    $output = $this->genericCommand->prepare([
      '-r',
      'echo "' . $actual[0] . '";',
    ])->runQuietly($actual[1]);
    $this->assertIsString($output);
    $this->assertEquals($expected, $output);
  }

  /**
   * An array of some commands to execute.
   */
  public function dataProviderWithEnvVariable() :array {
    return [
      [
        [
          'The Project: " . getenv("PROJECT") . " is awesome.',
          [
            'PROJECT' => 'acquia/acquia-cms-starterkit',
          ],
        ],
        'The Project: acquia/acquia-cms-starterkit is awesome.',
      ],
      [
        [
          'Site Studio credentials are: " . getenv("API_KEY") . ":" . getenv("API_SECRET") . ".',
          [
            'API_KEY' => 'internal-294849',
            'API_SECRET' => 'xiUkj94&uDvfGeRT',
          ],
        ],
        'Site Studio credentials are: internal-294849:xiUkj94&uDvfGeRT.',
      ],
    ];
  }

}
