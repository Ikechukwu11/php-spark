<?php
$spark_events = [];
$spark_listeners = [];

/**
 * Register an event listener for a component
 */
function on($event, $component)
{
    global $spark_listeners;
    $spark_listeners[$event][] = $component;
}

/**
 * Emit a normal event to registered components
 */
function emit($event, $payload = null)
{
    global $spark_events, $spark_listeners;

    $targets = $spark_listeners[$event] ?? [];

    foreach ($targets as $comp) {
        $spark_events[] = [
            'event' => $event,
            'component' => $comp,
            'payload' => $payload
        ];
    }
}

/**
 * Emit a one-time refresh event with rendered component HTML
 *
 * @param array $components List of components to refresh
 * [
 *   ['component' => 'Navbar', 'snapshot' => [], 'html' => null]
 * ]
 */
function emit_refresh_once(array $components)
{
    global $spark_events, $spark_renders;

    foreach ($components as &$c) {
        $file = __DIR__ . '/../components/' . $c['component'] . '.php';
        if (!file_exists($file)) continue;
        $component = require $file;

        // Save the global render queue and isolate
        $savedRenders = $spark_renders;
        $spark_renders = [];

        // Isolated buffer for this component
        $c['id']= uniqid();
        $c['html'] = (function () use ($component, $c) {
            ob_start();
            $component($c['component'] . '_' . $c['id'], $c['snapshot'] ?? []);
            run_renders(); // flush only renders queued in this call
            return ob_get_clean();
        })();

        // Restore the global render queue
        $spark_renders = $savedRenders;

        $spark_events[] = [
            'event' => '__refresh_once',
            'payload' => [$c]
        ];
    }
}


/**
 * Retrieve and clear all pending events
 */
function get_events()
{
    global $spark_events;
    $ev = $spark_events;
    $spark_events = [];
    return $ev;
}
