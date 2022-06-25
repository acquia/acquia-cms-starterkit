<?php

/**
 * @file
 * Acquia CMS cli main file.
 */

namespace AcquiaCMS\Cli;

use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Filesystem\Filesystem;

if (!file_exists(__DIR__ . '/../vendor/autoload.php') && !file_exists(__DIR__ . '/../../../autoload.php')) {
  echo "\033[31mCould not find autoloader. Run `composer install` command first.\033[0m" . PHP_EOL;
  exit(1);
}
(@include_once __DIR__ . '/../vendor/autoload.php') || @include_once __DIR__ . '/../../../autoload.php';

set_time_limit(0);
ini_set('memory_limit', -1);

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], "prod", TRUE);
$kernel = new Kernel($env, FALSE);

// Handle the cache:clear command. This isn't implemented as a console
// command because any corrupted cache won't allow us to run the command
// when it's actually needed.
if (in_array($input->getFirstArgument(), ['cache:clear', 'cc'])) {
  $filesystem = new Filesystem();
  // Delete the cached directory.
  $cache_dir = $kernel->getCacheDir();
  $filesystem->remove($cache_dir);
  $filesystem->mkdir($cache_dir);
  $filesystem->touch("{$cache_dir}/.gitkeep");
  echo "\033[1;32m[ok]\033[0m All caches have been cleared." . PHP_EOL;
  exit(0);
}

// Clear cache everytime before running site:install command
// Because user might not run this command everytime
// specially on Acquia Cloud environment.
if (in_array($input->getFirstArgument(), ['site:install', 'si', 'site-install'])) {
  $filesystem = new Filesystem();
  // Delete the cached directory.
  $cache_dir = $kernel->getCacheDir();
  $filesystem->remove($cache_dir);
  $filesystem->mkdir($cache_dir);
  $filesystem->touch("{$cache_dir}/.gitkeep");
}

$kernel->boot();
$container = $kernel->getContainer();
$application = $container->get(Application::class);
$application->setName('Acquia CMS starterkit cli tool.');
$application->setVersion('1.0');

// Add command autocompletion.
$application->add(new CompletionCommand());
$application->run();
