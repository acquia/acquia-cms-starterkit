<?php

namespace AcquiaCMS\Cli\Http\Client\Github;

/**
 * Interacts with the Acquia Minimal Github API client.
 *
 * @see https://docs.github.com/en/rest/reference/repos
 */
class AcquiaMinimalClient extends GithubBase {

  /**
   * Returns the github repo name.
   */
  public function getRepoName() :string {
    return "acquia/drupal-minimal-project";
  }

}
