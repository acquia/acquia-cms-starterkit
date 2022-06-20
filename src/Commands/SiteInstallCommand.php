<?php

namespace AcquiaCMS\Cli\Commands;

use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Enum\StatusCodes;
use AcquiaCMS\Cli\Exception\AcmsCliException;
use AcquiaCMS\Cli\Helpers\InstallerQuestions;
use AcquiaCMS\Cli\Helpers\Task\InstallTask;
use AcquiaCMS\Cli\Helpers\Task\SiteInstallTask;
use AcquiaCMS\Cli\Helpers\Traits\StatusMessageTrait;
use AcquiaCMS\Cli\Helpers\Traits\UserInputTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Provides the Acquia CMS site:install command.
 *
 * @code ./vendor/bin/acms site:install
 */
class SiteInstallCommand extends Command {

  use StatusMessageTrait;
  use UserInputTrait;

  /**
   * The AcquiaCMS Cli object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCmsCli;

  protected $siteInstallTask;

  /**
   * Constructs an instance.
   *
   * @param \AcquiaCMS\Cli\Helpers\Task\SiteInstallTask $siteInstallTask
   *   Provides the Acquia CMS Install task object.
   * @param \AcquiaCMS\Cli\Cli $cli
   *   Provides the AcquiaCMS Cli class object.
   */
  public function __construct(Cli $cli, SiteInstallTask $siteInstallTask) {
    $this->acquiaCmsCli = $cli;
    $this->siteInstallTask = $siteInstallTask;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() :void {
    $this->setName("site:install")
      ->setDescription("A wrapper command for drush site:install command.")
      ->setDefinition([
        new InputArgument('profile',  InputArgument::IS_ARRAY,
          "An install profile name. Defaults to <info>minimal</info> unless an install profile is marked as a distribution. ". PHP_EOL .
        "Additional info for the install profile may also be provided with additional arguments. The key is in the form [form name].[parameter name]"),
        new InputOption('db-url','',InputOption::VALUE_OPTIONAL,"A Drupal 6 style database URL. Required for initial install, not re-install. If omitted and required, Drush prompts for this item."),
        new InputOption('db-prefix','', InputOption::VALUE_OPTIONAL, "An optional table prefix to use for initial install."),
        new InputOption('db-su','', InputOption::VALUE_OPTIONAL, "Account to use when creating a new database. Must have Grant permission (mysql only). Optional."),
        new InputOption('db-su-pw','', InputOption::VALUE_OPTIONAL, "Password for the <info>db-su</info> account. Optional."),
        new InputOption('account-name','', InputOption::VALUE_OPTIONAL, "uid1 name."),
        new InputOption('account-mail','', InputOption::VALUE_OPTIONAL, "uid1 email."),
        new InputOption('site-mail','', InputOption::VALUE_OPTIONAL, "<info>From</info>: for system mailings."),
        new InputOption('account-pass','', InputOption::VALUE_OPTIONAL, "uid1 pass. Defaults to a randomly generated password."),
        new InputOption('locale','', InputOption::VALUE_OPTIONAL, "A short language code. Sets the default site language. Language files must already be present.", 'en'),
        new InputOption('site-name','', InputOption::VALUE_OPTIONAL, "Name of the Drupal site."),
        new InputOption('site-pass','', InputOption::VALUE_OPTIONAL),
        new InputOption('sites-subdir','', InputOption::VALUE_OPTIONAL, "Name of directory under <info>sites</info> which should be created."),
        new InputOption('existing-config ','', InputOption::VALUE_NONE, "Configuration from <info>sync</info> directory should be imported during installation."),
        new InputOption('uri','l', InputOption::VALUE_OPTIONAL, "Multisite uri to setup drupal site."),
        new InputOption('yes','y', InputOption::VALUE_OPTIONAL, "Equivalent to --no-interaction."),
        new InputOption('no','', InputOption::VALUE_OPTIONAL, "Cancels at any confirmation prompt."),
        new InputOption('hide-command','hide', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
        new InputOption('display-command','d', InputOption::VALUE_NONE, "Doesn't show the command executed on terminal."),
      ])
      ->setHelp("The <info>site:install</info> command install Drupal along with modules/themes/configuration/profile.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) :int {
    try {
      $this->siteInstallTask->configure($input, $output);
      $this->siteInstallTask->run();
    }
    catch (AcmsCliException $e) {
      $output->writeln("<error>" . $e->getMessage() . "</error>");
      return StatusCodes::ERROR;
    }
    return StatusCodes::OK;
  }

}
