<?php

namespace AcquiaCMS\Cli\FileSystem;

use Dflydev\DotAccessData\Data;
use Grasmash\Expander\Expander;

/**
 * Class to alter yaml config expander.
 */
class YamlConfigExpander extends Expander {

  /**
   * {@inheritdoc}
   */
  public function expandProperty(string $property_name, string $unexpanded_value, Data $data): mixed {
    $expanded_value = parent::expandProperty($property_name, $unexpanded_value, $data);
    if (substr($property_name, 0, 4) == "env." && !$data->has($property_name)) {
      $env_key = substr($property_name, 4);
      if (!getenv($env_key)) {
        $data->set($property_name, "");
        return "";
      }
    }
    return $expanded_value;
  }

}
