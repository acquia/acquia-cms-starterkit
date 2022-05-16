<?php

namespace tests\Helpers\Parsers;

use AcquiaCMS\Cli\Helpers\Parsers\PHPParser;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Test the PHPParser class.
 */
class PHPParserTest extends TestCase {
  use ProphecyTrait;

  /**
   * Tests the method: parseEnvVars() for PHPParser class.
   *
   * @param string $actual
   *   An actual string before parsing.
   * @param string $expected
   *   An expected string after parsing.
   * @param array $envVariables
   *   An array of env. variables with values.
   *
   * @dataProvider parseEnvVariablesDataProvider
   */
  public function testPhpEnvVars(string $actual, string $expected, array $envVariables = []) :void {
    if ($envVariables) {
      foreach ($envVariables as $key => $value) {
        putenv("$key=$value");
      }
    }
    $this->assertEquals($expected, PHPParser::parseEnvVars($actual));
  }

  /**
   * @dataProvider parseValidQuestionExpression
   */
  public function testParseValidQuestion(string $actual, array $expected, string $exception = NULL) :void {
    if ($exception) {
      $this->expectException($exception);
    }
    $this->assertEquals($expected, PHPParser::parseQuestionExpression($actual));
  }

  /**
   * Provides the data to test: testParseQuestionExpression().
   */
  public function parseValidQuestionExpression(): array {
    return [
      [
        '${site_studio} == "yes"',
        [
          '${site_studio} == "yes"',
          'site_studio',
          ' ',
          '==',
          ' ',
          '"yes"',
        ],
      ],
      [
        '${some_integer_value} == 10',
        [
          '${some_integer_value} == 10',
          'some_integer_value',
          ' ',
          '==',
          ' ',
          '10',
          '10',
        ],
      ],
      [
        '${some_value} == no',
        [],
        'RuntimeException',
      ],
      [
        '${some_value} === yes',
        [],
        'RuntimeException',
      ],
    ];
  }

  /**
   * Provides the data to test: testInstallPackages().
   */
  public function parseEnvVariablesDataProvider(): array {
    return [
      [
        'Static Value',
        'Static Value',
      ],
      [
        '${CONNECTOR_ID}',
        '12345',
        [
          'CONNECTOR_ID' => 12345,
        ],
      ],
      [
        '$AH_GROUP.${AH_ENV}',
        'orionacms.prod',
        [
          'AH_GROUP' => 'orionacms',
          'AH_ENV' => 'prod',
        ],
      ],
      [
        '${SOME_VALUE}-XYZ',
        '-XYZ',
        [],
      ],
    ];
  }

}
