<?php

namespace tests\Helpers\Process;

use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerCommandTest extends TestCase {
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
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $output = $this->prophesize(OutputInterface::class);
    $this->output = $output->reveal();
    $this->composerCommand = new Composer(getcwd(), $this->output);
  }

  /**
   * Test composer version command.
   */
  public function testExecuteCommand() :void {
    $output = $this->composerCommand->prepare(["--version"])->runQuietly();
    $this->assertIsString($output);
    $this->assertStringStartsWith("Composer", $output);
  }

  /**
   * Test failed/invalid execute command.
   */
  public function testException() :void {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessageMatches('/The command "\'.*\'xyz\'" failed./');
    $this->composerCommand->prepare(["xyz"])->runQuietly();
  }

}
