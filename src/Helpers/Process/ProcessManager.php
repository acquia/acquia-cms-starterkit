<?php

namespace AcquiaCMS\Cli\Helpers\Process;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Process;

class ProcessManager {
  protected $process;
  protected $output;
  public function __construct(OutputInterface $output) {
    $this->output = $output;
  }

  public function add(array $command) {
    $process = new Process($command);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL)
      ->setTty(Process::isTtySupported());
    $this->process[] = $process;
  }

  public function getLastProcess() {
    return reset($this->process);
  }

  public function getAllProcess() {
    return $this->process;
  }
  public function runAll() {
    $status = TRUE;
    foreach ($this->getAllProcess() as $process) {
      $status = $this->run();
      if (!$status) {
        $status = FALSE;
        break;
      }
    }
    return $status;
  }

  public function run() {
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
