<?php

namespace AcquiaCMS\Cli\FileSystem;

use AcquiaCMS\Cli\Exception\ListException;
use AcquiaCMS\Cli\FileSystem\Validator\StarterKitValidator;

/**
 * Class to manage StarterKit file.
 */
class StarterKitFile extends File {

  /**
   * {@inheritdoc}
   */
  public function validate(): bool {
    parent::validate();
    $validator = new StarterKitValidator();
    $validator->validate($this->getContent());
    if ($validator->getErrors()) {
      $message = [];
      foreach ($validator->getErrors() as $key => $error) {
        $message[$key] = $this->parseError($error);
      }
      throw new ListException(
        sprintf(
          "Please fix all errors in the file: '<fg=white;options=underscore>%s</>'",
          $this->file->getPathname()
        ),
        $message
      );
    }
    return TRUE;
  }

}
