<?php

namespace AcquiaCMS\Cli\Helpers\Traits;

/**
 * Provides the class to style message printed on cli.
 */
trait StatusMessageTrait {

  /**
   * Print the message to cli.
   */
  public function style(string $message, ?string $type) :array {
    $messages = [];
    switch ($type) {
      case "headline":
        $messages = [
          '',
          "<fg=green;>" . $message . "</>",
          str_repeat("-", strlen($message)),
        ];
        break;

      case "warning":
        $messages = [
          '',
          "<comment>" . $message . "</comment>",
        ];
        break;

      case "success":
        $messages = [
          '',
          "<info>" . $message . "</info>",
        ];
        break;

      default:
        $messages[] = $message;
    }
    return $messages;
  }

}
