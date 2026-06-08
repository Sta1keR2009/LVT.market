<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/SimpleDigiKeyAuth.php';

echo "<h1>🧪 Simple Digi-Key Auth Test</h1>";

try {
    $auth = new SimpleDigiKeyAuth();
    $auth->testUrl();
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}