<?php

namespace AcquiaCMS\Cli\Exception;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * A general Acquia CMS cli exception.
 */
class ListException extends \Exception {

  /**
   * An array of data.
   *
   * @var array
   */
  private array $data;

  /**
   * The given error message.
   *
   * @var string
   */
  private string $errorMessage;

  /**
   * {@inheritdoc}
   */
  public function __construct(string $message = "", array $data = [], int $code = 0, \Throwable $previous = NULL) {
    $this->data = $data;
    $this->errorMessage = $message;
    $message = str_replace(PHP_EOL, PHP_EOL . " ", $message);
    $this->errorMessage = " <fg=white;bg=red;options=bold>[error]</> " . $message;
    parent::__construct($message, $code, $previous);
  }

  /**
   * Displays the error message.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The input object.
   */
  public function displayMessage(OutputInterface $output): void {
    $formatter = $output->getFormatter();
    $output->writeln($formatter->format($this->errorMessage));
    foreach ($this->data as $key => $error) {
      if (is_string($key)) {
        $output->writeln($formatter->format("  - " . $key . ":"));
        foreach ($error as $value) {
          $output->writeln($formatter->format("    * " . $value));
        }
        $output->writeln("");
      }
      else {
        $output->writeln($formatter->format("    * " . $error));
      }
    }
  }

}
