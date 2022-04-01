<?php

namespace AcquiaCMS\Cli\Http\Client;

use Acquia\Orca\Exception\OrcaHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Interacts with the Acquia Minimal Github API client.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis
 */
abstract class HttpClientBase {

  protected $method;

  protected $options;

  protected $baseUrl;

  /**
   * The HTTP client.
   *
   * @var \Symfony\Contracts\HttpClient\HttpClientInterface
   */
  private $httpClient;

  /**
   * Constructs an instance.
   *
   * @param \Symfony\Contracts\HttpClient\HttpClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(HttpClientInterface $http_client) {
    $this->method = "GET";
    $this->options = [];
    $this->httpClient = $http_client;
  }

  /**
   * Gets the oldest supported branch of Drupal core.
   *
   * @return string
   *   The branch name.
   *
   * @throws \Acquia\Orca\Exception\OrcaHttpException
   *
   * @noinspection PhpDocMissingThrowsInspection
   */
  public function request($url) {
    $response = $this->httpClient
      ->request($this->method, $this->baseUrl  . $url, $this->getOptions());

    $response = $response->getContent();
    return $response;
  }

  protected function setMethod($method) {
    $this->method = $method;
  }

  protected function setOptions(array $options) {
    $this->options = $options;
  }

  protected function getOptions() {
    return $this->options;
  }
  protected function setBaseUrl(string $baseUrl = "") {
    $this->baseUrl = $baseUrl;
  }
}
