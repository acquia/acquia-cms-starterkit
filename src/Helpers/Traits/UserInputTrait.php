<?php

namespace AcquiaCMS\Cli\Helpers\Traits;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Provides the trait for user input questions.
 */
trait UserInputTrait {

  /**
   * Style the question to print on cli.
   */
  public function styleQuestion(string $question, string $default_value = '', bool $required = FALSE, bool $new_line = FALSE) :string {
    $message = " <info>$question</info>";
    if (!$default_value && $required) {
      $message .= "<fg=red;options=bold> *</>";
    }
    if ($default_value) {
      $message .= " <comment>[$default_value]</comment>";
    }
    $message .= ":" . PHP_EOL . " > ";
    return ($new_line) ? PHP_EOL . $message : $message;
  }

  /**
   * Get user input options.
   *
   * @param array $options
   *   List of user and default inputs.
   * @param string $command_type
   *   Command type whether its build or install.
   *
   * @return array
   *   Filter input options.
   */
  public function getInputOptions(array $options, string $command_type): array {
    $output = [];
    if ($command_type === 'install') {
      $inputOptions = array_filter($options);
    }
    else {
      $inputOptions = array_filter($options, function ($option) {
        return $option === 'yes';
      });
    }

    foreach ($inputOptions as $key => $value) {
      $arg = str_replace('enable-', '', $key);
      $output[$arg] = $value;
    }

    return $output;
  }

  /**
   * Get drush input options.
   */
  public function getDrushOptions(): array {
    return [
      new InputArgument('profile', InputArgument::IS_ARRAY,
        "An install profile name. Defaults to <info>minimal</info> unless an install profile is marked as a distribution. " . PHP_EOL .
      "Additional info for the install profile may also be provided with additional arguments. The key is in the form [form name].[parameter name]"),
      new InputOption('db-url', '', InputOption::VALUE_OPTIONAL, "A Drupal 6 style database URL. Required for initial install, not re-install. If omitted and required, Drush prompts for this item."),
      new InputOption('db-prefix', '', InputOption::VALUE_OPTIONAL, "An optional table prefix to use for initial install."),
      new InputOption('db-su', '', InputOption::VALUE_OPTIONAL, "Account to use when creating a new database. Must have Grant permission (mysql only). Optional."),
      new InputOption('db-su-pw', '', InputOption::VALUE_OPTIONAL, "Password for the <info>db-su</info> account. Optional."),
      new InputOption('account-name', '', InputOption::VALUE_OPTIONAL, "uid1 name.", 'admin'),
      new InputOption('account-mail', '', InputOption::VALUE_OPTIONAL, "uid1 email.", 'no-reply@example.com'),
      new InputOption('site-mail', '', InputOption::VALUE_OPTIONAL, "<info>From</info>: for system mailings.", 'no-reply@example.com'),
      new InputOption('account-pass', '', InputOption::VALUE_OPTIONAL, "uid1 pass. Defaults to a randomly generated password."),
      new InputOption('locale', '', InputOption::VALUE_OPTIONAL, "A short language code. Sets the default site language. Language files must already be present.", 'en'),
      new InputOption('site-name', '', InputOption::VALUE_OPTIONAL, "Name of the Drupal site.", 'Acquia CMS'),
      new InputOption('site-pass', '', InputOption::VALUE_OPTIONAL),
      new InputOption('sites-subdir', '', InputOption::VALUE_OPTIONAL, "Name of directory under <info>sites</info> which should be created."),
      new InputOption('existing-config ', '', InputOption::VALUE_NONE, "Configuration from <info>sync</info> directory should be imported during installation."),
      new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
      new InputOption('yes', 'y', InputOption::VALUE_NONE, "Equivalent to --no-interaction."),
      new InputOption('no', '', InputOption::VALUE_NONE, "Cancels at any confirmation prompt."),
      new InputOption('hide-command', 'hide', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
      new InputOption('display-command', 'd', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
      new InputOption('without-product-info', 'wpi', InputOption::VALUE_NONE, "Doesn't show the product logo and headline."),
    ];
  }

}
