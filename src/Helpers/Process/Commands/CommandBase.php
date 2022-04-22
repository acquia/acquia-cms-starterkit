<?php

namespace AcquiaCMS\Cli\Helpers\Process\Commands;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * BaseCommand to execute any command on shell.
 */
class CommandBase implements CommandInterface {

  /**
   * A Symfony process class object.
   *
   * @var \Symfony\Component\Process\Process
   */
  protected $process;

  /**
   * A Symfony console output class opbject.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * A base command name/path to execute.
   *
   * @var string
   */
  protected $command;

  /**
   * Root directory path of the project.
   *
   * @var string
   */
  protected $rootDir;

  /**
   * A class constructor.
   *
   * @param string $root_dir
   *   Root directory path.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   A Symfony output class object.
   */
  public function __construct(string $root_dir, OutputInterface $output) {
    $this->command = '';
    $this->rootDir = $root_dir;
    $this->output = $output;
  }

  /**
   * Sets the base command name/path.
   *
   * @param string $path
   *   Sets the base command.
   */
  public function setCommand(string $path) :void {
    $this->command = $path;
  }

  /**
   * Prepares the command to execute.
   *
   * @param array $commands
   *   An array of commands.
   *
   * @return CommandInterface
   *   Returns the command object.
   */
  public function prepare(array $commands = []) : CommandInterface {
    try {
      $commands = array_merge(
        [$this->getCommand()],
        $commands,
      );
      $this->process = new Process($commands);
      $this->process->setTimeout(NULL)
        ->setIdleTimeout(NULL)
        ->setTty(Process::isTtySupported())
        ->setWorkingDirectory($this->rootDir);
    }
    catch (\Exception $e) {
      print $e->getMessage() . PHP_EOL;
      die;
    }
    return $this;
  }

  /**
   * Executes the command on terminal.
   *
   * @return int
   *   Returns the command output status code.
   */
  public function run(array $env = []) :int {
    $this->output->writeln(sprintf('> %s', $this->process->getCommandLine()));
    $status = $this->process->run(function ($type, $buffer) {
      if (Process::ERR != $type) {
        $this->output->writeln($buffer);
      }
    }, $env);
    $this->verifyCommand();
    return $status;
  }

  /**
   * Executes the command silently on terminal.
   *
   * @return string
   *   Returns the executed command output.
   */
  public function runQuietly(array $env = []) :string {
    $this->process->setTty(FALSE);
    $this->process->run(NULL, $env);
    $this->verifyCommand();
    return $this->process->getOutput();
  }

  /**
   * Verify if command executed successfully.
   */
  protected function verifyCommand() :void {
    if (!$this->process->isSuccessful()) {
      $this->process->disableOutput();
      throw new ProcessFailedException($this->process);
    }
  }

  /**
   * Gets base command name/path.
   *
   * @return string
   *   Returns the base command.
   *
   * @throws \Exception
   *   Throws exception if command is empty.
   */
  public function getCommand(): string {
    if (empty($this->command)) {
      throw new \Exception("Command can not be empty. Provide command name. Ex: drush, php etc.");
    }
    return $this->command;
  }

}
