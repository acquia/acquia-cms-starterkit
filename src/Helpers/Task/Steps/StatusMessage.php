<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the class to style message printed on cli.
 */
class StatusMessage {

  const TYPE_HEADLINE = "headline";
  const TYPE_WARNING = "warning";
  const TYPE_SUCCESS = "success";

  /**
   * Holds the symfony console output object.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * Constructs an object.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Holds the symfony console output object.
   */
  public function __construct(OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * Print the message to cli.
   */
  public function print(string $message, string $type) :void {
    switch ($type) {
      case self::TYPE_HEADLINE:
        $this->output->writeln("");
        $this->output->writeln("<fg=green;>" . $message . "</>");
        $this->output->writeln(str_repeat("-", strlen($message)));
        break;

      case self::TYPE_WARNING:
        $this->output->writeln("");
        $this->output->writeln("<comment>" . $message . "</comment>");
        break;

      case self::TYPE_SUCCESS:
        $this->output->writeln("");
        $this->output->writeln("<info>" . $message . "</info>");
        break;
    }
  }

}
