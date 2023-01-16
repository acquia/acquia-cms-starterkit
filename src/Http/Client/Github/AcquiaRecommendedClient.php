<?php

namespace AcquiaCMS\Cli\Http\Client\Github;

/**
 * Interacts with the Acquia Minimal GitHub API client.
 *
 * @see https://docs.github.com/en/rest/reference/repos
 */
class AcquiaRecommendedClient extends GithubBase {

  /**
   * For now let's hardcode branch name for Drupal Core 10.
   * @todo: Remove after DRP team make a new release tag for Drupal 10.
   *
   * @var string
   */
  protected $latestReleaseTag = "drupal10";

  /**
   * Returns the GitHub repo name.
   */
  public function getRepoName(): string {
    return "acquia/drupal-recommended-project";
  }

}
