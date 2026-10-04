<?php

require_once __DIR__ . '/src/GeminiClassifier.php';
require_once __DIR__ . '/src/GoogleTasksClient.php';
require_once __DIR__ . '/src/MetaWhatsAppClient.php';
require_once __DIR__ . '/src/MessageFilter.php';

$config = require __DIR__ . '/config.php';

date_default_timezone_set($config['timezone'] ?? 'UTC');

function bot_log($message, $config) {
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    if (!empty($config['log_file'])) {
        @file_put_contents($config['log_file'], $entry, FILE_APPEND);
    }
    error_log($message);
}

$metaClient = new MetaWhatsAppClient(
    $config['meta']['verify_token'],
    $config['meta']['access_token'] ?: null,
    $config['meta']['phone_number_id'] ?: null
);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $challenge = $metaClient->handleVerification($_GET);
    if ($challenge !== null) {
        http_response_code(200);
        header('Content-Type: text/plain');
        echo $challenge;
        exit;
    }
    http_response_code(403);
    echo "forbidden";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $payload = json_decode($rawInput, true);

    if (empty($payload)) {
        http_response_code(400);
        echo json_encode(['error' => 'empty_payload']);
        exit;
    }

    $messages = $metaClient->extractIncomingMessages($payload);
    if (empty($messages)) {
        http_response_code(200);
        echo json_encode(['status' => 'noop']);
        exit;
    }

    $classifier = new GeminiClassifier(
        $config['gemini_api_key'],
        $config['gemini_model'],
        $config['timezone']
    );

    $tasksClient = new GoogleTasksClient(
        $config['google']['client_id'],
        $config['google']['client_secret'],
        $config['google']['refresh_token'],
        $config['google']['task_list_id']
    );

    $results = [];

    foreach ($messages as $msg) {
        $filterCheck = MessageFilter::isAllowed($msg, $config['filters'] ?? []);
        if (!$filterCheck['allowed']) {
            bot_log("Filtered out: " . $filterCheck['reason'], $config);
            $results[] = [
                'message_id' => $msg['id'],
                'action'     => 'filtered',
                'reason'     => $filterCheck['reason'],
            ];
            continue;
        }

        try {
            $analysis = $classifier->classifyAndExtract($msg['text'], $msg['name']);

            if (!empty($analysis['is_actionable'])) {
                $notes = "From WhatsApp ({$msg['name']} +{$msg['from']}):\n\"{$msg['text']}\"\n";
                if (!empty($analysis['notes'])) {
                    $notes .= "Context: " . $analysis['notes'];
                }

                $createdTask = $tasksClient->createTask(
                    $analysis['title'] ?: $msg['text'],
                    $analysis['due_date'] ?? null,
                    $notes
                );

                if (!empty($config['meta']['send_reply'])) {
                    $dueStr = !empty($analysis['due_date'])
                        ? date('D, M j, Y \a\t g:i A', strtotime($analysis['due_date']))
                        : 'No due date';

                    $typeIcon = ($analysis['type'] === 'event') ? '📅' : '📌';
                    $reply = "✅ *Added to Google Tasks!*\n\n"
                        . "{$typeIcon} *Title:* {$analysis['title']}\n"
                        . "⏰ *Due:* {$dueStr}";

                    $metaClient->sendMessage($msg['from'], $reply);
                }

                $results[] = [
                    'message_id' => $msg['id'],
                    'action'     => 'created_task',
                    'title'      => $analysis['title'],
                    'due'        => $analysis['due_date'],
                ];
            } else {
                $results[] = [
                    'message_id' => $msg['id'],
                    'action'     => 'ignored',
                    'reason'     => $analysis['reasoning'] ?? 'not actionable',
                ];
            }
        } catch (Throwable $e) {
            bot_log("Pipeline error: " . $e->getMessage(), $config);
            $results[] = [
                'message_id' => $msg['id'],
                'action'     => 'error',
                'error'      => $e->getMessage(),
            ];
        }
    }

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status'    => 'ok',
        'processed' => count($results),
        'results'   => $results,
    ]);
    exit;
}

http_response_code(405);
echo "method not allowed";
