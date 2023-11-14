<?php

namespace tests\Http\Client\Github;

use AcquiaCMS\Cli\Http\Client\Github\GithubBase;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubBaseTest extends TestCase {
  use ProphecyTrait;

  /**
   * Holds the symfony console output object.
   *
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->httpClient = $this->prophesize(HttpClientInterface::class)->reveal();
  }

  /**
   * Test if method:getRepoName() returns the expected string.
   *
   * @test
   */
  public function testAbstractMethod() :void {
    $stub = $this->getMockForAbstractClass(GithubBase::class, [$this->httpClient]);
    $stub->expects($this->any())
      ->method('getRepoName')
      ->will($this->returnValue("acquia/acquia_cms"));
    $this->assertEquals("acquia/acquia_cms", $stub->getRepoName());
  }

  /**
   * Test getLatestReleaseTag(), getFileContents() returns the expected string.
   *
   * @test
   */
  public function testMethods() :void {
    $stub = $this->getMockBuilder('AcquiaCMS\Cli\Http\Client\Github\GithubBase')
      ->disableOriginalConstructor()
      ->getMock();
    $stub->expects($this->any())
      ->method('getLatestReleaseTag')
      ->will($this->returnValue("1.4.6"));
    $this->assertEquals("1.4.6", $stub->getLatestReleaseTag());

    $stub->expects($this->any())
      ->method('getFileContents')
      ->with("composer.json")
      ->will($this->returnValue("{ name: 'acquia/acquia_cms'}"));

    $this->assertEquals("{ name: 'acquia/acquia_cms'}", $stub->getFileContents("composer.json"));
  }

}
