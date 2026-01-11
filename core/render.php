<?php
$spark_renders = [];

function render($callback) {
    global $spark_renders;
    $spark_renders[] = $callback;
}

function run_renders() {
    global $spark_renders;
    foreach ($spark_renders as $r) $r();
    $spark_renders = [];
}