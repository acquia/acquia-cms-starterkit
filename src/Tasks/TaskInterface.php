<?php

namespace AcquiaCMS\Cli\Tasks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface defining the task object.
 * @phpcs:disable Drupal.Commenting.DocComment.ShortSingleLine
 */
interface TaskInterface {

  /**
   * This should be used, if there are some additional task/command that needs
   * to run before running the command. Ex: If task headline to display on
   * terminal. Or there are some validation that needs to run before running
   * the command.
   *
   * This must return from below Status Code:
   * StatusCode::OK: Ok, so continue to run execute() command.
   * StatusCode::Skip: Stop & continue to next task.
   *   DO NOT run execute() or postExecute().
   * StatusCode::Exit: Something happened, so STOP & EXIT.
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int;

  /**
   * This should be used to run the actual task the needs to execute.
   *
   * This must return from below Status Code:
   * StatusCode::OK: Ok, so continue to run postExecute() command.
   * StatusCode::Skip: Stop & continue to next task. DO NOT run postExecute().
   * StatusCode::Exit: Something happened, so STOP & EXIT.
   */
  public function execute(InputInterface $input, OutputInterface $output): int;

  /**
   * This should be used to run the commands/tasks that needs to execute
   * post running the actual command. Ex: Display something on terminal if
   * task passed/failed etc.
   *
   * This must return from below Status Code:
   * StatusCode::OK: Ok and continue to next task.
   * StatusCode::Exit: Something happened, so STOP & EXIT.
   */
  public function postExecute(InputInterface $input, OutputInterface $output): int;

  /**
   * Instantiates a new instance of this class.
   *
   * This is a factory method that returns a new instance of this class. The
   * factory should pass any needed dependencies into the constructor of this
   * class, but not the container itself. Every call to this method must return
   * a new instance of this class; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   The command object which invoke this task.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface;

}
