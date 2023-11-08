<?php

namespace AcquiaCMS\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class for manage input and output object.
 */
class ConsoleIO {

  /**
   * The symfony input object.
   *
   * @var \Symfony\Component\Console\Input\InputInterface
   */
  private $input;

  /**
   * The symfony output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  private $output;

  /**
   * Sets the input & output object.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input object.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output object.
   */
  public function setInputOutput(InputInterface $input, OutputInterface $output): void {
    $this->input = $input;
    $this->output = $output;
  }

  /**
   * Return Symfony Input object.
   */
  public function getInput(): InputInterface {
    return $this->input;
  }

  /**
   * Return Symfony Output object.
   */
  public function getOutput(): OutputInterface {
    return $this->output;
  }

}
