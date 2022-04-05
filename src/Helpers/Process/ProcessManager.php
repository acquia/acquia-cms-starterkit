<?php

namespace AcquiaCMS\Cli\Helpers\Process;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * ProcessManager class to add & execute different commands.
 */
class ProcessManager {

  /**
   * An array of process object.
   *
   * @var array
   */
  protected $process;

  /**
   * A Symfony console output interface object.
   *
   * @var Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * Constructs an object.
   *
   * @param Symfony\Component\Console\Output\OutputInterface $output
   *   Hold the symfony console output object.
   */
  public function __construct(OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * Adds the process command to an array.
   *
   * @param array $command
   *   An array of commands to execute.
   */
  public function add(array $command) {
    $process = new Process($command);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL)
      ->setTty(Process::isTtySupported());
    $this->process[] = $process;
  }

  /**
   * Returns the last inserted process to provide ability to alter.
   */
  public function getLastProcess() {
    return reset($this->process);
  }

  /**
   * Returns an array of process commands array.
   */
  public function getAllProcess() {
    return $this->process;
  }

  /**
   * Executes all process commands from the array.
   */
  public function runAll() {
    $status = TRUE;
    foreach ($this->getAllProcess() as $process) {
      $status = $this->run($process);
      if (!$status) {
        $status = FALSE;
        break;
      }
    }
    return $status;
  }

  /**
   * Executes the command from the process array.
   *
   * @param Symfony\Component\Process\Process $process
   *   A Process object.
   */
  public function run(Process $process = NULL) {
    $process = array_shift($this->process);
    $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
    $process->start();
    $process->wait(function ($type, $buffer) {
      if (Process::ERR != "err") {
        $this->output->writeln($buffer);
      }
    });
    if (!$process->isSuccessful()) {
      return FALSE;
    };
    return TRUE;
  }

}
