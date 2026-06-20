<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $classPath = __DIR__ . '/../classes/AirportSearch.php';
    if (!file_exists($classPath)) {
        throw new Exception("File missing: Cannot find AirportSearch.php at " . realpath($classPath));
    }

    require_once $classPath;

    $query = trim($_GET['q'] ?? '');

    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    if (!class_exists('AirportSearch')) {
        throw new Exception("Class missing: AirportSearch class definition not found inside the file.");
    }

    $searchEngine = new AirportSearch();
    $results = $searchEngine->search($query);

    echo json_encode($results);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}