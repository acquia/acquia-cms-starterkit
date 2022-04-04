<?php

namespace AcquiaCMS\Cli;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class Cli {

  public string $headline = "Welcome to Acquia CMS starterkit installer";

  public function __construct(string $project_dir, SymfonyStyle $output) {
    $this->projectDirectory = $project_dir;
    $this->output = $output;
  }

  public function printLogo() {
    $this->output->writeln("<info>" . file_get_contents($this->getLogo()) . "</info>");
  }
  public function getLogo() {
    return $this->projectDirectory . "/assets/acquia_cms.icon.ascii";
  }
  public function printHeadline() {
    $this->output->writeln("<fg=cyan;options=bold,underscore> " . $this->headline . "</>");
    $this->output->newLine();
  }

  public function getAcquiaCmsFile() {
    return Yaml::parseFile($this->projectDirectory . '/acms/acms.yml');
  }
  public function getStarterKits() {
    $fileContent = $this->getAcquiaCmsFile();
    return $fileContent['starter_kits'];
  }
}
