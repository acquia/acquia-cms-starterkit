<?php

namespace AcquiaCMS\Cli\Http\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Interacts with the http request.
 */
abstract class HttpClientBase {

  /**
   * Stores the http request method. Ex: "GET", "POST" etc.
   *
   * @var string
   */
  protected $method;

  /**
   * Stores an array of options to pass on http request.
   *
   * @var array
   */
  protected $options;

  /**
   * Stores the base url for making an http request.
   *
   * @var string
   */
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
   * Makes a http request to given url.
   *
   * @param string $url
   *   String having url path to make a http request.
   */
  public function request(string $url) :string {
    $response = $this->httpClient
      ->request($this->method, $this->baseUrl . $url, $this->getOptions());

    $response = $response->getContent();
    return $response;
  }

  /**
   * Sets the given method to http request. Ex: 'GET', 'POST' etc.
   */
  public function setMethod(string $method) :void {
    $this->method = $method;
  }

  /**
   * Sets the given options to http request.
   *
   * @param array $options
   *   An array of options to pass on http request.
   */
  public function setOptions(array $options) :void {
    $this->options = $options;
  }

  /**
   * Returns the given options.
   *
   * @return array
   *   Returns an array of http request options.
   */
  public function getOptions() :array {
    return $this->options;
  }

  /**
   * Sets the base url to http request.
   *
   * @param string $baseUrl
   *   A string having baseUrl.
   */
  public function setBaseUrl(string $baseUrl = "") :void {
    $this->baseUrl = $baseUrl;
  }

}
