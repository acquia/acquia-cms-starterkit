<?php

namespace tests\Helpers\Parsers;

use AcquiaCMS\Cli\Helpers\Parsers\JsonParser;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test the JsonParser class.
 */
class JsonParserTest extends TestCase {
  use ProphecyTrait;

  /**
   * Tests the method: downloadPackages() for JsonParser class.
   *
   * @param array $actual
   *   An actual array before parsing.
   * @param array $expected
   *   An expected array after parsing.
   *
   * @dataProvider downloadPackagesDataProvider
   */
  public function testDownloadPackages(array $actual, array $expected) :void {
    $this->assertEquals($expected, JsonParser::downloadPackages($actual));
  }

  /**
   * Tests the method: installPackages() for JsonParser class.
   *
   * @param array $actual
   *   An actual array before parsing.
   * @param array $expected
   *   An expected array after parsing.
   *
   * @dataProvider installPackagesDataProvider
   */
  public function testInstallPackages(array $actual, array $expected) :void {
    $this->assertEquals($expected, JsonParser::installPackages($actual));
  }

  /**
   * Provides the data to test: testDownloadPackages().
   */
  public function downloadPackagesDataProvider(): array {
    return [
      [
        ["acquia_cms_common", "acquia/cohesion", "drupal/acquia_cms_article", "acquia/cohesion_theme"],
        ["drupal/acquia_cms_common", "acquia/cohesion", "drupal/acquia_cms_article", "acquia/cohesion_theme"],
      ],
      [
        ["acquia_cms_common:^1.3", "acquia/cohesion:^6.8"],
        ["drupal/acquia_cms_common:^1.3", "acquia/cohesion:^6.8"],
      ],
    ];
  }

  /**
   * Provides the data to test: testInstallPackages().
   */
  public function installPackagesDataProvider(): array {
    return [
      [
        ["acquia_cms_common", "acquia/cohesion", "drupal/acquia_cms_article", "acquia/cohesion_theme"],
        ["acquia_cms_common", "cohesion", "acquia_cms_article", "cohesion_theme"],
      ],
      [
        ["acquia_cms_common:^1.3", "acquia/cohesion:^6.8", "acquia/acquia-cms-starterkit:^1.5"],
        ["acquia_cms_common", "cohesion", "acquia-cms-starterkit"],
      ],
    ];
  }

}
