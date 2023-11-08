<?php

namespace AcquiaCMS\Cli\FileSystem;

use AcquiaCMS\Cli\Helpers\ArrayHelper;
use Consolidation\Config\Loader\ConfigProcessor;

/**
 * Class to process yaml data.
 */
class YamlConfigProcessor extends ConfigProcessor {

  /**
   * {@inheritdoc}
   */
  protected function process(array $processed, array $toBeProcessed, $referenceArray = []) {
    $reduced = $this->reduce($toBeProcessed);
    return $this->evaluate($reduced, $referenceArray);
  }

  /**
   * {@inheritdoc}
   */
  protected function reduce(array $toBeProcessed): array {
    $processed = [];
    foreach ($toBeProcessed as $fileName => $value) {
      if (file_exists($fileName)) {
        if (!$processed) {
          $processed = $value;
          continue;
        }
        $processedStarterKits = array_keys($processed['starter_kits']);
        if (isset($value['starter_kits'])) {
          $value['starter_kits'] = array_filter($value['starter_kits'], function ($starter_kit, $machine_name) use ($processedStarterKits) {
            return !in_array($machine_name, $processedStarterKits);
          }, ARRAY_FILTER_USE_BOTH);
        }
        $processed = ArrayHelper::mergeRecursive($processed, $value);
      }
    }
    return $processed;
  }

}
