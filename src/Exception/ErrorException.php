<?php

namespace AcquiaCMS\Cli\Exception;

/**
 * A general Acquia CMS cli error exception.
 */
class ErrorException extends \Exception {

  /**
   * {@inheritdoc}
   */
  public function __construct(string $message = "", int $code = 0, \Throwable $previous = NULL) {
    $message = str_replace(PHP_EOL, PHP_EOL . " ", $message);
    $message = " <fg=white;bg=red;options=bold>[error]</> " . $message . "";
    parent::__construct($message, $code, $previous);
  }

}
