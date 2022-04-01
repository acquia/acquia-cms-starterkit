<?php

namespace AcquiaCMS\Cli\Http\Client\Github;

use AcquiaCMS\Cli\Http\Client\HttpClientBase;

/**
 * Interacts with the Acquia Minimal Github API client.
 *
 * @see https://docs.github.com/en/rest/reference/repos
 */
abstract class GithubBase extends HttpClientBase {
  protected $baseUrl = "https://api.github.com/repos";

  protected $latestReleaseTag;
  /**
   * Gets the latest tag of acquia-minimal-project.
   *
   * @return string
   *   The branch name.
   *
   *
   * @noinspection PhpDocMissingThrowsInspection
   */
  public function getLatestReleaseTag(): string {
    if ($this->latestReleaseTag) {
      return $this->latestReleaseTag;
    }

    $response = $this->request("/" . $this->getRepoName() . "/releases/latest");
    $json_data = json_decode($response);
    $this->latestReleaseTag = $json_data->tag_name;
    return $this->latestReleaseTag;
  }

  public function getFileContents($file_name) {
    $tag_name = $this->getLatestReleaseTag();
    $this->setBaseUrl("https://raw.githubusercontent.com");
    $response = $this->request("/" . $this->getRepoName() . "/" . $tag_name . "/" . $file_name);
    return $response;
  }
  abstract function getRepoName();
}
