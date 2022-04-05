<?php

namespace AcquiaCMS\Cli\Http\Client\Github;

use AcquiaCMS\Cli\Http\Client\HttpClientBase;

/**
 * Interacts with the Acquia Minimal Github API client.
 *
 * @see https://docs.github.com/en/rest/reference/repos
 */
abstract class GithubBase extends HttpClientBase {

  /**
   * Defines the baseurl for github repos.
   *
   * @var string
   */
  protected $baseUrl = "https://api.github.com/repos";

  /**
   * Holds the latest release defined for given github repo.
   *
   * @var string
   */
  protected $latestReleaseTag;

  /**
   * Gets the latest tag of acquia-minimal-project.
   *
   * @return string
   *   Returns the lastest release tag.
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

  /**
   * Returns the contents of given file.
   *
   * @return string
   *   Returns the http response.
   */
  public function getFileContents(string $file_name) {
    $tag_name = $this->getLatestReleaseTag();
    $this->setBaseUrl("https://raw.githubusercontent.com");
    $response = $this->request("/" . $this->getRepoName() . "/" . $tag_name . "/" . $file_name);
    return $response;
  }

  /**
   * Ab abstract method to get the repository name.
   */
  abstract public function getRepoName(): string;

}
