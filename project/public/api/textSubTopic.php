<?php
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    if (!isset($_GET['topic']) || !isset($_GET['subtopic'])) {
        throw new Exception('Both topic and subtopic parameters are required');
    }
    $connect = new \App\Connect();

    $topicName = urldecode($_GET['topic']);
    $subtopicName = urldecode($_GET['subtopic']);

    $text = $connect->db->GetTextSubTopics($topicName, $subtopicName);

    if (empty($text)) {
        http_response_code(404);
        echo json_encode(['error' => 'Subtopic not found']);
    } else {
        echo json_encode([
            'topic' => $topicName,
            'subtopic' => $subtopicName,
            'text' => $text
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}