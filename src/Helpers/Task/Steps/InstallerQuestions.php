<?php

namespace AcquiaCMS\Cli\Helpers\Task\Steps;

/**
 * File for setting API keys.
 */
class InstallerQuestions {

  /**
   * SET Api Keys as per the starter kit selected.
   *
   * @param string $starter_kit
   *   Selected Starter kit.
   */
  public function execute(string $starter_kit) :array {
    $keys = [];
    // Returns Environment variables as per selected ACMS starter kit.
    switch ($starter_kit) {
      case "acquia_cms_demo":
        $keys = [
          "SEARCH_UUID" => getenv("SEARCH_UUID"),
          "GMAPS_KEY" => getenv("GMAPS_KEY"),
          "SITESTUDIO_API_KEY" => getenv("SITESTUDIO_API_KEY"),
          "SITESTUDIO_ORG_KEY" => getenv("SITESTUDIO_ORG_KEY"),
          "CONNECTOR_ID" => getenv("CONNECTOR_ID"),
        ];
        break;

      case "acquia_cms_low_code":
        $keys = [
          "SEARCH_UUID" => getenv("SEARCH_UUID"),
          "SITESTUDIO_API_KEY" => getenv("SITESTUDIO_API_KEY"),
          "SITESTUDIO_ORG_KEY" => getenv("SITESTUDIO_ORG_KEY"),
        ];
        break;

      case "acquia_cms_standard":
        $keys = [
          "SEARCH_UUID" => getenv("SEARCH_UUID"),
        ];
        break;

      case "acquia_cms_minimal":
        $keys = [
          "SEARCH_UUID" => getenv("SEARCH_UUID"),
        ];
        break;

      case "acquia_cms_headless":

      default:
        break;
    }

    return $keys;
  }

}
