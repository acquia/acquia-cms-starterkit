<?php

namespace AcquiaCMS\Cli\Helpers\Process;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessFacade {
  protected $process;
  protected $output;
  public function __construct(SymfonyStyle $output) {
    $this->output = $output;
  }

  protected function beforeRunning() {

  }

  public function isDrupalProject() {
    $process = new Process(["composer", "config", "extra.drupal-scaffold"]);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL);
    $process->run();
    if (!$process->isSuccessful()) {
      return FALSE;
    }
    return TRUE;
  }

  public function add(array $command) {
    $process = new Process($command);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL)
      ->setTty(true);
    $this->process[] = $process;
  }

//  public function add(array $command, string $description = NULL) {
//    $process = new Process($command);
//    $process->setTimeout(NULL)
//      ->setIdleTimeout(NULL)
//      ->setTty(true);
//    $this->process[] = [
//      "process" => $process,
//      "description" => $description
//    ];
//  }
  public function run() {
    foreach ($this->process as $process) {
      array_shift($this->process);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->start();
      $process->wait(function ($type, $buffer) {
        print $buffer;
      });
      if (!$process->isSuccessful()) {
        break;
      }
    }
    die;
    #if (!$this->isDrupalProject()) {
      $this->output->newLine();
      $this->output->writeln(
          "<comment>[debug]</comment> Looks like it's not a Drupal project."
      );
      $this->output->newLine();
      $message = "Converting current project to Drupal project.";
      $this->output->writeln("<fg=green;>" . $message . "</>");
      $this->output->writeln(str_repeat("-", strlen($message)));
      $process = new Process(["composer", "config", "--no-plugins", "allow-plugins.composer/installers", 'true']);
      $process->setTimeout(NULL)
        ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--no-plugins", "allow-plugins.cweagans/composer-patches", 'true']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--no-plugins", "allow-plugins.drupal/core-composer-scaffold", 'true']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--json", "extra.drupal-scaffold", '{"file-mapping": { "[web-root]/sites/default/default.services.yml": { "mode": "replace", "overwrite": false, "path": "docroot/core/assets/scaffold/files/default.services.yml" }, "[web-root]/sites/default/default.settings.php": { "mode": "replace", "overwrite": false, "path": "docroot/core/assets/scaffold/files/default.settings.php" } }, "gitignore": true, "locations": { "web-root": "docroot/" }}']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "extra.enable-patching", "true"]);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--json", "extra.installer-paths", '{ "docroot/core": [ "type:drupal-core" ], "docroot/libraries/{$name}": [ "type:drupal-library", "type:bower-asset", "type:npm-asset" ], "docroot/modules/contrib/{$name}": [ "type:drupal-module" ], "docroot/modules/custom/{$name}": [ "type:drupal-custom-module" ], "docroot/profiles/contrib/{$name}": [ "type:drupal-profile" ], "docroot/themes/contrib/{$name}": [ "type:drupal-theme" ], "docroot/themes/custom/{$name}": [ "type:drupal-custom-theme" ], "drush/Commands/contrib/{$name}": [ "type:drupal-drush" ] }']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--json", "extra.installer-types", '["bower-asset","npm-asset"]']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--json", "extra.patchLevel", '{ "drupal/core": "-p2" }']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--json", "repositories.assets", '{ "type": "composer", "url": "https://asset-packagist.org" }']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "config", "--json", "repositories.drupal", '{ "type": "composer", "url": "https://packages.drupal.org/8" }']);
      $process->setTimeout(NULL)
          ->setIdleTimeout(NULL);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->run();

      $process = new Process(["composer", "require", "composer/installers:^1.9", "cweagans/composer-patches:^1.6", "drupal/core-composer-scaffold:^9", "drupal/core-recommended: ^9", "drush/drush: ^10.3 || ^11", "-W"]);
      $process->setTimeout(NULL)
        ->setIdleTimeout(NULL)
        ->setTty(true);
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->start();
      $process->wait(function ($type, $buffer) {
          print $buffer;
      });

    #}
    //die;
    foreach ($this->process as $processArray) {
      $process = $processArray['process'];
      if ($processArray['description']) {
        $this->output->newLine();
        $this->output->writeln("<fg=green;>" . $processArray['description'] . "</>");
        $this->output->writeln(str_repeat("-", strlen($processArray['description'])));
      }
      $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
      $process->start();
      $process->wait(function ($type, $buffer) {
        print $buffer;
      });
      if (!$process->isSuccessful()) {
        break;
      }
    }

    $process = new Process(["./vendor/bin/drush", "site:install"]);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL)
      ->setTty(true);
    $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
    $process->start();
    $process->wait(function ($type, $buffer) {
      print $buffer;
    });

    $process = new Process(["./vendor/bin/drush", "en", "acquia_cms_article", "acquia_cms_event","-y"]);
    $process->setTimeout(NULL)
      ->setIdleTimeout(NULL)
      ->setTty(true);
    $this->output->writeln(sprintf('> %s', $process->getCommandLine()));
    $process->start();
    $process->wait(function ($type, $buffer) {
      print $buffer;
    });
  }

}
