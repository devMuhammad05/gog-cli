<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID', ''),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    'redirect_uri' => 'http://localhost:8080', // For CLI apps

    'scopes' => [
        'https://www.googleapis.com/auth/gmail.modify',
        'https://www.googleapis.com/auth/gmail.readonly',
    ],

    'credentials_path' => (env('HOME') ?: env('USERPROFILE')) . DIRECTORY_SEPARATOR . '.gog' . DIRECTORY_SEPARATOR . 'credentials.json',
    'token_path' => (env('HOME') ?: env('USERPROFILE')) . DIRECTORY_SEPARATOR . '.gog' . DIRECTORY_SEPARATOR . 'token.json',
];
