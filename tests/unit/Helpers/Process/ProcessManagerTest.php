<?php

namespace tests\Helpers\Process;

use AcquiaCMS\Cli\Helpers\Process\ProcessManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ProcessManagerTest extends TestCase {
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
   * @var \AcquiaCMS\Cli\Helpers\Process\ProcessManager
   */
  protected $processManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $output = $this->prophesize(OutputInterface::class);
    $this->output = $output->reveal();
    $this->processManager = new ProcessManager($this->output);
  }

  /**
   * Tests the method: add() for ProcessManager class.
   */
  public function testAdd() :void {
    $process = [];
    foreach ($this->getAllProcessCommands() as $commands) {
      $process[] = $this->addProcess($commands);
      $this->processManager->add($commands);
    }
    $this->assertEquals($this->processManager->getAllProcess(), $process);
  }

  /**
   * Tests the method: getLastProcess() for ProcessManager class.
   */
  public function testLastProcess() :void {
    $this->assertNull($this->processManager->getLastProcess());
    $this->processManager->add($this->getAllProcessCommands()[0]);
    $lastProcess = $this->processManager->getLastProcess();
    $this->assertInstanceOf(Process::class, $lastProcess);
  }

  /**
   * Tests the method: runAll() for ProcessManager class.
   */
  public function testExecuteAllProcesses() :void {
    $this->processManager->add($this->getAllProcessCommands()[0]);
    $lastProcess = $this->processManager->getLastProcess();
    $this->assertInstanceOf(Process::class, $lastProcess);
    $lastProcess->setTty(FALSE);
    $this->assertTrue($this->processManager->runAll());
  }

  /**
   * Tests the method: run() for ProcessManager class.
   */
  public function testExecuteOneProcess() :void {
    $this->processManager->add($this->getAllProcessCommands()[1]);
    $lastProcess = $this->processManager->getLastProcess();
    $this->assertInstanceOf(Process::class, $lastProcess);
    $lastProcess->setTty(FALSE);
    $this->assertTrue($this->processManager->run($lastProcess));
  }

  /**
   * An array of some commands to execute.
   */
  private function getAllProcessCommands() :array {
    return [
      [
        "ls", "-ltra",
      ],
      [
        "echo", "test",
      ],
      [
        "echo", "acquia_cms_article",
      ],
    ];
  }

  /**
   * Adds the process commands in an array and return Process class pbject.
   *
   * @param array $commands
   *   An array of commands to execute.
   *
   * @return \Symfony\Component\Process\Process
   *   Return the Process class object.
   */
  private function addProcess(array $commands) :Process {
    $process = new Process($commands);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL)
      ->setTty(Process::isTtySupported());
    return $process;
  }

}
