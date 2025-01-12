<?php

/**
 * Send a JSON response and set HTTP status code
 * @param int $status HTTP status code
 * @param array $data Response data
 */
function sendResponse(int $status, array $data) {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

/**
 * Get JSON input from the request body
 * @return array|null Decoded JSON data or null
 */
function getJsonInput() {
    return json_decode(file_get_contents("php://input"), true);
}
?>
