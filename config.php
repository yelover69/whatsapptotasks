<?php

return [
    'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
    'gemini_model'   => getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash',

    'google' => [
        'client_id'     => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'refresh_token' => getenv('GOOGLE_REFRESH_TOKEN') ?: '',
        'task_list_id'  => getenv('GOOGLE_TASK_LIST_ID') ?: '@default',
    ],

    'meta' => [
        'verify_token'    => getenv('META_VERIFY_TOKEN') ?: 'my_secret_whatsapp_token_123',
        'access_token'    => getenv('META_ACCESS_TOKEN') ?: '',
        'phone_number_id' => getenv('META_PHONE_NUMBER_ID') ?: '',
        'send_reply'      => filter_var(getenv('WHATSAPP_AUTO_REPLY') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    'filters' => [
        'mode'                  => getenv('FILTER_MODE') ?: 'all',
        'allowed_phone_numbers' => array_filter(array_map('trim', explode(',', getenv('ALLOWED_PHONE_NUMBERS') ?: ''))),
        'allowed_group_ids'     => array_filter(array_map('trim', explode(',', getenv('ALLOWED_GROUP_IDS') ?: ''))),
        'group_trigger_keyword' => getenv('GROUP_TRIGGER_KEYWORD') ?: '',
    ],

    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    'log_file' => getenv('VERCEL') ? sys_get_temp_dir() . '/bot.log' : __DIR__ . '/bot.log',
];
