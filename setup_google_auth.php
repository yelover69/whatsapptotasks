<?php

$config = require __DIR__ . '/config.php';

$clientId = $config['google']['client_id'] ?: ($_GET['client_id'] ?? '');
$clientSecret = $config['google']['client_secret'] ?: ($_GET['client_secret'] ?? '');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$currentScript = strtok($_SERVER['REQUEST_URI'] ?? '/setup_google_auth.php', '?');
$redirectUri = $protocol . $host . $currentScript;

$code = $_GET['code'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Tasks Auth</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0b0f19; color: #f1f5f9; padding: 40px 20px; }
        .card { max-width: 580px; margin: 0 auto; background: #131b2e; padding: 32px; border-radius: 12px; border: 1px solid #1e293b; }
        h1 { font-size: 22px; margin-top: 0; color: #38bdf8; }
        p { color: #94a3b8; line-height: 1.6; }
        input[type="text"] { width: 100%; box-sizing: border-box; padding: 12px; margin: 8px 0 16px; background: #0b0f19; border: 1px solid #293548; border-radius: 6px; color: #fff; font-family: inherit; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: 500; border: none; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .code-box { background: #0b0f19; border: 1px solid #10b981; padding: 14px; border-radius: 6px; color: #34d399; font-family: monospace; word-break: break-all; margin: 16px 0; }
        .alert { background: #1e293b; padding: 12px 16px; border-radius: 6px; border-left: 3px solid #38bdf8; margin-bottom: 20px; font-size: 13px; color: #94a3b8; }
        code { color: #38bdf8; font-family: monospace; }
    </style>
</head>
<body>
<div class="card">
    <h1>Google Tasks Connect</h1>

    <?php if ($code): ?>
        <?php
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $postData = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $tokens = json_decode($response, true);
        ?>

        <?php if (!empty($tokens['refresh_token'])): ?>
            <p>Authorization complete. Set this as <code>GOOGLE_REFRESH_TOKEN</code> in your environment:</p>
            <div class="code-box"><?= htmlspecialchars($tokens['refresh_token']) ?></div>
        <?php else: ?>
            <div class="alert" style="border-left-color: #ef4444;">
                <?= htmlspecialchars($tokens['error_description'] ?? $tokens['error'] ?? $response) ?>
            </div>
            <p><a href="<?= htmlspecialchars($currentScript) ?>" class="btn">Retry</a></p>
        <?php endif; ?>

    <?php else: ?>

        <div class="alert">
            Redirect URI in Google Cloud Console: <code><?= htmlspecialchars($redirectUri) ?></code>
        </div>

        <form method="GET" action="https://accounts.google.com/o/oauth2/v2/auth">
            <label style="font-size: 14px; color: #cbd5e1;">Google Client ID</label>
            <input type="text" name="client_id" value="<?= htmlspecialchars($clientId) ?>" required placeholder="e.g. 12345-abc.apps.googleusercontent.com">

            <input type="hidden" name="redirect_uri" value="<?= htmlspecialchars($redirectUri) ?>">
            <input type="hidden" name="response_type" value="code">
            <input type="hidden" name="scope" value="https://www.googleapis.com/auth/tasks">
            <input type="hidden" name="access_type" value="offline">
            <input type="hidden" name="prompt" value="consent">

            <button type="submit" class="btn">Authorize Google Tasks</button>
        </form>

    <?php endif; ?>
</div>
</body>
</html>
