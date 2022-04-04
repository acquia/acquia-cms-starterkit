<?php

namespace AcquiaCMS\Cli\Http\Client;

interface HttpClientInterface {
  public function request(string $url);
  public function setMethod(string $url);
  public function setOptions(array $options);
  public function setBaseUrl(string $url);
  public function getOptions();
}
