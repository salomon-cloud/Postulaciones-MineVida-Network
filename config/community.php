<?php

return [
    'server_ip' => env('MINEVIDA_SERVER_IP', 'play.minevida.net'),
    'server_version' => env('MINEVIDA_SERVER_VERSION', 'Java 1.20+'),
    'discord_widget_id' => env('MINEVIDA_DISCORD_WIDGET_ID', '1422483289767153686'),

    'social_links' => [
        [
            'label' => 'Discord',
            'description' => 'Comunidad y soporte',
            'url' => env('MINEVIDA_DISCORD_URL', 'https://discord.gg/minevida'),
            'abbr' => 'DC',
        ],
        [
            'label' => 'Tienda',
            'description' => 'Rangos y extras',
            'url' => env('MINEVIDA_STORE_URL'),
            'abbr' => 'ST',
        ],
        [
            'label' => 'TikTok',
            'description' => 'Clips y novedades',
            'url' => env('MINEVIDA_TIKTOK_URL'),
            'abbr' => 'TK',
        ],
        [
            'label' => 'YouTube',
            'description' => 'Videos y eventos',
            'url' => env('MINEVIDA_YOUTUBE_URL'),
            'abbr' => 'YT',
        ],
        [
            'label' => 'Instagram',
            'description' => 'Anuncios y capturas',
            'url' => env('MINEVIDA_INSTAGRAM_URL'),
            'abbr' => 'IG',
        ],
    ],
];
