<?php

return [
    'logo_path' => env('COMMUNITY_LOGO_PATH', 'images/MineVidaLogo.png'),
    'public_background_path' => env('COMMUNITY_PUBLIC_BACKGROUND_PATH', 'images/lumo_fondo.png'),
    'sidebar_background_path' => env('COMMUNITY_SIDEBAR_BACKGROUND_PATH', 'images/slidebar.png'),

    'server_ip' => env('COMMUNITY_SERVER_IP', env('MINEVIDA_SERVER_IP', 'play.minevida.net')),
    'server_version' => env('COMMUNITY_SERVER_VERSION', env('MINEVIDA_SERVER_VERSION', 'Java 1.20+')),
    'server_mode' => env('COMMUNITY_SERVER_MODE', env('MINEVIDA_SERVER_MODE', 'Survival OP')),
    'server_players_label' => env('COMMUNITY_SERVER_PLAYERS_LABEL', env('MINEVIDA_SERVER_PLAYERS_LABEL', 'Comunidad activa')),
    'server_performance' => env('COMMUNITY_SERVER_PERFORMANCE', env('MINEVIDA_SERVER_PERFORMANCE', 'TPS 20.0')),
    'discord_widget_id' => env('COMMUNITY_DISCORD_WIDGET_ID', env('MINEVIDA_DISCORD_WIDGET_ID', '')),

    'discord_status_images' => [
        'pending' => env('DISCORD_STATUS_PENDING_IMAGE_URL', ''),
        'in_review' => env('DISCORD_STATUS_IN_REVIEW_IMAGE_URL', ''),
        'interview' => env('DISCORD_STATUS_INTERVIEW_IMAGE_URL', ''),
        'accepted' => env('DISCORD_STATUS_ACCEPTED_IMAGE_URL', ''),
        'rejected' => env('DISCORD_STATUS_REJECTED_IMAGE_URL', ''),
    ],

    'application_defaults' => [
        'applications_open' => env('APPLICATIONS_OPEN_BY_DEFAULT', true),
        'minimum_age' => env('APPLICATION_MINIMUM_AGE', 15),
        'reapply_cooldown_days' => env('APPLICATION_REAPPLY_COOLDOWN_DAYS', 14),
        'require_discord_guild' => env('APPLICATION_REQUIRE_DISCORD_GUILD', false),
    ],

    'social_links' => [
        [
            'label' => 'Discord',
            'description' => 'Comunidad y soporte',
            'url' => env('COMMUNITY_DISCORD_URL', env('MINEVIDA_DISCORD_URL', '')),
            'abbr' => 'DC',
        ],
        [
            'label' => 'Tienda',
            'description' => 'Rangos y extras',
            'url' => env('COMMUNITY_STORE_URL', env('MINEVIDA_STORE_URL')),
            'abbr' => 'ST',
        ],
        [
            'label' => 'TikTok',
            'description' => 'Clips y novedades',
            'url' => env('COMMUNITY_TIKTOK_URL', env('MINEVIDA_TIKTOK_URL')),
            'abbr' => 'TK',
        ],
        [
            'label' => 'YouTube',
            'description' => 'Videos y eventos',
            'url' => env('COMMUNITY_YOUTUBE_URL', env('MINEVIDA_YOUTUBE_URL')),
            'abbr' => 'YT',
        ],
        [
            'label' => 'Instagram',
            'description' => 'Anuncios y capturas',
            'url' => env('COMMUNITY_INSTAGRAM_URL', env('MINEVIDA_INSTAGRAM_URL')),
            'abbr' => 'IG',
        ],
    ],
];
