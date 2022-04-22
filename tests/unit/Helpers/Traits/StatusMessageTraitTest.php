<?php

namespace AcquiaCMS\Cli\Helpers\Traits;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Provides the class to test StatusMessageTrait.
 */
class StatusMessageTraitTest extends TestCase {

  use StatusMessageTrait;
  use ProphecyTrait;

  /**
   * Tests the method style() of trait: StatusMessageTrait.
   *
   * @param array $actual
   *   An actual array before styling.
   * @param array $expected
   *   An expected array after styling.
   *
   * @dataProvider styleDataProvider
   */
  public function testStyle(array $actual, array $expected) :void {
    $this->assertEquals($this->style($actual[0], $actual[1]), $expected);
  }

  /**
   * Provides the dataProvider to function testStyle().
   *
   * @return array[]
   *   Returns an array of actual/expected data.
   */
  public function styleDataProvider() {
    return [
      [
        ['This is headline message.', 'headline'],
        [
          '',
          "<fg=green;>This is headline message.</>",
          str_repeat("-", strlen("This is headline message.")),
        ],
      ],
      [
        ['This is warning message.', 'warning'],
        [
          '',
          "<comment>This is warning message.</comment>",
        ],
      ],
      [
        ['This is success message.', 'success'],
        [
          '',
          "<info>This is success message.</info>",
        ],
      ],
      [
        ['This is default message.', ''],
        ["This is default message."],
      ],
    ];
  }

}
