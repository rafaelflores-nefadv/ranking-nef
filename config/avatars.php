<?php

return [
    'disk' => env('AVATAR_DISK', 'public'),
    'directory' => env('AVATAR_DIRECTORY', 'avatars'),
    'max_kb' => (int) env('AVATAR_MAX_KB', 2048),
    'max_px' => (int) env('AVATAR_MAX_PX', 512),
    'jpeg_quality' => (int) env('AVATAR_JPEG_QUALITY', 82),
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
];
