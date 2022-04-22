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
  public function run() :int;

  /**
   * Executes the command quietly (without printing command on terminal).
   *
   * @return string
   *   Return the executed command output.
   */
  public function runQuietly() :string;

  /**
   * Return the base command name.
   *
   * @return string
   *   Returns the command.
   */
  public function getCommand(): string;

  /**
   * Adds the command arguments prefix.
   *
   * @return array
   *   Return the commands to prepend.
   */
  public function prefix(): array;

  /**
   * Adds the command arguments suffix.
   *
   * @return array
   *   Return the commands to append to the last.
   */
  public function suffix(): array;

}
