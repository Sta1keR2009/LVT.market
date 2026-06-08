<?php
/**
 * Manticore Search proxy for AI consultant.
 * Queries product index for availability and details.
 * 
 * POST /local/api/manticore_search.php
 * Body: {"query": "поисковый запрос", "limit": 5}
 */
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: https://test.lvt.market');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$query = trim($body['query'] ?? '');
$limit = min((int)($body['limit'] ?? 5), 20);

if (empty($query)) {
    echo json_encode(['ok' => false, 'error' => 'empty query', 'products' => []]);
    exit;
}

try {
    // Manticore HTTP API
    $manticoreUrl = 'http://127.0.0.1:9308/search';
    
    $searchBody = json_encode([
        'index' => 'bitrix',
        'query' => [
            'match' => ['*' => $query],
        ],
        'limit' => $limit,
        'options' => [
            'ranker' => 'bm25',
        ],
    ]);

    $ch = curl_init($manticoreUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $searchBody,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        echo json_encode(['ok' => false, 'error' => "manticore: HTTP $httpCode", 'products' => []]);
        exit;
    }

    $data = json_decode($response, true);
    $hits = $data['hits']['hits'] ?? [];

    $products = [];
    foreach ($hits as $hit) {
        $src = $hit['_source'] ?? [];
        $products[] = [
            'id' => $src['item_id'] ?? $hit['_id'],
            'title' => $src['title'] ?? '',
            'description' => mb_substr(strip_tags($src['body'] ?? ''), 0, 300),
            'score' => $hit['_score'] ?? 0,
        ];
    }

    echo json_encode([
        'ok' => true,
        'query' => $query,
        'total' => $data['hits']['total'] ?? count($products),
        'products' => $products,
    ]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'products' => []]);
}
