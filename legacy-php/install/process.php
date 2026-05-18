<?php

header('Content-Type: application/json');
http_response_code(403);

echo json_encode([
    'success' => false,
    'message' => 'Installer has been permanently disabled for security. Use controlled configuration and migrations instead.',
]);
