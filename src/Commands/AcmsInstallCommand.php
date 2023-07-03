<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Helpers\Process\Commands\Generic;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code
 *   ./vendor/bin/acms acms:install
 * @endcode
 */
class AcmsInstallCommand extends Command {

  use StatusMessageTrait;
  use UserInputTrait;

  /**
   * A drush command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Generic
   */
  protected $genericCommand;

  /**
   * User selected bundle.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $filesystem;

  /**
   * Constructs an instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A Symfony container class object.
   */
  public function __construct(ContainerInterface $container) {
    $this->genericCommand = $container->get(Generic::class);
    $this->filesystem = $container->get(Filesystem::class);
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("acms:install")
      ->setDescription("Use this command to setup & install site.")
      ->setDefinition([
        new InputOption('name', '', InputOption::VALUE_OPTIONAL, "Name of the starter kit"),
        new InputOption('uri', 'l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site.", 'default'),
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
        new InputOption('existing-config', '', InputOption::VALUE_NONE, "Configuration from <info>sync</info> directory should be imported during installation."),
        new InputOption('yes', 'y', InputOption::VALUE_NONE, "Equivalent to --no-interaction."),
        new InputOption('no', '', InputOption::VALUE_NONE, "Cancels at any confirmation prompt."),
        new InputOption('hide-command', 'hide', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
        new InputOption('display-command', 'd', InputOption::VALUE_NONE, "Show the command executed on terminal."),
        new InputOption('demo_content', '', InputOption::VALUE_OPTIONAL, "Provide option to add demo content."),
        new InputOption('content_model', '', InputOption::VALUE_OPTIONAL, "Provide option to include ACMS recommended content types."),
        new InputOption('dam_integration', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('gdpr_integration', '', InputOption::VALUE_OPTIONAL, "Provide option to add GDPR in application."),
        new InputOption('GMAPS_KEY', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('SITESTUDIO_API_KEY', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('SITESTUDIO_ORG_KEY', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('nextjs_app', '', InputOption::VALUE_OPTIONAL, "Provide option to add demo content."),
        new InputOption('nextjs_app_site_url', '', InputOption::VALUE_OPTIONAL, "Provide option to include ACMS recommended content types."),
        new InputOption('nextjs_app_site_name', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('nextjs_app_env_file', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('pwa_integration', '', InputOption::VALUE_OPTIONAL, "Provide option to add pwa in application."),
        new InputOption('pwa_site_name', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
        new InputOption('pwa_short_name', '', InputOption::VALUE_OPTIONAL, "Provide option to add DAM in application."),
      ])
      ->setHelp("The <info>acms:install</info> command downloads & setup Drupal site based on user selected use case.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    $build_command = $install_command = [];
    foreach ($input->getOptions() as $key => $value) {
      if ($value) {
        $build_command[] = $install_command[] = '--' . $key . '=' . $value;
      }
    }
    if ($input->getOption('no-interaction')) {
      $install_command[] = '--no-interaction';
    }
    $site_uri = $input->getOption('uri');
    if ($this->filesystem->exists('./vendor/bin/acms')) {
      $this->genericCommand->setCommand('./vendor/bin/acms');
    }
    else {
      $this->genericCommand->setCommand('./bin/acms');
    }
    $install_command = array_merge($install_command, ['--uri=' . $site_uri]);
    $build_command = array_merge($build_command, $install_command);
    $build_command = array_merge(['acms:build'], $build_command);
    $install_command = array_merge([
      'site:install',
      '--without-product-info',
    ], $install_command);

    // Execute acms acms:build.
    $this->genericCommand->prepare($build_command)->run();

    // Execute acms site:install.
    $this->genericCommand->prepare($install_command)->run();
    return StatusCodes::OK;
  }

}
