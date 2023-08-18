<?php

namespace AcquiaCMS\Cli\Helpers\Traits;

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
    return $command_type === 'install' ? array_filter($options) :
    array_filter($options, function ($option) {
      return $option === 'yes';
    });
  }

  /**
   * Get drush input options.
   */
  public function getDrushOptions(): array {
    return [
      'db-url' => [
        'description' => "A Drupal 6 style database URL. Required for initial install, not re-install. If omitted and required, Drush prompts for this item.",
        'default_value' => "",
      ],
      'db-prefix' => [
        'description' => "An optional table prefix to use for initial install.",
        'default_value' => "",
      ],
      'db-su' => [
        'description' => "Account to use when creating a new database. Must have Grant permission (mysql only). Optional.",
        'default_value' => "",
      ],
      'db-su-pw' => [
        'description' => "Password for the <info>db-su</info> account. Optional.",
        'default_value' => "",
      ],
      'account-name' => [
        'description' => "User ID 1 name.",
        'default_value' => "admin",
      ],
      'account-mail' => [
        'description' => "User ID 1 email.",
        'default_value' => "no-reply@example.com",
      ],
      'site-mail' => [
        'description' => "<info>From</info>: for system mailings.",
        'default_value' => "no-reply@example.com",
      ],
      'account-pass' => [
        'description' => "User ID 1 pass. Defaults to a randomly generated password.",
        'default_value' => "",
      ],
      'locale' => [
        'description' => "A short language code. Sets the default site language. Language files must already be present.",
        'default_value' => "en",
      ],
      'site-name' => [
        'description' => "Name of the Drupal site.",
        'default_value' => "Acquia CMS",
      ],
      'site-pass' => [
        'description' => "Application password. Defaults to a randomly generated password.",
        'default_value' => "",
      ],
      'site-subdir' => [
        'description' => "Name of directory under <info>sites</info> which should be created.",
        'default_value' => "",
      ],
      'existing-config' => [
        'description' => "Configuration from <info>sync</info> directory should be imported during installation.",
        'default_value' => 'none',
      ],
      'uri' => [
        'description' => "Multisite uri to setup drupal site.",
        'default_value' => "default",
        'alias' => "l",
      ],
      'yes' => [
        'description' => "Equivalent to --no-interaction.",
        'default_value' => 'none',
        'alias' => "y",
      ],
      'no' => [
        'description' => "Cancels at any confirmation prompt.",
        'default_value' => 'none',
      ],
      'hide-command' => [
        'description' => "Hide Command. Doesn't show the command executed on terminal.",
        'default_value' => 'none',
        'alias' => "hide",
      ],
      'display-command' => [
        'description' => "Display Command. Doesn't show the command executed on terminal.",
        'default_value' => 'none',
        'alias' => "d",
      ],
      'without-product-info' => [
        'description' => "Doesn't show the product logo and headline.",
        'default_value' => "none",
        'alias' => "wpi",
      ],
    ];

  }

  /**
   * Filter input install options.
   *
   * @param array $options
   *   Install input options.
   * @param string|null $starterkit
   *   Starter kit name.
   *
   * @return array
   *   Filtered input options.
   */
  public function filterInputOptions(array $options, ?string $starterkit = ''): array {
    switch ($starterkit) {
      case 'acquia_cms_headless':
        if ($options['nextjs-app'] === 'no') {
          unset($options['nextjs-app']);
          unset($options['nextjs-app-site-url']);
          unset($options['nextjs-app-site-name']);
          unset($options['nextjs-app-env-file']);
        }
        unset($options['sitestudio-api-key']);
        unset($options['sitestudio-org-key']);
        break;

      case 'acquia_cms_enterprise_low_code':
        unset($options['nextjs-app']);
        unset($options['nextjs-app-site-url']);
        unset($options['nextjs-app-site-name']);
        break;

      case 'acquia_cms_community':
        unset($options['nextjs-app']);
        unset($options['nextjs-app-site-url']);
        unset($options['nextjs-app-site-name']);
        unset($options['sitestudio-api-key']);
        unset($options['sitestudio-org-key']);
        break;

      default:
        unset($options['demo-content']);
        unset($options['content-model']);
        unset($options['dam-integration']);
        unset($options['gdpr-integration']);
        unset($options['nextjs-app']);
        unset($options['nextjs-app-site-url']);
        unset($options['nextjs-app-site-name']);
        unset($options['nextjs-app-env-file']);
        unset($options['sitestudio-api-key']);
        unset($options['sitestudio-org-key']);
        unset($options['gmaps-key']);
        unset($options['without-product-info']);
        unset($options['no-interaction']);
        unset($options['uri']);
        break;
    }

    return $options;
  }

}
