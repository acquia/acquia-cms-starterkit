<?php

namespace AcquiaCMS\Cli\Helpers;

/**
 * The composer packages interface.
 */
interface PackagesInterface {

  /**
   * Returns an array of installed packages.
   *
   * @param bool $reset
   *   Decide if reset.
   *
   * @return array
   *   An array of packages.
   */
  public function getInstalledPackages(bool $reset = FALSE): array;

}
