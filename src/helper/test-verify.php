<?php
header('Content-Type: application/json');

$response = [
    'success' => true,
    'message' => 'Test successful',
    'received_data' => $_POST
];

echo json_encode($response);
