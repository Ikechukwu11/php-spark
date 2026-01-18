<?php
// core/refresh_once.php

/**
 * Queue a component to be re-rendered once and sent in the current response.
 *
 * @param string $componentName The name of the component file in /components or /pages
 * @param array $snapshot Optional snapshot data for the render
 */
function refresh_once(string $componentName, array $snapshot = [])
{
  static $queue = [];

  $queue[] = compact('componentName', 'snapshot');

  return $queue;
}

/**
 * Get all queued refresh_once components and render them as payload
 */
function get_refresh_once_payload(): array
{
  global $SPARK_COMPONENTS_DIR;
  static $queue = null;

  // reset after first retrieval
  $currentQueue = $queue ?? [];
  $queue = [];

  $payload = [];
  foreach ($currentQueue as $item) {
    $file = __DIR__ . '/../components/' . $item['componentName'] . '.php';
    if (!file_exists($file)) continue;
    $component = require $file;

    ob_start();
    $component($item['componentName'] . '_' . uniqid(), $item['snapshot']);
    $html = ob_get_clean();

    $payload[] = [
      'component' => $item['componentName'],
      'html' => $html,
      'snapshot' => $item['snapshot'] ?? []
    ];
  }

  return $payload;
}
