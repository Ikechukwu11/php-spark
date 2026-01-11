<?php
$spark_events = [];

function emit($event, $payload=null) {
    global $spark_events;
    $spark_events[] = ['event'=>$event,'payload'=>$payload];
}

function get_events() {
    global $spark_events;
    $ev = $spark_events;
    $spark_events = [];
    return $ev;
}