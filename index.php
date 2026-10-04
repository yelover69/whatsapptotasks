<?php
$config = require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp AI Task Bot</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.6; }
        .container { max-width: 700px; margin: 0 auto; background: #1e293b; padding: 32px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #38bdf8; margin-top: 0; font-size: 26px; }
        .badge { display: inline-block; background: #059669; color: #ecfdf5; font-size: 12px; font-weight: bold; padding: 4px 10px; border-radius: 9999px; }
        .card { background: #0f172a; border: 1px solid #334155; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 8px; }
        .btn:hover { background: #1d4ed8; }
        ul { padding-left: 20px; color: #cbd5e1; }
        li { margin-bottom: 8px; }
        code { background: #334155; padding: 2px 6px; border-radius: 4px; color: #38bdf8; font-family: monospace; }
        .status-ok { color: #4ade80; font-weight: bold; }
        .status-warn { color: #f59e0b; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>🤖 WhatsApp AI Task Bot</h1>
        <span class="badge">Online</span>
    </div>
    <p>Your 100% free serverless bot is running and ready to convert WhatsApp messages into Google Tasks using Google Gemini Flash.</p>

    <div class="card">
        <h3 style="margin-top:0; color:#e2e8f0;">Configuration Status</h3>
        <ul>
            <li>
                <strong>Gemini API Key:</strong> 
                <?= !empty($config['gemini_api_key']) && $config['gemini_api_key'] !== 'YOUR_GEMINI_API_KEY' 
                    ? '<span class="status-ok">Configured ✅</span>' 
                    : '<span class="status-warn">Missing ⚠️ (Set GEMINI_API_KEY)</span>' ?>
            </li>
            <li>
                <strong>Google Tasks OAuth:</strong> 
                <?= !empty($config['google']['refresh_token']) && $config['google']['refresh_token'] !== 'YOUR_GOOGLE_REFRESH_TOKEN' 
                    ? '<span class="status-ok">Connected ✅</span>' 
                    : '<span class="status-warn">Needs Authorization ⚠️</span>' ?>
            </li>
            <li>
                <strong>Meta Webhook Verify Token:</strong> 
                <code><?= htmlspecialchars($config['meta']['verify_token']) ?></code>
            </li>
            <li>
                <strong>Access Control Mode:</strong> 
                <code><?= htmlspecialchars($config['filters']['mode'] ?? 'all') ?></code>
                <?php if (($config['filters']['mode'] ?? 'all') === 'whitelist'): ?>
                    (<span><?= count($config['filters']['allowed_phone_numbers'] ?? []) ?> people, <?= count($config['filters']['allowed_group_ids'] ?? []) ?> groups</span>)
                <?php else: ?>
                    <span style="color:#94a3b8;">(Public: processes all messages)</span>
                <?php endif; ?>
            </li>
        </ul>
        <a href="setup_google_auth.php" class="btn">Connect Google Tasks (1-Click OAuth)</a>
    </div>

    <div class="card">
        <h3 style="margin-top:0; color:#e2e8f0;">Meta WhatsApp Webhook Configuration</h3>
        <p>In your <a href="https://developers.facebook.com/" target="_blank" style="color:#38bdf8;">Meta App Dashboard</a> &gt; WhatsApp &gt; Configuration:</p>
        <ul>
            <li><strong>Callback URL:</strong> <code><?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/webhook.php" ?></code></li>
            <li><strong>Verify Token:</strong> <code><?= htmlspecialchars($config['meta']['verify_token']) ?></code></li>
            <li><strong>Webhook Fields:</strong> Subscribe to <code>messages</code>.</li>
        </ul>
    </div>
</div>
</body>
</html>
