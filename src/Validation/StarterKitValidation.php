<?php

namespace AcquiaCMS\Cli\Validation;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Validation service to validate each key in starter kit.
 */
class StarterKitValidation {

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
   *   Returns an absolute root path to project.
   */
  public function __construct(
    OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * Validates each key value for starter-kit.
   */
  public function validateStarterKit(array &$starterkits): array {
    foreach ($starterkits as $starterkit) {
      if (empty($starterkit['name']) || !is_string($starterkit['name'])) {
        $this->output->writeln(sprintf(
          '<error>[error] Name field is mandatory and should be in string format.</error>'
        ));
        exit;
      }
      if (empty($starterkit['description']) || !is_string($starterkit['description'])) {
        $this->output->writeln(sprintf(
          '<error>[error] Description field is mandatory and should be in string format.</error>'
        ));
        exit;
      }
      if (isset($starterkit['modules']) && (!is_array($starterkit['modules']['require']) || !is_array($starterkit['modules']['install']))) {
        $this->output->writeln(sprintf(
          '<error>[error] Modules field is not in array format.</error>'
        ));
        exit;
      }
      if (!is_array($starterkit['themes']['require']) ||
        !is_array($starterkit['themes']['install']) ||
        !is_string($starterkit['themes']['admin']) ||
        !is_string($starterkit['themes']['default'])) {
        $this->output->writeln(sprintf(
          '<error>[error] Themes field is not in array format or doesnt contain string format.</error>'
        ));
        exit;
      }
    }

    return $starterkits;
  }

}
