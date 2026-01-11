<?php
$spark_actions = [];

function action($name, $callback) {
    global $spark_actions;
    $spark_actions[$name] = $callback;
}

function call_action($name, $payload=[]) {
    global $spark_actions;
    if (isset($spark_actions[$name])) call_user_func_array($spark_actions[$name], $payload);
}