<?php

namespace AcquiaCMS\Cli\Helpers\Task;


use AcquiaCMS\Cli\Cli;
use AcquiaCMS\Cli\Helpers\Task\Steps\DownloadDrupal;
use AcquiaCMS\Cli\Helpers\Task\Steps\EnableModules;
use AcquiaCMS\Cli\Helpers\Task\Steps\SiteInstall;
use AcquiaCMS\Cli\Helpers\Task\Steps\StatusMessage;
use AcquiaCMS\Cli\Helpers\Task\Steps\ValidateDrupal;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InstallTask {
  public function __construct(Cli $cli, ValidateDrupal $validateDrupal, DownloadDrupal $downloadDrupal, SiteInstall $siteInstall, StatusMessage $statusMessage, EnableModules $enableModules) {
    $this->acquiaCmsCli = $cli;
    $this->starterKits = $this->acquiaCmsCli->getStarterKits();
    $this->validateDrupal = $validateDrupal;
    $this->downloadDrupal = $downloadDrupal;
    $this->statusMessage = $statusMessage;
    $this->enableModules = $enableModules;
    $this->siteInstall = $siteInstall;
  }

  public function configure(InputInterface $input, OutputInterface $output, Command $command) {
    $this->command = $command;
    $this->input = $input;
    $this->output = $output;
  }

  public function run() {
    $this->acquiaCmsCli->printLogo();
    $this->acquiaCmsCli->printHeadline();
    $this->renderStarterKits();
    $bundle = $this->askBundleQuestion();
    if (!$this->validateDrupal->execute()) {
      $this->statusMessage->print("Looks like, current project is not a Drupal project.", StatusMessage::TYPE_WARNING);
      $this->statusMessage->print("Converting the current project to Drupal project.", StatusMessage::TYPE_HEADLINE);
      $this->downloadDrupal->execute();
    } else {
      $this->statusMessage->print("Seems Drupal is already downloaded. Skipping downloading Drupal.", StatusMessage::TYPE_SUCCESS);
    }
    $this->statusMessage->print("Installing Site", StatusMessage::TYPE_HEADLINE);
    $this->siteInstall->execute();
    $this->statusMessage->print("Enabling modules for the bundle: `$bundle`.", StatusMessage::TYPE_HEADLINE);
    $this->enableModules->execute($this->starterKits[$bundle]);
  }

  protected function renderStarterKits() {
    $table = new Table($this->output);
    $table->setHeaders(['ID', 'Name', 'Description']);
    foreach($this->starterKits as $id => $starter_kit) {
      $useCases[$id] = $starter_kit;
      $table->addRow([$id, $starter_kit['name'], $starter_kit['description']]);
    }
    $table->setStyle('box');
    $table->render();
  }

  protected function askBundleQuestion() {
    $helper = $this->command->getHelper('question');
    $bundles = array_keys($this->starterKits);
    $question = new Question("Please choose bundle from one of the above use case: <comment>[$bundles[0]]</comment>: ", $bundles[0]);
    $question->setAutocompleterValues($bundles);
    $question->setValidator(function ($answer) use ($bundles) {
      if (!is_string($answer) || !in_array($answer, $bundles)) {
        throw new \RuntimeException(
          "Please choose from one of the use case defined above. Ex: acquia_cms_demo."
        );
      }
      return $answer;
    });
    $question->setMaxAttempts(3);
    return $helper->ask($this->input, $this->output, $question);
  }

}
