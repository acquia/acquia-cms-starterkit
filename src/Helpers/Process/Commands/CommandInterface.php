<?php

declare(strict_types=1);

namespace AcquiaCMS\Cli\Helpers\Process\Commands;

/**
 * Describes the interface for the shell commands.
 */
interface CommandInterface {

  /**
   * Sets the command name to execute. Ex: `export`, `echo` etc.
   *
   * @param string $path
   *   The command name.
   */
  public function setCommand(string $path): void;

  /**
   * Prepares the commands to execute.
   *
   * @param array $commands
   *   An array of command arguments to execute.
   *
   * @return CommandInterface
   *   Returns the current command object.
   */
  public function prepare(array $commands): CommandInterface;

  /**
   * Executes the command on shell.
   *
   * @return int
   *   Returns 0 (for success), 1 (for failure)
   */
  public function run(array $env = []) :int;

  /**
   * Executes the command quietly (without printing command on terminal).
   *
   * @return string
   *   Return the executed command output.
   */
  public function runQuietly(array $env = []) :string;

  /**
   * Return the base command name.
   *
   * @return string
   *   Returns the command.
   */
  public function getCommand(): string;

}
