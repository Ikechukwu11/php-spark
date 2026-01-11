<?php
$spark_states = [];

function &state($key, $compId='global', $default=null) {
    global $spark_states;
    $id = "$compId:$key";
    if (!isset($spark_states[$id])) $spark_states[$id] = $default;
    return $spark_states[$id];
}