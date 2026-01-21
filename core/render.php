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

function render_page(string $file)
{
    $source = file_get_contents($file);

    // Compile JSX-like components
    $compiled = spark_compile_components($source);

    // Execute compiled PHP
    eval('?>' . $compiled);
}
