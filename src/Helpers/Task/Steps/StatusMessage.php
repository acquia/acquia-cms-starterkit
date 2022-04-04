<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use Symfony\Component\Console\Style\SymfonyStyle;

class StatusMessage {

  CONST TYPE_HEADLINE = "headline";
  CONST TYPE_WARNING = "warning";
  CONST TYPE_SUCCESS = "success";

  public function __construct(SymfonyStyle $output) {
    $this->output = $output;
  }
  public function print($message, $type) {
      switch ($type) {
        case self::TYPE_HEADLINE:
          $this->output->newLine();
          $this->output->writeln("<fg=green;>" . $message . "</>");
          $this->output->writeln(str_repeat("-", strlen($message)));
          break;
        case self::TYPE_WARNING:
          $this->output->newLine();
          $this->output->writeln("<comment>" . $message . "</comment>");
          break;
        case self::TYPE_SUCCESS:
          $this->output->newLine();
          $this->output->writeln("<info>" . $message . "</info>");
          break;
      }
    }
}
