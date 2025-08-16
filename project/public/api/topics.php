<?php
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $connect = new \App\Connect();
    $topics = $connect->db->GetData();
    echo json_encode($topics);
    http_response_code(200);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}