<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../errors.log');
require __DIR__.'/../bootstrap.php';
