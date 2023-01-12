<?php

namespace AcquiaCMS\Cli\Http\Client\Github;

/**
 * Interacts with the Acquia Minimal GitHub API client.
 *
 * @see https://docs.github.com/en/rest/reference/repos
 */
class AcquiaRecommendedClient extends GithubBase {

  /**
   * Returns the GitHub repo name.
   */
  public function getRepoName(): string {
    return "acquia/drupal-recommended-project";
  }

}
