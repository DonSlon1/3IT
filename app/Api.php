<?php

require_once __DIR__ . '/../DbConfig.php';
session_start();

// Set JSON header
header('Content-Type: application/json');

// Get database connection
$db = DbConfig::getDbConnection();

// Get the requested action from URL
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($path, '/'));

if (count($pathParts) >= 2 && $pathParts[0] === 'api' && $pathParts[1] === 'stats') {
    try {
        $sessionId = session_id();

        // Get total count
        $totalCount = $db->query('SELECT COUNT(*) FROM zaznamy')->fetchSingle();

        // Get marked count from database (same as table page)
        $markedCount = $db->query('
            SELECT COUNT(DISTINCT zaznam_id)
            FROM `marked_records`
            WHERE session_id = %s', $sessionId)->fetchSingle();

        // Calculate percentage
        $percentage = $totalCount > 0 ? round(($markedCount / $totalCount) * 100) : 0;

        echo json_encode([
            'success' => true,
            'total' => (int)$totalCount,
            'marked' => (int)$markedCount,
            'percentage' => (int)$percentage
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching statistics'
        ]);
    }
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'API endpoint not found'
    ]);
}