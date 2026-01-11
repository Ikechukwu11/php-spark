<?php
// bootstrap.php
require __DIR__.'/./core/autoload.php';
require __DIR__.'/./core/runtime.php';
require __DIR__.'/./core/state.php';
require __DIR__.'/./core/action.php';
require __DIR__.'/./core/render.php';
require __DIR__.'/./core/event.php';
require __DIR__.'/./includes/helpers.php';
require __DIR__.'/./includes/layout.php';
require __DIR__.'/./includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  if (isset($data['_spark'])) {

    $componentFile = __DIR__ . "/components/{$data['component']}.php";
    if (!file_exists($componentFile)) {
      echo json_encode(['html' => 'Component not found', 'snapshot' => [], 'events' => []]);
      exit;
    }

    $component = require $componentFile; // returns closure

    $id = $data['id'] ?? uniqid($data['component'] . '_');
    $snapshot = $data['snapshot'] ?? [];

    // ⚡ Mount the component
    $component($id, $snapshot); // mount, register actions, render

    // ⚡ Execute the action
    call_action($data['action'], $data['payload'] ?? []);

    // ⚡ Capture render output
    ob_start();
    run_renders();
    $html = ob_get_clean();

    echo json_encode([
      'html' => $html,
      'snapshot' => $snapshot,
      'events' => []
    ]);
    exit; // <--- MUST exit here
  }
}


$path = trim($_GET['path'] ?? 'index', '/');
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = trim($route, '/');
$route = $route === '' ? 'index' : $route;

$page = __DIR__ . "/pages/$route.php";
//$page = __DIR__ . '/./pages/' . $path . '.php';
if (!file_exists($page)) {
  http_response_code(404);
  exit("Page not found");
}
ob_start();
require $page;
$content = ob_get_clean();
layout(fn() => print $content);