<?php

namespace tests;

use AcquiaCMS\Cli\Cli;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;

class CliTest extends TestCase {
  use ProphecyTrait;

  /**
   * Holds the symfony console output object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $output;

  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $projectDirectory;


  /**
   * An absolute directory to project.
   *
   * @var string
   */
  protected $rootDirectory;

  /**
   * An acquia minimal client object.
   *
   * @var \AcquiaCMS\Cli\Cli
   */
  protected $acquiaCli;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->output = $this->prophesize(OutputInterface::class);
    $output = $this->output->reveal();
    $this->projectDirectory = getcwd();
    $this->rootDirectory = $this->projectDirectory;
    $this->acquiaCli = new Cli($this->projectDirectory, $this->rootDirectory, $output);
  }

  /**
   * Test the welcome message that display when running command acms:install.
   *
   * @test
   */
  public function testExecute() :void {
    $this->assertEquals("Welcome to Acquia CMS Starter Kit installer", $this->acquiaCli->headline);
    $this->assertEquals($this->projectDirectory . "/assets/acquia_cms.icon.ascii", $this->acquiaCli->getLogo());
    $this->assertEquals($this->getAcmsFileContents(), $this->acquiaCli->getAcquiaCmsFile());
  }

  /**
   * An array of default contents for acms/acms.yml file.
   */
  protected function getAcmsFileContents() :array {
    return [
      "starter_kits" => [
        "acquia_cms_demo" => [
          'name' => 'Acquia CMS Demo',
          'description' => 'Low-code demonstration of ACMS with default content.',
          'modules' => [
            "install" => ["acquia_cms_starter", "acquia_cms_tour"],
          ],
          'themes' => [
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
            "default" => "cohesion_theme",
          ],
        ],
        "acquia_cms_low_code" => [
          "name" => "Acquia CMS Low Code",
          "description" => "Acquia CMS with Site Studio but no content opinion.",
          "modules" => [
            "install" => ["acquia_cms_page", "acquia_cms_search", "acquia_cms_tour"],
          ],
          "themes" => [
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
            "default" => "cohesion_theme",
          ],
        ],
        "acquia_cms_standard" => [
          "name" => "Acquia CMS Standard",
          "description" => "Acquia CMS with a starter content model, but no demo content, classic custom themes.",
          "modules" => [
            "install" => ["acquia_cms_article", "acquia_cms_event", "acquia_cms_search", "acquia_cms_tour", "acquia_cms_video"],
          ],
          "themes" => [
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
          ],
        ],
        "acquia_cms_minimal" => [
          "name" => "Acquia CMS Minimal",
          "description" => "Acquia CMS in a blank slate, ideal for custom PS.",
          "modules" => [
            "install" => ["acquia_cms_search", "acquia_cms_tour"],
          ],
          "themes" => [
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
          ],
        ],
        "acquia_cms_headless" => [
          "name" => "Acquia CMS Headless",
          "description" => "ACMS with headless functionality.",
          "modules" => [
            "install" => ["acquia_cms_headless", "acquia_cms_tour"],
          ],
          "themes" => [
            "install" => ["acquia_claro"],
            "admin" => "acquia_claro",
          ],
        ],
      ],
    ];
  }

}
