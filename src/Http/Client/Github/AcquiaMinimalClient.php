<?php

namespace AcquiaCMS\Cli\Http\Client\Github;

/**
 * Interacts with the Acquia Minimal Github API client.
 *
 * @see https://docs.github.com/en/rest/reference/repos
 */
class AcquiaMinimalClient extends GithubBase {

  function getRepoName() {
    return "acquia/drupal-minimal-project";
  }

}
