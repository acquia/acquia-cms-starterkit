<?php

namespace AcquiaCMS\Cli\Helpers\Traits;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Provides the class to test StatusMessageTrait.
 */
class UserInputTraitTest extends TestCase {

  use UserInputTrait;
  use ProphecyTrait;

  /**
   * Tests the method style() of trait: StatusMessageTrait.
   *
   * @param array $actual
   *   An actual array before styling.
   * @param string $expected
   *   An expected string after styling.
   *
   * @dataProvider styleQuestionDataProvider
   */
  public function testStyle(array $actual, string $expected): void {
    $this->assertEquals($this->styleQuestion(...$actual), $expected);
  }

  /**
   * Provides the dataProvider to function testStyle().
   *
   * @return array[]
   *   Returns an array of actual/expected data.
   */
  public static function styleQuestionDataProvider(): array {
    return [
      [
        ['Please enter the Site Studio API Key', 'inter-abcd-47d4kf7'],
        " <info>Please enter the Site Studio API Key</info> <comment>[inter-abcd-47d4kf7]</comment>:" . PHP_EOL . " > ",
      ],
      [
        ['Please enter the Site Studio Organization Key'],
        " <info>Please enter the Site Studio Organization Key</info>:" . PHP_EOL . " > ",
      ],
      [
        ['Please enter the Connector ID', '', TRUE],
        " <info>Please enter the Connector ID</info><fg=red;options=bold> *</>:" . PHP_EOL . " > ",
      ],
      [
        ['Please enter some key with new line', '', TRUE, TRUE],
        PHP_EOL . " <info>Please enter some key with new line</info><fg=red;options=bold> *</>:" . PHP_EOL . " > ",
      ],
    ];
  }

}
