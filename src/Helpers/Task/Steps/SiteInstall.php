<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

use AcquiaCMS\Cli\Helpers\Process\Commands\Drush;
use AcquiaCMS\Cli\Helpers\Task\SharedFactory;

/**
 * Run the drush command to install Drupal site.
 */
class SiteInstall {

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Drush
   */
  public $drushCommand;

  /**
   * Site name.
   *
   * @var string
   */
  public string $siteName;

  /**
   * Account password.
   *
   * @var string
   */
  protected string $password;

  /**
   * Constructs an object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Drush $drush
   *   Holds the drush command class object.
   */
  public function __construct(Drush $drush) {
    $this->drushCommand = $drush;
    SharedFactory::setData('password');
    $this->password = SharedFactory::getData('password');
  }

  /**
   * Run the drush commands to install Drupal.
   *
   * @param array $args
   *   An array of params argument to pass.
   */
  public function execute(array $args = []): int {
    // Handle the account password.
    if (array_key_exists('account-pass', $args)) {
      $this->password = $args['account-pass'];
      unset($args['account-pass']);
    }

    $this->siteName = array_key_exists('site-name', $args) ?
    $args['site-name'] : $args['name'];
    unset($args['name']);
    unset($args['site-name']);

    // Prepare site install command data.
    $siteInstallCommand = [
      "site:install",
      "minimal",
      "--site-name=$this->siteName",
      "--account-pass=$this->password",
    ];

    // Remove without-product-info from argument as it is only used internally.
    unset($args['without-product-info']);

    // Iterate arguments i.e options to prepare for site install.
    foreach ($args as $key => $value) {
      if ($value != "true") {
        $siteInstallCommand[] = "--" . $key . "=" . $value;
      }
      else {
        $siteInstallCommand[] = "--" . $key;
      }
    }

    return $this->drushCommand->prepare($siteInstallCommand)->run();
  }

}
