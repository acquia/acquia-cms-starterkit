<?php

namespace tests\Helpers;

use AcquiaCMS\Cli\Helpers\Utility;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UtilityTest extends TestCase {
  use ProphecyTrait;

  /**
   * Tests the method: normalizePath() of class Utility.
   *
   * @param string $expected
   *   An expected normalized directory path.
   * @param string $actual
   *   An actual directory path to normalize.
   *
   * @dataProvider dataProviderDirectory
   */
  public function testNormalizeDirectoryPath(string $expected, string $actual): void {
    $this->assertEquals($expected, Utility::normalizePath($actual));
  }

  /**
   * Tests the method: replaceValueByKey() of class Utility.
   */
  public function testReplaceValueByKey(): void {
    $actual = [
      "name" => "Acquia CMS Headless",
      "description" => "The headless starter kit preconfigures Drupal for serving structured, RESTful",
      "modules" => [
        "require" => [
          "acquia_cms_headless",
          "acquia_cms_search",
          "acquia_cms_tour",
          "acquia_cms_toolbar",
          "consumer_image_styles",
        ],
        "install" => [
          "acquia_cms_headless_ui",
          "acquia_cms_search",
          "acquia_cms_tour",
          "acquia_cms_toolbar",
          "consumer_image_styles",
        ],
      ],
      "themes" => [
        "require" => ["gin"],
        "install" => ["gin"],
        "admin" => "gin",
        "default" => "olivero",
      ],
    ];
    $expected = unserialize(serialize($actual), ['allowed_classes' => FALSE]);
    $expected['themes']['require'] = ["acquia_claro"];
    $this->assertEquals($expected, Utility::replaceValueByKey($actual, "themes.require", "gin", "acquia_claro"));

    $expected = unserialize(serialize($actual), ['allowed_classes' => FALSE]);
    $expected['themes']['install'] = ["acquia_claro"];
    $this->assertEquals($expected, Utility::replaceValueByKey($actual, "themes.install", "gin", "acquia_claro"));

    $expected = unserialize(serialize($actual), ['allowed_classes' => FALSE]);
    $expected['themes']['admin'] = "acquia_claro";
    $this->assertEquals($expected, Utility::replaceValueByKey($actual, "themes.admin", "gin", "acquia_claro"));
  }

  /**
   * Tests the method: removeValueByKey() of class Utility.
   */
  public function testRemoveValueByKey(): void {
    $actual = [
      "name" => "Acquia CMS Headless",
      "description" => "The headless starter kit preconfigures Drupal for serving structured, RESTful",
      "modules" => [
        "require" => [
          "acquia_cms_headless",
          "acquia_cms_search",
          "acquia_cms_tour",
          "acquia_cms_toolbar",
          "consumer_image_styles",
        ],
        "install" => [
          "acquia_cms_headless_ui",
          "acquia_cms_search",
          "acquia_cms_tour",
          "acquia_cms_toolbar",
          "consumer_image_styles",
        ],
      ],
      "themes" => [
        "require" => ["gin"],
        "install" => ["gin"],
        "admin" => "gin",
        "default" => "olivero",
      ],
    ];
    $expected = unserialize(serialize($actual), ['allowed_classes' => FALSE]);
    unset($expected["modules"]["require"][2]);
    $expected["modules"]["require"] = array_values($expected["modules"]["require"]);
    $this->assertEquals($expected, Utility::removeValueByKey($actual, "modules.require", "acquia_cms_tour"));
  }

  /**
   * Provides an array of actual & expected directory path.
   *
   * @return array
   *   Returns an array of directories path.
   */
  public static function dataProviderDirectory(): array {
    return [
      [
        "/acquia/acquia-cms-project",
        "/acquia/acquia-cms-project/src/tests/../../",
      ],
      [
        "/acquia/acquia-cms-project/vendor/bin",
        "/acquia/acquia-cms-project/src/tests/unit/Helpers/../../../../../acquia-cms-project/vendor/bin",
      ],
      [
        "/acquia/acquia-cms-project/vendor/bin",
        "/acquia/acquia-cms-project/src/tests/unit/Helpers/../../../../../acquia-cms-project/vendor/bin/../bin",
      ],
      [
        "/acquia/acquia-cms-project/bin/acms.php",
        "/acquia/acquia-cms-project/src/tests/../../var/logs/../../bin/acms.php",
      ],
    ];
  }

  /**
   * Tests the method generateRandomPassword().
   */
  public function testGeneratedPassword(): void {
    $password = Utility::generateRandomPassword(12);
    $this->assertMatchesRegularExpression("/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}/", $password);
  }

}
