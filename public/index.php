<?php
// Simple test endpoint
if (strpos($_SERVER['REQUEST_URI'], '/api/test') !== false) {
    echo json_encode(['message' => 'API is working!', 'timestamp' => time()]);
    exit;
}

// Temporary: just return a message
echo json_encode(['status' => 'Basic API working', 'uri' => $_SERVER['REQUEST_URI']]);
exit;
?>