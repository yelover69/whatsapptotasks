<?php

require_once __DIR__ . '/src/GeminiClassifier.php';
require_once __DIR__ . '/src/GoogleTasksClient.php';
require_once __DIR__ . '/src/MessageFilter.php';

$config = require __DIR__ . '/config.php';

$sampleMessage = $argv[1] ?? "Remember to submit the quarterly report by tomorrow at 5 PM";
$senderPhone = $argv[2] ?? "15551234567";

if (empty($config['gemini_api_key'])) {
    fwrite(STDERR, "Error: GEMINI_API_KEY is not set.\n");
    exit(1);
}

$mockMsg = ['from' => $senderPhone, 'text' => $sampleMessage, 'is_group' => false];
$filterCheck = MessageFilter::isAllowed($mockMsg, $config['filters'] ?? []);
if (!$filterCheck['allowed']) {
    echo "Filtered: {$filterCheck['reason']}\n";
    exit(0);
}

$classifier = new GeminiClassifier(
    $config['gemini_api_key'],
    $config['gemini_model'],
    $config['timezone']
);

$result = $classifier->classifyAndExtract($sampleMessage, "Tester");

echo json_encode([
    'input'          => $sampleMessage,
    'classification' => $result,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

if (!empty($result['is_actionable']) && !empty($config['google']['refresh_token'])) {
    $tasksClient = new GoogleTasksClient(
        $config['google']['client_id'],
        $config['google']['client_secret'],
        $config['google']['refresh_token'],
        $config['google']['task_list_id']
    );
    $task = $tasksClient->createTask(
        $result['title'],
        $result['due_date'],
        "CLI Test: {$sampleMessage}"
    );
    echo "Created task ID: " . ($task['id'] ?? 'unknown') . PHP_EOL;
}
