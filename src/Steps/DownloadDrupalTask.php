<?php

namespace AcquiaCMS\Cli\Steps;

use AcquiaCMS\Cli\Enum\StatusCode;
use AcquiaCMS\Cli\Helpers\Process\Commands\Composer;
use AcquiaCMS\Cli\Http\Client\Github\AcquiaRecommendedClient;
use AcquiaCMS\Cli\Tasks\TaskInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to download drupal project. If already exists, then skip downloading.
 *
 * @Task(
 *   id = "download_drupal_task",
 *   weight = 2,
 * )
 */
class DownloadDrupalTask extends BaseTask {

  /**
   * Holds the composer command object.
   *
   * @var \AcquiaCMS\Cli\Helpers\Process\Commands\Composer
   */
  protected $composerCommand;

  /**
   * Holds the http client object.
   *
   * @var \AcquiaCMS\Cli\Http\Client\Github\AcquiaRecommendedClient
   */
  protected $client;

  /**
   * Creates the task object.
   *
   * @param \AcquiaCMS\Cli\Helpers\Process\Commands\Composer $composer_command
   *   A composer command object.
   * @param \AcquiaCMS\Cli\Http\Client\Github\AcquiaRecommendedClient $client
   *   A http client object.
   */
  public function __construct(Composer $composer_command, AcquiaRecommendedClient $client) {
    $this->composerCommand = $composer_command;
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(Command $command, ContainerInterface $container): TaskInterface {
    return new static(
      $container->get('composer_command'),
      $container->get('drupal_project')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(InputInterface $input, OutputInterface $output): int {
    $command_output = $this->composerCommand->prepare([
      'show',
      'drupal/core',
      '--format=json',
    ])->runQuietly([], FALSE);
    $version = '';
    $json_output = json_decode($command_output);
    if (json_last_error() === JSON_ERROR_NONE) {
      $version = implode(', ', $json_output->versions);
    }
    if ($version) {
      $output->writeln($this->style("Seems Drupal is already downloaded. " .
        "The downloaded Drupal core version is: $version. " .
        "Skipping downloading Drupal.", 'success'
      ));
      return StatusCode::SKIP;
    }
    return StatusCode::OK;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln($this->style("Looks like, current project is not a Drupal project:", 'warning'));
    $output->writeln($this->style("Converting the current project to Drupal project:", 'headline'));
    $jsonObject = json_decode($this->client->getFileContents("composer.json"));
    $repositories = &$jsonObject->require;
    if (isset($repositories->{'acquia/acquia-cms-starterkit'})) {
      unset($repositories->{'acquia/acquia-cms-starterkit'});
    }
    foreach ($jsonObject->repositories as $repoName => $data) {
      $this->composerCommand->prepare([
        "config",
        "--json",
        "repositories.$repoName",
        json_encode($data),
      ])->run();
    }
    foreach ($jsonObject->extra as $key => $data) {
      $this->composerCommand->prepare([
        "config",
        "--json",
        "extra.$key",
        json_encode($data),
      ])->run();
    }
    foreach ($jsonObject->config->{'allow-plugins'} as $plugin => $value) {
      $this->composerCommand->prepare([
        "config",
        "--no-plugins",
        "allow-plugins.$plugin",
        $value,
      ])->run();
    }
    $requireCommands = $this->getRequireCommands($jsonObject);
    foreach ($requireCommands as $requireCommand) {
      $this->composerCommand->prepare($requireCommand)->run();
    }
    return StatusCode::OK;
  }

  /**
   * Returns the require command.
   *
   * @param mixed $jsonObject
   *   The json object.
   */
  protected function getRequireCommands($jsonObject) : array {
    $requireCommands = [];
    foreach ([$jsonObject->{'require'}, $jsonObject->{'require-dev'}] as $index => $packages) {
      $packages = (array) $packages;
      $packages = array_map(function ($key, $value) {
        return $key . ":" . $value;
      }, array_keys($packages), $packages);
      $requireCommand = array_merge(["require", "-W"], $packages);
      if ($index) {
        $requireCommand[] = "--dev";
      };
      $requireCommands[] = $requireCommand;
    }
    return $requireCommands;
  }

}
