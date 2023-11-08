<?php

namespace AcquiaCMS\Cli\FileSystem;

use AcquiaCMS\Cli\Exception\ErrorException;
use AcquiaCMS\Cli\FileSystem\Validator\ValidatorInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class to manage yaml files.
 */
class File implements FileInterface {

  /**
   * Holds file info object.
   *
   * @var \Symfony\Component\Finder\SplFileInfo
   */
  protected $file;

  /**
   * Holds an array of file contents.
   *
   * @var array
   */
  protected $content;

  /**
   * Holds type of file.
   *
   * @var string
   */
  protected $type;

  /**
   * Construct file object.
   */
  public function __construct(SplFileInfo $file) {
    $this->file = $file;
    $this->type = $file->getFilenameWithoutExtension();
    $this->content = [];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function validate(): bool {
    try {
      $this->content = Yaml::parse($this->file->getContents());
    }
    catch (ParseException $e) {
      throw new ErrorException(
        sprintf("The file '<fg=white;options=underscore>%s</>' is invalid and contain syntax error." . PHP_EOL . "%s",
          $this->file->getPathname(),
          $e->getMessage()
        )
          );
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent(): array {
    return $this->content;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile(): SplFileInfo {
    return $this->file;
  }

  /**
   * {@inheritdoc}
   */
  protected function parseError(array $errors): array {
    $message = [];
    foreach ($errors as $key => $error) {
      switch ($error['type']) {
        case ValidatorInterface::KEY_NOT_PRESENT_ERROR;
          $message[] = sprintf(
            "Key '%s' not present.",
            $key,
          );
          break;

        case ValidatorInterface::INVALID_DATA_TYPE_ERROR;
          $message[] = sprintf(
            "The value for key '%s' should be '%s', but '%s' was provided.",
            $key,
            $error['expected'],
            $error['actual'],
          );
          break;
      }
    }
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->type;
  }

}
