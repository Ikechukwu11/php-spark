<?php
/**
 * Bootstrap File - Application Entry Point
 *
 * This file handles two primary request flows:
 * 1. POST requests with _spark flag: Component-based AJAX requests
 * 2. GET requests: Traditional page routing and navigation
 *
 *
 */

// Load core dependencies
require __DIR__.'/./core/autoload.php';
require __DIR__.'/./core/runtime.php';
require __DIR__.'/./core/state.php';
require __DIR__.'/./core/action.php';
require __DIR__.'/./core/render.php';
require __DIR__.'/./core/event.php';
require __DIR__ . '/./core/listeners.php';
require __DIR__.'/./includes/helpers.php';
require __DIR__.'/./includes/layout.php';
require __DIR__.'/./includes/db.php';

/**
 * Handle POST requests (AJAX Component Requests)
 *
 * Processes component mounting and action execution for dynamic content updates.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_SERVER['HTTP_X_SPARK'] ?? null)) {
  $data = json_decode(file_get_contents('php://input'), true);

  // Check if this is a Spark component request
  if (isset($data['_spark'])) {

    // Load component file from components directory
    $componentFile = __DIR__ . "/components/{$data['component']}.php";
    if (!file_exists($componentFile)) {
      try {
        $componentFile = __DIR__ . "/components/{$data['component']}/index.php";
      } catch (\Throwable $th) {
        //throw $th;
      echo json_encode(['html' => 'Component not found', 'snapshot' => [], 'events' => []]);
        exit;
    }
    }

    // Component file should return a closure
    $component = require $componentFile;

    // Generate unique component ID and retrieve snapshot state
    $id = $data['id'] ?? uniqid($data['component'] . '_');
    $snapshot = $data['snapshot'] ?? [];

    // ⚡ Mount the component with ID and current state
    $component($id, $snapshot);

    // ⚡ Execute the action with provided payload
    if ($data['action'] !== '__refresh') {
      call_action($data['action'], $data['payload'] ?? []);
    }


    // ⚡ Capture rendered output from all registered renders
    ob_start();
    run_renders();
    $html = ob_get_clean();

    // Return JSON response with rendered HTML and state
    echo json_encode([
      'html' => $html,
      'snapshot' => $snapshot,
      'events' => get_events()
    ]);
    exit; // Must exit to prevent further output
  }
}

/**
 * Handle GET requests (Page Routing)
 *
 * Routes traditional page requests to appropriate template files.
 */

// Extract route from REQUEST_URI
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = trim($route, '/');
$route = $route === '' ? 'index' : $route;

// Attempt to load page file (supports both /page.php and /page/index.php patterns)
$page = __DIR__ . "/pages/$route.php";
if (!file_exists($page)) {
  $page = __DIR__ . "/pages/$route/index.php";
}

// Return 404 if page not found
if (!file_exists($page)) {
  http_response_code(404);
  exit("Page not found");
}

// Load and render the page
require $page;