<?php
function spark_component($name, $snapshot = [])
{
    $id = uniqid($name . '_');
    $file = __DIR__ . "/../components/$name.php";
    $component = require $file;

    ob_start();
    $component($id, $snapshot);
    run_renders();
    $html = ob_get_clean();

    return "<div data-spark='$name' data-id='$id' data-snapshot='" . htmlspecialchars(json_encode($snapshot), ENT_QUOTES) . "'>$html</div>";
}
